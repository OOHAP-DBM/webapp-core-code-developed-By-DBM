<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Modules\KYC\Models\VendorKYC;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminKYCReviewController extends Controller
{
    /**
     * Display a listing of KYC reviews with payout status
     * GET /admin/vendor/kyc-reviews
     */
    public function index(Request $request): View
    {
        $query = VendorKYC::with(['vendor', 'verifier']);

        // Filter by payout status
        if ($request->filled('payout_status')) {
            $query->where('payout_status', $request->payout_status);
        }

        // Filter by verification status
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('pan_number', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                      $vendorQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Default sort: newest first
        $query->orderBy('submitted_at', 'desc');

        $kycs = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'pending_verification' => VendorKYC::where('payout_status', 'pending_verification')->count(),
            'verified' => VendorKYC::where('payout_status', 'verified')->count(),
            'rejected' => VendorKYC::where('payout_status', 'rejected')->count(),
            'failed' => VendorKYC::where('payout_status', 'failed')->count(),
        ];

        return view('admin.vendor.kyc_reviews', compact('kycs', 'stats'));
    }

    /**
     * Display the specified KYC review detail
     * GET /admin/vendor/kyc-reviews/{id}
     */
    public function show(int $id): View
    {
        $kyc = VendorKYC::with(['vendor', 'verifier'])
            ->findOrFail($id);

        return view('admin.vendor.kyc_review_detail', compact('kyc'));
    }
}

