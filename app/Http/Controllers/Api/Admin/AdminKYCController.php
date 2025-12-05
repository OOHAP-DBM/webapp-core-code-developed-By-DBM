<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorKYC;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminKYCController extends Controller
{
    /**
     * List all KYC submissions with filters
     * GET /api/v1/admin/kyc
     */
    public function index(Request $request): JsonResponse
    {
        $query = VendorKYC::with(['vendor:id,name,email,phone', 'verifier:id,name']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('verification_status', $request->status);
        }

        // Search by vendor name, email, or business name
        if ($request->has('search')) {
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

        // Sort by submitted date (default: newest first)
        $sortBy = $request->get('sort_by', 'submitted_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $kycs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $kycs->map(function ($kyc) {
                return [
                    'id' => $kyc->id,
                    'vendor' => [
                        'id' => $kyc->vendor->id,
                        'name' => $kyc->vendor->name,
                        'email' => $kyc->vendor->email,
                        'phone' => $kyc->vendor->phone,
                    ],
                    'business_name' => $kyc->business_name,
                    'business_type' => $kyc->business_type,
                    'pan_number' => $kyc->pan_number,
                    'gst_number' => $kyc->gst_number,
                    'verification_status' => $kyc->verification_status,
                    'status_label' => $kyc->status_label,
                    'submitted_at' => $kyc->submitted_at,
                    'verified_at' => $kyc->verified_at,
                    'verifier' => $kyc->verifier ? [
                        'id' => $kyc->verifier->id,
                        'name' => $kyc->verifier->name,
                    ] : null,
                ];
            }),
            'meta' => [
                'current_page' => $kycs->currentPage(),
                'last_page' => $kycs->lastPage(),
                'per_page' => $kycs->perPage(),
                'total' => $kycs->total(),
            ],
        ]);
    }

    /**
     * Get KYC details
     * GET /api/v1/admin/kyc/{id}
     */
    public function show($id): JsonResponse
    {
        $kyc = VendorKYC::with(['vendor:id,name,email,phone,created_at', 'verifier:id,name'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $kyc->id,
                'vendor' => [
                    'id' => $kyc->vendor->id,
                    'name' => $kyc->vendor->name,
                    'email' => $kyc->vendor->email,
                    'phone' => $kyc->vendor->phone,
                    'registered_at' => $kyc->vendor->created_at,
                ],
                'business_type' => $kyc->business_type,
                'business_name' => $kyc->business_name,
                'gst_number' => $kyc->gst_number,
                'pan_number' => $kyc->pan_number,
                'legal_name' => $kyc->legal_name,
                'contact_name' => $kyc->contact_name,
                'contact_email' => $kyc->contact_email,
                'contact_phone' => $kyc->contact_phone,
                'address' => $kyc->address,
                'city' => $kyc->city,
                'state' => $kyc->state,
                'pincode' => $kyc->pincode,
                'account_holder_name' => $kyc->account_holder_name,
                'masked_account_number' => $kyc->masked_account_number,
                'ifsc' => $kyc->ifsc,
                'bank_name' => $kyc->bank_name,
                'account_type' => $kyc->account_type,
                'verification_status' => $kyc->verification_status,
                'status_label' => $kyc->status_label,
                'verification_details' => $kyc->verification_details,
                'submitted_at' => $kyc->submitted_at,
                'verified_at' => $kyc->verified_at,
                'verifier' => $kyc->verifier ? [
                    'id' => $kyc->verifier->id,
                    'name' => $kyc->verifier->name,
                ] : null,
                'razorpay_subaccount_id' => $kyc->razorpay_subaccount_id,
                'documents' => [
                    'pan_card' => $kyc->getFirstMediaUrl('pan_card'),
                    'aadhar_card' => $kyc->getFirstMediaUrl('aadhar_card'),
                    'gst_certificate' => $kyc->getFirstMediaUrl('gst_certificate'),
                    'business_proof' => $kyc->getFirstMediaUrl('business_proof'),
                    'cancelled_cheque' => $kyc->getFirstMediaUrl('cancelled_cheque'),
                ],
                'created_at' => $kyc->created_at,
                'updated_at' => $kyc->updated_at,
            ],
        ]);
    }

    /**
     * Approve KYC
     * POST /api/v1/admin/kyc/{id}/approve
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $kyc = VendorKYC::findOrFail($id);

            if ($kyc->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC is already approved',
                ], 400);
            }

            // Approve KYC
            $kyc->approve(auth()->id());

            // Update vendor status
            $kyc->vendor->update(['status' => 'active']);

            DB::commit();

            Log::info('Admin approved vendor KYC', [
                'admin_id' => auth()->id(),
                'vendor_id' => $kyc->vendor_id,
                'kyc_id' => $kyc->id,
            ]);

            // TODO: Send notification/email to vendor

            return response()->json([
                'success' => true,
                'message' => 'KYC approved successfully. Vendor is now active.',
                'data' => [
                    'id' => $kyc->id,
                    'status' => $kyc->verification_status,
                    'verified_at' => $kyc->verified_at,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Admin KYC approval failed', [
                'admin_id' => auth()->id(),
                'kyc_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve KYC',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Reject KYC
     * POST /api/v1/admin/kyc/{id}/reject
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $kyc = VendorKYC::findOrFail($id);

            if ($kyc->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reject an approved KYC',
                ], 400);
            }

            // Reject KYC
            $kyc->reject(auth()->id(), $request->reason);

            // Update vendor status
            $kyc->vendor->update(['status' => 'kyc_rejected']);

            DB::commit();

            Log::info('Admin rejected vendor KYC', [
                'admin_id' => auth()->id(),
                'vendor_id' => $kyc->vendor_id,
                'kyc_id' => $kyc->id,
                'reason' => $request->reason,
            ]);

            // TODO: Send notification/email to vendor

            return response()->json([
                'success' => true,
                'message' => 'KYC rejected successfully. Vendor has been notified.',
                'data' => [
                    'id' => $kyc->id,
                    'status' => $kyc->verification_status,
                    'verified_at' => $kyc->verified_at,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Admin KYC rejection failed', [
                'admin_id' => auth()->id(),
                'kyc_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject KYC',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Request resubmission with remarks
     * POST /api/v1/admin/kyc/{id}/request-resubmission
     */
    public function requestResubmission(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'remarks' => 'required|array|min:1',
            'remarks.*' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $kyc = VendorKYC::findOrFail($id);

            if ($kyc->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot request resubmission for an approved KYC',
                ], 400);
            }

            // Request resubmission
            $kyc->requestResubmission(auth()->id(), $request->remarks);

            // Update vendor status
            $kyc->vendor->update(['status' => 'kyc_resubmission_required']);

            DB::commit();

            Log::info('Admin requested KYC resubmission', [
                'admin_id' => auth()->id(),
                'vendor_id' => $kyc->vendor_id,
                'kyc_id' => $kyc->id,
                'remarks_count' => count($request->remarks),
            ]);

            // TODO: Send notification/email to vendor

            return response()->json([
                'success' => true,
                'message' => 'Resubmission requested successfully. Vendor has been notified.',
                'data' => [
                    'id' => $kyc->id,
                    'status' => $kyc->verification_status,
                    'remarks' => $request->remarks,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Admin KYC resubmission request failed', [
                'admin_id' => auth()->id(),
                'kyc_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to request resubmission',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get KYC statistics
     * GET /api/v1/admin/kyc/stats
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'pending' => VendorKYC::pending()->count(),
            'under_review' => VendorKYC::underReview()->count(),
            'approved' => VendorKYC::approved()->count(),
            'rejected' => VendorKYC::rejected()->count(),
            'resubmission_required' => VendorKYC::resubmissionRequired()->count(),
            'total' => VendorKYC::count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
