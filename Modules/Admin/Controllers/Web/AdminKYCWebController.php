<?php

namespace Modules\Admin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VendorKYC;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminKYCWebController extends Controller
{
    /**
     * List all KYC submissions
     * GET /admin/kyc
     */
    public function index(Request $request): View
    {
        $query = VendorKYC::with(['vendor:id,name,email,phone', 'verifier:id,name']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('verification_status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'LIKE', "%{$search}%")
                  ->orWhere('pan_number', 'LIKE', "%{$search}%")
                  ->orWhere('gst_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('vendor', function ($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'submitted_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $kycs = $query->paginate(20)->withQueryString();

        // Get stats
        $stats = [
            'pending' => VendorKYC::pending()->count(),
            'under_review' => VendorKYC::underReview()->count(),
            'approved' => VendorKYC::approved()->count(),
            'rejected' => VendorKYC::rejected()->count(),
            'resubmission_required' => VendorKYC::resubmissionRequired()->count(),
            'total' => VendorKYC::count(),
        ];

        return view('admin.kyc.index', compact('kycs', 'stats'));
    }

    /**
     * Show KYC details
     * GET /admin/kyc/{id}
     */
    public function show($id): View
    {
        $kyc = VendorKYC::with(['vendor:id,name,email,phone,created_at', 'verifier:id,name'])
            ->findOrFail($id);

        return view('admin.kyc.show', compact('kyc'));
    }
}
