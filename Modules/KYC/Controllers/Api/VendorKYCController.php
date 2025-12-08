<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Modules\KYC\Models\VendorKYC;
use Modules\Users\Models\User;
use App\Jobs\CreateRazorpaySubAccountJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VendorKYCController extends Controller
{
    /**
     * Submit or update vendor KYC
     * POST /api/v1/vendor/kyc
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Business Information
            'business_type' => 'required|in:individual,proprietorship,partnership,pvt_ltd,public_ltd,llp',
            'business_name' => 'required|string|max:200',
            'gst_number' => 'nullable|string|size:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'pan_number' => 'required|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'legal_name' => 'required|string|max:200',
            
            // Contact Information
            'contact_name' => 'required|string|max:100',
            'contact_email' => 'required|email|max:100',
            'contact_phone' => 'required|string|max:15',
            
            // Address
            'address' => 'required|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|size:6|regex:/^[0-9]{6}$/',
            
            // Bank Details
            'account_holder_name' => 'required|string|max:200',
            'account_number' => 'required|string|min:8|max:18',
            'account_number_confirmation' => 'required|same:account_number',
            'ifsc' => 'required|string|size:11|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_name' => 'required|string|max:100',
            'account_type' => 'required|in:savings,current',
            
            // Documents
            'pan_card' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'aadhar_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'gst_certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'business_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'cancelled_cheque' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
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

            $vendor = auth()->user();

            // Check if KYC already exists
            $vendorKYC = VendorKYC::where('vendor_id', $vendor->id)->first();

            if ($vendorKYC) {
                // Update existing KYC (for resubmission)
                if (!in_array($vendorKYC->verification_status, ['rejected', 'resubmission_required', 'pending'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'KYC is already submitted and under review or approved',
                    ], 400);
                }

                $vendorKYC->update($validator->validated());
            } else {
                // Create new KYC
                $vendorKYC = VendorKYC::create(array_merge(
                    $validator->validated(),
                    ['vendor_id' => $vendor->id]
                ));
            }

            // Handle document uploads
            $documentFields = ['pan_card', 'aadhar_card', 'gst_certificate', 'business_proof', 'cancelled_cheque'];
            
            foreach ($documentFields as $field) {
                if ($request->hasFile($field)) {
                    // Remove old media if exists
                    $vendorKYC->clearMediaCollection($field);
                    
                    // Add new media
                    $vendorKYC->addMediaFromRequest($field)
                        ->toMediaCollection($field);
                }
            }

            // Mark as submitted
            $vendorKYC->markAsSubmitted();

            // Update vendor status
            $vendor->update(['status' => 'kyc_submitted']);

            // Dispatch job to create Razorpay sub-account
            CreateRazorpaySubAccountJob::dispatch($vendorKYC);

            DB::commit();

            Log::info('Vendor KYC submitted', [
                'vendor_id' => $vendor->id,
                'kyc_id' => $vendorKYC->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'KYC submitted successfully. You will be notified once verified.',
                'data' => [
                    'kyc_id' => $vendorKYC->id,
                    'status' => $vendorKYC->verification_status,
                    'submitted_at' => $vendorKYC->submitted_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Vendor KYC submission failed', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit KYC. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get vendor KYC status
     * GET /api/v1/vendor/kyc/status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $vendor = auth()->user();
        $vendorKYC = VendorKYC::where('vendor_id', $vendor->id)->first();

        if (!$vendorKYC) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_kyc' => false,
                    'status' => 'not_submitted',
                    'message' => 'KYC not yet submitted',
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'has_kyc' => true,
                'status' => $vendorKYC->verification_status,
                'status_label' => $vendorKYC->status_label,
                'submitted_at' => $vendorKYC->submitted_at,
                'verified_at' => $vendorKYC->verified_at,
                'business_name' => $vendorKYC->business_name,
                'business_type' => $vendorKYC->business_type,
                'razorpay_subaccount_id' => $vendorKYC->razorpay_subaccount_id,
                'completion_status' => $vendorKYC->completion_status,
                'verification_details' => $vendorKYC->verification_details,
                'documents' => [
                    'pan_card' => $vendorKYC->getFirstMediaUrl('pan_card'),
                    'aadhar_card' => $vendorKYC->getFirstMediaUrl('aadhar_card'),
                    'gst_certificate' => $vendorKYC->getFirstMediaUrl('gst_certificate'),
                    'business_proof' => $vendorKYC->getFirstMediaUrl('business_proof'),
                    'cancelled_cheque' => $vendorKYC->getFirstMediaUrl('cancelled_cheque'),
                ],
            ],
        ]);
    }

    /**
     * Get KYC details for editing
     * GET /api/v1/vendor/kyc
     */
    public function getDetails(Request $request): JsonResponse
    {
        $vendor = auth()->user();
        $vendorKYC = VendorKYC::where('vendor_id', $vendor->id)->first();

        if (!$vendorKYC) {
            return response()->json([
                'success' => false,
                'message' => 'KYC not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $vendorKYC->id,
                'business_type' => $vendorKYC->business_type,
                'business_name' => $vendorKYC->business_name,
                'gst_number' => $vendorKYC->gst_number,
                'pan_number' => $vendorKYC->pan_number,
                'legal_name' => $vendorKYC->legal_name,
                'contact_name' => $vendorKYC->contact_name,
                'contact_email' => $vendorKYC->contact_email,
                'contact_phone' => $vendorKYC->contact_phone,
                'address' => $vendorKYC->address,
                'city' => $vendorKYC->city,
                'state' => $vendorKYC->state,
                'pincode' => $vendorKYC->pincode,
                'account_holder_name' => $vendorKYC->account_holder_name,
                'masked_account_number' => $vendorKYC->masked_account_number,
                'ifsc' => $vendorKYC->ifsc,
                'bank_name' => $vendorKYC->bank_name,
                'account_type' => $vendorKYC->account_type,
                'verification_status' => $vendorKYC->verification_status,
                'status_label' => $vendorKYC->status_label,
                'documents' => [
                    'pan_card' => $vendorKYC->getFirstMediaUrl('pan_card'),
                    'aadhar_card' => $vendorKYC->getFirstMediaUrl('aadhar_card'),
                    'gst_certificate' => $vendorKYC->getFirstMediaUrl('gst_certificate'),
                    'business_proof' => $vendorKYC->getFirstMediaUrl('business_proof'),
                    'cancelled_cheque' => $vendorKYC->getFirstMediaUrl('cancelled_cheque'),
                ],
            ],
        ]);
    }
}

