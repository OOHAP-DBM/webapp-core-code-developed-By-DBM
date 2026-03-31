<?php

namespace Modules\POS\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Modules\POS\Models\VendorPaymentDetail;
use Illuminate\Validation\Rule;

/**
 * API Controller for Vendor POS Bookings
 * Handles bank accounts and UPI details for POS bookings
 */
class VendorPaymentDetailController extends Controller
{
    const MAX_BANKS = 5;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // ─── BANK ENDPOINTS ───────────────────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/pos/vendor/banks",
     *     summary="List all saved bank accounts",
     *     tags={"POS Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Banks retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function listBanks(Request $request): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            $banks = VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Banks retrieved successfully',
                'data'    => $banks,
                'count'   => $banks->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('API listBanks failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch banks'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/pos/vendor/banks",
     *     summary="Add a new bank account (max 5)",
     *     tags={"POS Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ifsc_code","account_number","account_holder"},
     *             @OA\Property(property="ifsc_code", type="string", example="SBIN0001234"),
     *             @OA\Property(property="account_number", type="string", example="123456789012"),
     *             @OA\Property(property="account_holder", type="string", example="John Doe"),
     *             @OA\Property(property="bank_name", type="string", example="State Bank of India"),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Bank added successfully"),
     *     @OA\Response(response=422, description="Validation error or max limit reached")
     * )
     */
    public function storeBank(Request $request): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            $existingCount = VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->count();

            if ($existingCount >= self::MAX_BANKS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum ' . self::MAX_BANKS . ' bank accounts allowed. Please delete one to add another.',
                ], 422);
            }

            $validated = $request->validate([
                'ifsc_code'      => 'required|string|size:11',
                'account_number' => [
                    'required', 'string', 'max:30',
                    Rule::unique('vendor_payment_details')
                        ->where(fn($q) => $q->where('vendor_id', $vendorId))
                ],
                'account_holder' => 'required|string|max:255',
                'bank_name'      => 'nullable|string|max:255',
                'is_default'     => 'nullable|boolean',
            ], [
                'account_number.unique' => 'This account number already exists. Please use a different account number.',
            ]);

            $isDefault = (bool) ($validated['is_default'] ?? false);
            if ($existingCount === 0) $isDefault = true;

            if ($isDefault) {
                VendorPaymentDetail::where('vendor_id', $vendorId)
                    ->where('type', 'bank')
                    ->update(['is_default' => false]);
            }

            $bank = VendorPaymentDetail::create([
                'vendor_id'      => $vendorId,
                'type'           => 'bank',
                'ifsc_code'      => strtoupper($validated['ifsc_code']),
                'account_number' => $validated['account_number'],
                'account_holder' => $validated['account_holder'],
                'bank_name'      => $validated['bank_name'] ?? null,
                'is_default'     => $isDefault,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank added successfully',
                'data'    => $bank,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('API storeBank failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to save bank'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/pos/vendor/banks/{id}",
     *     summary="Update an existing bank account",
     *     tags={"POS Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Bank updated successfully"),
     *     @OA\Response(response=404, description="Bank not found")
     * )
     */
    public function updateBank(Request $request, int $id): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            $bank = VendorPaymentDetail::where('id', $id)
                ->where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->first();

            if (!$bank) {
                return response()->json(['success' => false, 'message' => 'Bank not found'], 404);
            }

            $validated = $request->validate([
                'ifsc_code'      => 'required|string|size:11',
                'account_number' => [
                    'required', 'string', 'max:30',
                    Rule::unique('vendor_payment_details')
                        ->where(fn($q) => $q->where('vendor_id', $vendorId))
                        ->ignore($id)
                ],
                'account_holder' => 'required|string|max:255',
                'bank_name'      => 'nullable|string|max:255',
                'is_default'     => 'nullable|boolean',
            ], [
                'account_number.unique' => 'This account number already exists. Please use a different account number.',
            ]);

            $isDefault = (bool) ($validated['is_default'] ?? $bank->is_default);

            if ($isDefault && !$bank->is_default) {
                VendorPaymentDetail::where('vendor_id', $vendorId)
                    ->where('type', 'bank')
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $bank->update([
                'ifsc_code'      => strtoupper($validated['ifsc_code']),
                'account_number' => $validated['account_number'],
                'account_holder' => $validated['account_holder'],
                'bank_name'      => $validated['bank_name'] ?? null,
                'is_default'     => $isDefault,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank updated successfully',
                'data'    => $bank->fresh(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('API updateBank failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update bank'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/pos/vendor/banks/{id}",
     *     summary="Delete a bank account",
     *     tags={"POS Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Bank deleted successfully"),
     *     @OA\Response(response=404, description="Bank not found")
     * )
     */
    public function deleteBank(Request $request, int $id): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            $bank = VendorPaymentDetail::where('id', $id)
                ->where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->first();

            if (!$bank) {
                return response()->json(['success' => false, 'message' => 'Bank not found'], 404);
            }

            $wasDefault = $bank->is_default;
            $bank->delete();

            if ($wasDefault) {
                $next = VendorPaymentDetail::where('vendor_id', $vendorId)
                    ->where('type', 'bank')
                    ->orderBy('id')
                    ->first();
                $next?->update(['is_default' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bank removed successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('API deleteBank failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete bank'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/pos/vendor/banks/{id}/set-default",
     *     summary="Set a bank as default",
     *     tags={"POS Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Default bank updated"),
     *     @OA\Response(response=404, description="Bank not found")
     * )
     */
    public function setDefaultBank(Request $request, int $id): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            $bank = VendorPaymentDetail::where('id', $id)
                ->where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->first();

            if (!$bank) {
                return response()->json(['success' => false, 'message' => 'Bank not found'], 404);
            }

            VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->update(['is_default' => false]);

            $bank->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Default bank updated',
                'data'    => $bank->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('API setDefaultBank failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to set default bank'], 500);
        }
    }

    // ─── UPI ENDPOINTS ────────────────────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/pos/vendor/upi",
     *     summary="Get saved UPI details",
     *     tags={"POS Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="UPI details retrieved"),
     *     @OA\Response(response=404, description="No UPI details found")
     * )
     */
    public function getUpi(Request $request): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            $detail = VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'upi')
                ->first();

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'No UPI details found',
                    'data'    => null,
                ]);
            }

            $data = $detail->toArray();
            if (!empty($data['qr_image_path'])) {
                $data['qr_image_path'] = $detail->normalizedQrImagePath() ?? $data['qr_image_path'];
                $data['qr_image_url']  = $detail->qrImageUrl();
            } else {
                $data['qr_image_url'] = null;
            }

            return response()->json([
                'success' => true,
                'message' => 'UPI details retrieved successfully',
                'data'    => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('API getUpi failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch UPI details'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/pos/vendor/upi",
     *     summary="Save or update UPI details",
     *     tags={"POS Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"upi_id"},
     *                 @OA\Property(property="upi_id", type="string", example="vendor@upi"),
     *                 @OA\Property(property="qr_image", type="string", format="binary"),
     *                 @OA\Property(property="remove_qr", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="UPI details saved successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function storeUpi(Request $request): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            $request->validate([
                'upi_id'    => 'required|string|max:100',
                'qr_image'  => 'nullable|image|max:2048',
                'remove_qr' => 'nullable|integer|in:0,1',
            ]);

            $existing = VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'upi')
                ->first();

            $qrPath = null;

            if ($request->hasFile('qr_image')) {
                // Delete old QR if exists
                $existingQrPath = $existing?->normalizedQrImagePath();
                if ($existingQrPath && Storage::disk('public')->exists($existingQrPath)) {
                    Storage::disk('public')->delete($existingQrPath);
                }
                $qrPath = $request->file('qr_image')->store("vendor_qr/{$vendorId}", 'public');

            } elseif ($request->input('remove_qr') == '1') {
                // ✅ Explicitly remove QR
                $existingQrPath = $existing?->normalizedQrImagePath();
                if ($existingQrPath && Storage::disk('public')->exists($existingQrPath)) {
                    Storage::disk('public')->delete($existingQrPath);
                }
                $qrPath = null;

            } else {
                // Keep existing QR
                $qrPath = $existing?->normalizedQrImagePath();
            }

            $detail = VendorPaymentDetail::updateOrCreate(
                ['vendor_id' => $vendorId, 'type' => 'upi'],
                ['upi_id' => $request->upi_id, 'qr_image_path' => $qrPath]
            );

            $data = $detail->fresh()->toArray();
            if ($qrPath) {
                $data['qr_image_path'] = $detail->normalizedQrImagePath() ?? $data['qr_image_path'];
                $data['qr_image_url']  = $detail->qrImageUrl();
            } else {
                $data['qr_image_path'] = null;
                $data['qr_image_url']  = null;
            }

            return response()->json([
                'success' => true,
                'message' => 'UPI details saved successfully',
                'data'    => $data,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('API storeUpi failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to save UPI details'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/pos/vendor/upi/remove-qr",
     *     summary="Remove QR image from UPI details",
     *     tags={"POS Bookings"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="QR image removed successfully"),
     *     @OA\Response(response=404, description="UPI details not found")
     * )
     */
    public function removeQrImage(Request $request): JsonResponse
    {
        try {
            $vendorId = Auth::id();

            $detail = VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'upi')
                ->first();

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'UPI details not found',
                ], 404);
            }

            // Delete from storage
            $existingQrPath = $detail->normalizedQrImagePath();
            if ($existingQrPath && Storage::disk('public')->exists($existingQrPath)) {
                Storage::disk('public')->delete($existingQrPath);
            }

            // Clear from database
            $detail->update([
                'qr_image_path' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR image removed successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('API removeQrImage failed', [
                'vendor_id' => Auth::id(),
                'error'     => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove QR image',
            ], 500);
        }
    }
}