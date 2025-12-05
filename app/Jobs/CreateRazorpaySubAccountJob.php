<?php

namespace App\Jobs;

use App\Models\VendorKYC;
use App\Services\RazorpayService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CreateRazorpaySubAccountJob implements ShouldQueue
{
    use Queueable;

    protected VendorKYC $vendorKYC;

    /**
     * Create a new job instance.
     */
    public function __construct(VendorKYC $vendorKYC)
    {
        $this->vendorKYC = $vendorKYC;
        $this->onQueue('razorpay');
    }

    /**
     * Execute the job.
     */
    public function handle(RazorpayService $razorpayService): void
    {
        try {
            Log::info('CreateRazorpaySubAccountJob: Starting', [
                'vendor_kyc_id' => $this->vendorKYC->id,
                'vendor_id' => $this->vendorKYC->vendor_id,
            ]);

            // Check if sub-account already exists
            if ($this->vendorKYC->razorpay_subaccount_id) {
                Log::info('CreateRazorpaySubAccountJob: Sub-account already exists', [
                    'subaccount_id' => $this->vendorKYC->razorpay_subaccount_id,
                ]);
                return;
            }

            // Prepare sub-account data
            $subAccountData = [
                'business_type' => $this->mapBusinessType($this->vendorKYC->business_type),
                'business_name' => $this->vendorKYC->business_name,
                'email' => $this->vendorKYC->contact_email,
                'phone' => $this->vendorKYC->contact_phone,
                'legal_business_name' => $this->vendorKYC->legal_name,
                'customer_facing_business_name' => $this->vendorKYC->business_name,
                'profile' => [
                    'category' => 'advertising',
                    'subcategory' => 'outdoor_advertising',
                    'addresses' => [
                        'registered' => [
                            'street1' => $this->vendorKYC->address,
                            'city' => $this->vendorKYC->city ?? '',
                            'state' => $this->vendorKYC->state ?? '',
                            'postal_code' => $this->vendorKYC->pincode ?? '',
                            'country' => 'IN',
                        ],
                    ],
                ],
                'legal_info' => [
                    'pan' => $this->vendorKYC->pan_number,
                    'gst' => $this->vendorKYC->gst_number,
                ],
                'bank_account' => [
                    'ifsc_code' => $this->vendorKYC->ifsc,
                    'account_number' => $this->vendorKYC->account_number,
                    'beneficiary_name' => $this->vendorKYC->account_holder_name,
                    'account_type' => $this->vendorKYC->account_type,
                ],
                'tnc_accepted' => true,
            ];

            // Create sub-account via Razorpay
            $subAccount = $razorpayService->createSubAccount($subAccountData);

            // Update VendorKYC with sub-account ID
            $this->vendorKYC->update([
                'razorpay_subaccount_id' => $subAccount['id'],
                'verification_details' => array_merge($this->vendorKYC->verification_details ?? [], [
                    'razorpay_subaccount_created_at' => now()->toIso8601String(),
                    'razorpay_subaccount_status' => $subAccount['status'] ?? 'created',
                ]),
            ]);

            Log::info('CreateRazorpaySubAccountJob: Sub-account created successfully', [
                'vendor_kyc_id' => $this->vendorKYC->id,
                'subaccount_id' => $subAccount['id'],
            ]);

        } catch (\Exception $e) {
            Log::error('CreateRazorpaySubAccountJob: Failed to create sub-account', [
                'vendor_kyc_id' => $this->vendorKYC->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Store error in verification_details
            $this->vendorKYC->update([
                'verification_details' => array_merge($this->vendorKYC->verification_details ?? [], [
                    'razorpay_error' => $e->getMessage(),
                    'razorpay_error_at' => now()->toIso8601String(),
                ]),
            ]);

            throw $e;
        }
    }

    /**
     * Map business type to Razorpay format
     */
    protected function mapBusinessType(string $businessType): string
    {
        return match($businessType) {
            'individual' => 'individual',
            'proprietorship' => 'proprietorship',
            'partnership' => 'partnership',
            'pvt_ltd' => 'private_limited',
            'public_ltd' => 'public_limited',
            'llp' => 'llp',
            default => 'proprietorship',
        };
    }
}

