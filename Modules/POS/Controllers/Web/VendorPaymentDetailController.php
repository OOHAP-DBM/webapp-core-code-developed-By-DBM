<?php

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
    private function resolveEffectiveVendorId(Request $request): int
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $isAdminContext = method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['admin', 'superadmin', 'super_admin']);

        if (!$isAdminContext) {
            return (int) $user->id;
        }

        $sessionKey = 'pos.selected_vendor_id';
        $requestedVendorId = $request->input('vendor_id') ?? $request->query('vendor_id');

        if (!empty($requestedVendorId)) {
            $vendor = User::query()
                ->whereKey((int) $requestedVendorId)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'vendor');
                })
                ->first();

            if (!$vendor) {
                abort(422, 'Invalid vendor selected for POS context.');
            }

            if ($request->hasSession()) {
                $request->session()->put($sessionKey, (int) $vendor->id);
            }

            return (int) $vendor->id;
        }

        $sessionVendorId = $request->hasSession() ? $request->session()->get($sessionKey) : null;
        if (!empty($sessionVendorId)) {
            $exists = User::query()
                ->whereKey((int) $sessionVendorId)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'vendor');
                })
                ->exists();

            if ($exists) {
                return (int) $sessionVendorId;
            }
        }

        $fallbackVendorId = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'vendor');
            })
            ->orderBy('id')
            ->value('id');

        if (!$fallbackVendorId) {
            abort(422, 'No vendor available for POS context.');
        }

        if ($request->hasSession()) {
            $request->session()->put($sessionKey, (int) $fallbackVendorId);
        }

        return (int) $fallbackVendorId;
    }

    public function __construct()
    {
        $this->middleware(['auth', 'role:vendor|admin|superadmin']);
    }

    /**
     * GET /vendor/pos/api/payment-details?type=bank|upi
     * Returns saved payment details for the authenticated vendor
     */
    public function show(Request $request): JsonResponse
    {
        $type     = $request->get('type', 'bank');
        $vendorId = $this->resolveEffectiveVendorId($request);

        $detail = VendorPaymentDetail::where('vendor_id', $vendorId)
            ->where('type', $type)
            ->first();

        if (!$detail) {
            return response()->json(['success' => false, 'data' => null]);
        }

        // Append full URL for QR image
        $data = $detail->toArray();
        if (!empty($data['qr_image_path'])) {
            $data['qr_image_path'] = $detail->normalizedQrImagePath() ?? $data['qr_image_path'];
            $data['qr_image_url'] = $detail->qrImageUrl();
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * POST /vendor/pos/api/payment-details
     * Save (create or update) payment details for the vendor
     * Supports both JSON (bank) and multipart/form-data (upi with QR upload)
     */
    public function store(Request $request): JsonResponse
    {
        $type     = $request->input('type');
        $vendorId = $this->resolveEffectiveVendorId($request);

        if ($type === 'bank') {
            $request->validate([
                'ifsc_code'      => 'required|string|size:11',
                'account_number' => 'required|string|max:20',
                'account_holder' => 'required|string|max:255',
                'bank_name'      => 'nullable|string|max:255',
            ]);

            $detail = VendorPaymentDetail::updateOrCreate(
                ['vendor_id' => $vendorId, 'type' => 'bank'],
                [
                    'ifsc_code'      => strtoupper($request->ifsc_code),
                    'account_number' => $request->account_number,
                    'account_holder' => $request->account_holder,
                    'bank_name'      => $request->bank_name,
                ]
            );

            return response()->json(['success' => true, 'data' => $detail->toArray()]);
        }

        if ($type === 'upi') {
            $request->validate([
                'upi_id'   => 'required|string|max:100',
                'qr_image' => 'nullable|image|max:2048',
            ]);

            $qrPath = null;
            $existing = VendorPaymentDetail::where('vendor_id', $vendorId)->where('type', 'upi')->first();

            if ($request->hasFile('qr_image')) {
                // Delete old QR if exists
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
                [
                    'upi_id'        => $request->upi_id,
                    'qr_image_path' => $qrPath,
                ]
            );

            $data                = $detail->toArray();
            if ($qrPath) {
                $data['qr_image_path'] = $detail->normalizedQrImagePath() ?? $data['qr_image_path'];
                $data['qr_image_url'] = $detail->qrImageUrl();
            } else {
                $data['qr_image_url'] = null;
            }

            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid payment details type'], 422);
    }
}