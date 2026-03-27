<?php
// Modules/POS/Controllers/Web/VendorPaymentDetailController.php

namespace Modules\POS\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Modules\POS\Models\VendorPaymentDetail;
use App\Models\User;

class VendorPaymentDetailController extends Controller
{
    const MAX_BANKS = 5;

    public function __construct()
    {
        $this->middleware(['auth', 'role:vendor|admin|superadmin']);
    }

    // ─── Vendor ID resolver (unchanged from original) ─────────────────────────
    private function resolveEffectiveVendorId(Request $request): int
    {
        $user = Auth::user();
        if (!$user) abort(401, 'Unauthenticated');

        $isAdminContext = method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin', 'superadmin', 'super_admin']);

        if (!$isAdminContext) return (int) $user->id;

        $sessionKey = 'pos.selected_vendor_id';
        $requestedVendorId = $request->input('vendor_id') ?? $request->query('vendor_id');

        if (!empty($requestedVendorId)) {
            $vendor = User::query()->whereKey((int) $requestedVendorId)
                ->whereHas('roles', fn($q) => $q->where('name', 'vendor'))
                ->first();
            if (!$vendor) abort(422, 'Invalid vendor selected for POS context.');
            if ($request->hasSession()) $request->session()->put($sessionKey, (int) $vendor->id);
            return (int) $vendor->id;
        }

        $sessionVendorId = $request->hasSession() ? $request->session()->get($sessionKey) : null;
        if (!empty($sessionVendorId)) {
            $exists = User::query()->whereKey((int) $sessionVendorId)
                ->whereHas('roles', fn($q) => $q->where('name', 'vendor'))
                ->exists();
            if ($exists) return (int) $sessionVendorId;
        }

        $fallbackVendorId = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'vendor'))
            ->orderBy('id')->value('id');
        if (!$fallbackVendorId) abort(422, 'No vendor available for POS context.');
        if ($request->hasSession()) $request->session()->put($sessionKey, (int) $fallbackVendorId);

        return (int) $fallbackVendorId;
    }

    // ─── BANK ENDPOINTS ───────────────────────────────────────────────────────

    /**
     * GET /vendor/pos/api/payment-details/banks
     * List all saved banks for vendor (max 5)
     */
    public function listBanks(Request $request): JsonResponse
    {
        try {
            $vendorId = $this->resolveEffectiveVendorId($request);
            $banks = VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get();

            return response()->json(['success' => true, 'data' => $banks, 'count' => $banks->count()]);
        } catch (\Exception $e) {
            Log::error('listBanks failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch banks'], 500);
        }
    }

    /**
     * POST /vendor/pos/api/payment-details/banks
     * Add a new bank (max 5 per vendor)
     */
    public function storeBank(Request $request): JsonResponse
    {
        try {
            $vendorId = $this->resolveEffectiveVendorId($request);

            // Enforce max 5 bank limit
            $existingCount = VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'bank')->count();

            if ($existingCount >= self::MAX_BANKS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum ' . self::MAX_BANKS . ' bank accounts allowed. Please delete one to add another.',
                ], 422);
            }

            $validated = $request->validate([
                'ifsc_code'      => 'required|string|size:11',
                'account_number' => 'required|string|max:30',
                'account_holder' => 'required|string|max:255',
                'bank_name'      => 'nullable|string|max:255',
                'is_default'     => 'nullable|boolean',
            ]);

            $isDefault = (bool) ($validated['is_default'] ?? false);

            // If first bank ever, auto-set as default
            if ($existingCount === 0) $isDefault = true;

            // If setting this as default, clear others
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

            return response()->json(['success' => true, 'data' => $bank, 'message' => 'Bank added successfully'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('storeBank failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to save bank'], 500);
        }
    }

    /**
     * PUT /vendor/pos/api/payment-details/banks/{id}
     * Update an existing bank record
     */
    public function updateBank(Request $request, int $id): JsonResponse
    {
        try {
            $vendorId = $this->resolveEffectiveVendorId($request);

            // Ownership check
            $bank = VendorPaymentDetail::where('id', $id)
                ->where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->first();

            if (!$bank) {
                return response()->json(['success' => false, 'message' => 'Bank not found'], 404);
            }

            $validated = $request->validate([
                'ifsc_code'      => 'required|string|size:11',
                'account_number' => 'required|string|max:30',
                'account_holder' => 'required|string|max:255',
                'bank_name'      => 'nullable|string|max:255',
                'is_default'     => 'nullable|boolean',
            ]);

            $isDefault = (bool) ($validated['is_default'] ?? $bank->is_default);

            if ($isDefault && !$bank->is_default) {
                // Clear default from others before setting this one
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

            return response()->json(['success' => true, 'data' => $bank->fresh(), 'message' => 'Bank updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('updateBank failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update bank'], 500);
        }
    }

    /**
     * DELETE /vendor/pos/api/payment-details/banks/{id}
     * Delete a bank record
     */
    public function deleteBank(Request $request, int $id): JsonResponse
    {
        try {
            $vendorId = $this->resolveEffectiveVendorId($request);

            $bank = VendorPaymentDetail::where('id', $id)
                ->where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->first();

            if (!$bank) {
                return response()->json(['success' => false, 'message' => 'Bank not found'], 404);
            }

            $wasDefault = $bank->is_default;
            $bank->delete();

            // If deleted bank was default, assign default to next available bank
            if ($wasDefault) {
                $next = VendorPaymentDetail::where('vendor_id', $vendorId)
                    ->where('type', 'bank')
                    ->orderBy('id')
                    ->first();
                $next?->update(['is_default' => true]);
            }

            return response()->json(['success' => true, 'message' => 'Bank removed successfully']);
        } catch (\Exception $e) {
            Log::error('deleteBank failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete bank'], 500);
        }
    }

    /**
     * POST /vendor/pos/api/payment-details/banks/{id}/set-default
     * Set a specific bank as the default
     */
    public function setDefaultBank(Request $request, int $id): JsonResponse
    {
        try {
            $vendorId = $this->resolveEffectiveVendorId($request);

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

            return response()->json(['success' => true, 'data' => $bank->fresh(), 'message' => 'Default bank updated']);
        } catch (\Exception $e) {
            Log::error('setDefaultBank failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to set default bank'], 500);
        }
    }

    // ─── ORIGINAL show() and store() kept for backward compatibility ──────────
    // These handle the old ?type=bank|upi GET and generic POST.
    // The old bank POST path now routes to storeBank internally.

    /**
     * GET /vendor/pos/api/payment-details?type=bank|upi
     * Backward-compatible: returns default bank OR upi detail
     */
    public function show(Request $request): JsonResponse
    {
        $type     = $request->get('type', 'bank');
        $vendorId = $this->resolveEffectiveVendorId($request);

        if ($type === 'bank') {
            // Return list of all banks (new behaviour — frontend now uses /banks list)
            $banks = VendorPaymentDetail::where('vendor_id', $vendorId)
                ->where('type', 'bank')
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get();

            // Maintain backward compat: also return single "data" key = default bank
            $default = $banks->firstWhere('is_default', true) ?? $banks->first();
            return response()->json([
                'success' => true,
                'data'    => $default,       // single default (old consumers)
                'banks'   => $banks,         // full list (new consumers)
            ]);
        }

        // UPI — unchanged
        $detail = VendorPaymentDetail::where('vendor_id', $vendorId)
            ->where('type', 'upi')
            ->first();

        if (!$detail) {
            return response()->json(['success' => false, 'data' => null]);
        }

        $data = $detail->toArray();
        if (!empty($data['qr_image_path'])) {
            $data['qr_image_path'] = $detail->normalizedQrImagePath() ?? $data['qr_image_path'];
            $data['qr_image_url']  = $detail->qrImageUrl();
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * POST /vendor/pos/api/payment-details
     * Backward-compatible store — delegates bank to storeBank logic, UPI unchanged
     */
    public function store(Request $request): JsonResponse
    {
        $type = $request->input('type');

        if ($type === 'bank') {
            // Delegate to the new storeBank — handles limit + default logic
            return $this->storeBank($request);
        }

        if ($type === 'upi') {
            // UPI logic completely unchanged from original
            $request->validate([
                'upi_id'   => 'required|string|max:100',
                'qr_image' => 'nullable|image|max:2048',
            ]);

            $vendorId = $this->resolveEffectiveVendorId($request);
            $existing = VendorPaymentDetail::where('vendor_id', $vendorId)->where('type', 'upi')->first();
            $qrPath   = null;

            if ($request->hasFile('qr_image')) {
                $existingQrPath = $existing?->normalizedQrImagePath();
                if ($existingQrPath && Storage::disk('public')->exists($existingQrPath)) {
                    Storage::disk('public')->delete($existingQrPath);
                }
                $qrPath = $request->file('qr_image')->store("vendor_qr/{$vendorId}", 'public');
            } else {
                $qrPath = $existing?->normalizedQrImagePath();
            }

            $detail = VendorPaymentDetail::updateOrCreate(
                ['vendor_id' => $vendorId, 'type' => 'upi'],
                ['upi_id' => $request->upi_id, 'qr_image_path' => $qrPath]
            );

            $data = $detail->toArray();
            if ($qrPath) {
                $data['qr_image_path'] = $detail->normalizedQrImagePath() ?? $data['qr_image_path'];
                $data['qr_image_url']  = $detail->qrImageUrl();
            } else {
                $data['qr_image_url'] = null;
            }

            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid payment details type'], 422);
    }
}