<?php

namespace Modules\POS\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Modules\POS\Models\VendorPaymentDetail;

class VendorPaymentDetailController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active_role:vendor']);
    }

    /**
     * GET /vendor/pos/api/payment-details?type=bank|upi
     * Returns saved payment details for the authenticated vendor
     */
    public function show(Request $request): JsonResponse
    {
        $type     = $request->get('type', 'bank');
        $vendorId = Auth::id();

        $detail = VendorPaymentDetail::where('vendor_id', $vendorId)
            ->where('type', $type)
            ->first();

        if (!$detail) {
            return response()->json(['success' => false, 'data' => null]);
        }

        // Append full URL for QR image
        $data = $detail->toArray();
        if (!empty($data['qr_image_path'])) {
            $data['qr_image_url'] = Storage::disk('public')->url($data['qr_image_path']);
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
        $vendorId = Auth::id();

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
                if ($existing && $existing->qr_image_path && Storage::disk('public')->exists($existing->qr_image_path)) {
                    Storage::disk('public')->delete($existing->qr_image_path);
                }
                $qrPath = $request->file('qr_image')->store("vendor_qr/{$vendorId}", 'public');
            } else {
                $qrPath = $existing?->qr_image_path;
            }

            $detail = VendorPaymentDetail::updateOrCreate(
                ['vendor_id' => $vendorId, 'type' => 'upi'],
                [
                    'upi_id'        => $request->upi_id,
                    'qr_image_path' => $qrPath,
                ]
            );

            $data                = $detail->toArray();
            $data['qr_image_url'] = $qrPath ? Storage::disk('public')->url($qrPath) : null;

            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid payment details type'], 422);
    }
}