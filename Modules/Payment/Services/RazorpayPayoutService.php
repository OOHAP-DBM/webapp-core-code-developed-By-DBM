<?php

namespace App\Services;

use Modules\KYC\Models\VendorKYC;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RazorpayPayoutService
{
    protected string $keyId;
    protected string $keySecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key_id');
        $this->keySecret = config('services.razorpay.key_secret');
        $this->baseUrl = config('services.razorpay.base_url', 'https://api.razorpay.com/v1');
    }

    /**
     * Create Razorpay Route sub-account for vendor
     * 
     * @param VendorKYC $vendorKyc
     * @return array Sub-account data
     * @throws Exception
     */
    public function createSubAccount(VendorKYC $vendorKyc): array
    {
        try {
            // Check if sub-account already exists
            if ($vendorKyc->razorpay_subaccount_id) {
                Log::info('Sub-account already exists', [
                    'vendor_kyc_id' => $vendorKyc->id,
                    'subaccount_id' => $vendorKyc->razorpay_subaccount_id,
                ]);
                
                return [
                    'id' => $vendorKyc->razorpay_subaccount_id,
                    'status' => 'existing',
                ];
            }

            // Map business type to Razorpay format
            $businessType = $this->mapBusinessType($vendorKyc->business_type);

            // Prepare sub-account payload
            $payload = [
                'email' => $vendorKyc->contact_email,
                'phone' => $vendorKyc->contact_phone,
                'type' => 'route',
                'legal_business_name' => $vendorKyc->legal_name,
                'business_type' => $businessType,
                'contact_name' => $vendorKyc->contact_name,
                'profile' => [
                    'category' => 'advertising',
                    'subcategory' => 'outdoor_advertising',
                    'description' => $vendorKyc->business_name,
                    'addresses' => [
                        'registered' => [
                            'street1' => $vendorKyc->address,
                            'city' => $vendorKyc->city ?? '',
                            'state' => $vendorKyc->state ?? '',
                            'postal_code' => $vendorKyc->pincode ?? '',
                            'country' => 'IN',
                        ],
                    ],
                ],
                'legal_info' => [
                    'pan' => $vendorKyc->pan_number,
                ],
                'brand' => [
                    'color' => '#0066cc',
                ],
                'notes' => [
                    'vendor_id' => $vendorKyc->vendor_id,
                    'kyc_id' => $vendorKyc->id,
                    'business_name' => $vendorKyc->business_name,
                ],
                'tnc_accepted' => true,
            ];

            // Add GST if available
            if ($vendorKyc->gst_number) {
                $payload['legal_info']['gst'] = $vendorKyc->gst_number;
            }

            Log::info('Creating Razorpay sub-account', [
                'vendor_kyc_id' => $vendorKyc->id,
                'payload' => $payload,
            ]);

            // Call Razorpay API
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("{$this->baseUrl}/accounts", $payload);

            $responseData = $response->json();

            Log::info('Razorpay sub-account API response', [
                'vendor_kyc_id' => $vendorKyc->id,
                'status_code' => $response->status(),
                'response' => $responseData,
            ]);

            if ($response->failed()) {
                $errorMessage = $responseData['error']['description'] ?? 'Failed to create Razorpay sub-account';
                
                Log::error('Razorpay sub-account creation failed', [
                    'vendor_kyc_id' => $vendorKyc->id,
                    'error' => $errorMessage,
                    'error_details' => $responseData,
                ]);

                // Store error in verification_details
                $vendorKyc->update([
                    'payout_status' => 'failed',
                    'verification_details' => array_merge($vendorKyc->verification_details ?? [], [
                        'razorpay_error' => $errorMessage,
                        'razorpay_error_details' => $responseData['error'] ?? null,
                        'razorpay_error_at' => now()->toIso8601String(),
                    ]),
                ]);

                throw new Exception($errorMessage);
            }

            // Extract account ID
            $accountId = $responseData['id'] ?? null;
            $accountStatus = $responseData['status'] ?? 'created';

            if (!$accountId) {
                throw new Exception('No account ID in Razorpay response');
            }

            // Update VendorKYC with sub-account details
            $vendorKyc->update([
                'razorpay_subaccount_id' => $accountId,
                'payout_status' => $accountStatus === 'activated' ? 'verified' : 'pending_verification',
                'verification_details' => array_merge($vendorKyc->verification_details ?? [], [
                    'razorpay_account_status' => $accountStatus,
                    'razorpay_subaccount_created_at' => now()->toIso8601String(),
                    'razorpay_response' => $responseData,
                ]),
            ]);

            Log::info('Razorpay sub-account created successfully', [
                'vendor_kyc_id' => $vendorKyc->id,
                'account_id' => $accountId,
                'status' => $accountStatus,
            ]);

            return $responseData;

        } catch (Exception $e) {
            Log::error('Exception in createSubAccount', [
                'vendor_kyc_id' => $vendorKyc->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Fetch sub-account details from Razorpay
     * 
     * @param string $accountId
     * @return array
     * @throws Exception
     */
    public function fetchSubAccount(string $accountId): array
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/accounts/{$accountId}");

            $responseData = $response->json();

            if ($response->failed()) {
                throw new Exception(
                    $responseData['error']['description'] ?? 'Failed to fetch sub-account'
                );
            }

            return $responseData;

        } catch (Exception $e) {
            Log::error('Failed to fetch sub-account', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Add bank account to sub-account
     * 
     * @param VendorKYC $vendorKyc
     * @return array
     * @throws Exception
     */
    public function addBankAccount(VendorKYC $vendorKyc): array
    {
        try {
            if (!$vendorKyc->razorpay_subaccount_id) {
                throw new Exception('Sub-account ID not found');
            }

            $payload = [
                'ifsc_code' => $vendorKyc->ifsc,
                'account_number' => $vendorKyc->account_number,
                'beneficiary_name' => $vendorKyc->account_holder_name,
            ];

            Log::info('Adding bank account to sub-account', [
                'vendor_kyc_id' => $vendorKyc->id,
                'account_id' => $vendorKyc->razorpay_subaccount_id,
            ]);

            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("{$this->baseUrl}/accounts/{$vendorKyc->razorpay_subaccount_id}/bank_account", $payload);

            $responseData = $response->json();

            if ($response->failed()) {
                $errorMessage = $responseData['error']['description'] ?? 'Failed to add bank account';
                
                Log::error('Failed to add bank account', [
                    'vendor_kyc_id' => $vendorKyc->id,
                    'error' => $errorMessage,
                ]);

                throw new Exception($errorMessage);
            }

            Log::info('Bank account added successfully', [
                'vendor_kyc_id' => $vendorKyc->id,
                'bank_account_id' => $responseData['id'] ?? null,
            ]);

            return $responseData;

        } catch (Exception $e) {
            Log::error('Exception in addBankAccount', [
                'vendor_kyc_id' => $vendorKyc->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle account verified webhook
     * 
     * @param array $payload Webhook payload
     * @return void
     */
    public function handleAccountVerified(array $payload): void
    {
        try {
            $accountId = $payload['account']['id'] ?? null;
            
            if (!$accountId) {
                Log::warning('No account ID in verified webhook payload', ['payload' => $payload]);
                return;
            }

            Log::info('Processing account.verified webhook', [
                'account_id' => $accountId,
            ]);

            // Find VendorKYC by razorpay_subaccount_id
            $vendorKyc = VendorKYC::where('razorpay_subaccount_id', $accountId)->first();

            if (!$vendorKyc) {
                Log::warning('VendorKYC not found for account', ['account_id' => $accountId]);
                return;
            }

            // Update payout status
            $vendorKyc->update([
                'payout_status' => 'verified',
                'verification_details' => array_merge($vendorKyc->verification_details ?? [], [
                    'razorpay_verified_at' => now()->toIso8601String(),
                    'razorpay_webhook_payload' => $payload,
                ]),
            ]);

            // Update vendor status if KYC is approved
            if ($vendorKyc->isApproved()) {
                $vendorKyc->vendor->update(['status' => 'kyc_verified']);
                
                Log::info('Vendor status updated to kyc_verified', [
                    'vendor_id' => $vendorKyc->vendor_id,
                    'kyc_id' => $vendorKyc->id,
                ]);
            }

            Log::info('Account verified webhook processed successfully', [
                'account_id' => $accountId,
                'kyc_id' => $vendorKyc->id,
            ]);

        } catch (Exception $e) {
            Log::error('Error processing account.verified webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }

    /**
     * Handle account rejected webhook
     * 
     * @param array $payload Webhook payload
     * @return void
     */
    public function handleAccountRejected(array $payload): void
    {
        try {
            $accountId = $payload['account']['id'] ?? null;
            $reason = $payload['account']['rejection_reason'] ?? 'Unknown reason';
            $notes = $payload['account']['notes'] ?? [];
            
            if (!$accountId) {
                Log::warning('No account ID in rejected webhook payload', ['payload' => $payload]);
                return;
            }

            Log::info('Processing account.rejected webhook', [
                'account_id' => $accountId,
                'reason' => $reason,
            ]);

            // Find VendorKYC by razorpay_subaccount_id
            $vendorKyc = VendorKYC::where('razorpay_subaccount_id', $accountId)->first();

            if (!$vendorKyc) {
                Log::warning('VendorKYC not found for account', ['account_id' => $accountId]);
                return;
            }

            // Update payout status
            $vendorKyc->update([
                'payout_status' => 'rejected',
                'verification_details' => array_merge($vendorKyc->verification_details ?? [], [
                    'razorpay_rejected_at' => now()->toIso8601String(),
                    'razorpay_rejection_reason' => $reason,
                    'razorpay_rejection_notes' => $notes,
                    'razorpay_webhook_payload' => $payload,
                ]),
            ]);

            // Update vendor status
            $vendorKyc->vendor->update(['status' => 'kyc_rejected']);
            
            Log::info('Vendor status updated to kyc_rejected', [
                'vendor_id' => $vendorKyc->vendor_id,
                'kyc_id' => $vendorKyc->id,
                'reason' => $reason,
            ]);

            Log::info('Account rejected webhook processed successfully', [
                'account_id' => $accountId,
                'kyc_id' => $vendorKyc->id,
            ]);

        } catch (Exception $e) {
            Log::error('Error processing account.rejected webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }

    /**
     * Map business type to Razorpay format
     * 
     * @param string $businessType
     * @return string
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

    /**
     * Create transfer to vendor sub-account via Razorpay Route API
     * https://razorpay.com/docs/api/route/transfers/
     * 
     * @param string $paymentId Razorpay payment ID
     * @param string $accountId Razorpay sub-account ID
     * @param float $amount Transfer amount in INR
     * @param string $currency
     * @param array $notes
     * @return array Transfer response
     * @throws Exception
     */
    public function createTransfer(
        string $paymentId,
        string $accountId,
        float $amount,
        string $currency = 'INR',
        array $notes = []
    ): array {
        try {
            // Convert amount to paise (Razorpay uses smallest currency unit)
            $amountInPaise = (int) ($amount * 100);

            $payload = [
                'account' => $accountId,
                'amount' => $amountInPaise,
                'currency' => $currency,
                'notes' => $notes,
            ];

            Log::info('Creating Razorpay transfer', [
                'payment_id' => $paymentId,
                'account_id' => $accountId,
                'amount_inr' => $amount,
                'amount_paise' => $amountInPaise,
            ]);

            // POST /v1/payments/{payment_id}/transfers
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("{$this->baseUrl}/payments/{$paymentId}/transfers", $payload);

            $responseData = $response->json();

            Log::info('Razorpay transfer API response', [
                'payment_id' => $paymentId,
                'status_code' => $response->status(),
                'response' => $responseData,
            ]);

            if ($response->failed()) {
                $errorMessage = $responseData['error']['description'] ?? 'Failed to create transfer';
                
                Log::error('Razorpay transfer creation failed', [
                    'payment_id' => $paymentId,
                    'error' => $errorMessage,
                    'error_details' => $responseData,
                ]);

                throw new Exception($errorMessage);
            }

            Log::info('Razorpay transfer created successfully', [
                'payment_id' => $paymentId,
                'transfer_id' => $responseData['id'] ?? null,
                'amount' => $amount,
            ]);

            return $responseData;

        } catch (Exception $e) {
            Log::error('Exception in createTransfer', [
                'payment_id' => $paymentId,
                'account_id' => $accountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}


