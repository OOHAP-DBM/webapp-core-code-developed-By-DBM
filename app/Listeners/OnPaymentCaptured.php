<?php

namespace App\Listeners;

use App\Events\PaymentCaptured;
use App\Jobs\ScheduleBookingConfirmJob;
use App\Services\CommissionService;
use App\Services\RazorpayPayoutService;
use App\Services\PaymentSettlementService;
use Modules\Bookings\Services\BookingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ManualPayoutRequired;

class OnPaymentCaptured
{
    protected BookingService $bookingService;
    protected CommissionService $commissionService;
    protected RazorpayPayoutService $razorpayPayoutService;
    protected PaymentSettlementService $settlementService;

    /**
     * Create the event listener.
     */
    public function __construct(
        BookingService $bookingService, 
        CommissionService $commissionService,
        RazorpayPayoutService $razorpayPayoutService,
        PaymentSettlementService $settlementService
    ) {
        $this->bookingService = $bookingService;
        $this->commissionService = $commissionService;
        $this->razorpayPayoutService = $razorpayPayoutService;
        $this->settlementService = $settlementService;
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentCaptured $event): void
    {
        try {
            Log::info('OnPaymentCaptured: Processing', [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId,
                'amount' => $event->amount
            ]);

            // Find booking by razorpay_order_id
            $booking = $this->bookingService->findByRazorpayOrderId($event->orderId);

            if (!$booking) {
                Log::error('OnPaymentCaptured: Booking not found', [
                    'order_id' => $event->orderId
                ]);
                return;
            }

            // Calculate and record commission
            [$bookingPayment, $commissionLog] = $this->commissionService->calculateAndRecord(
                $booking,
                $event->paymentId,
                $event->orderId
            );

            Log::info('OnPaymentCaptured: Commission recorded', [
                'booking_id' => $booking->id,
                'booking_payment_id' => $bookingPayment->id,
                'commission_log_id' => $commissionLog->id,
                'vendor_payout_amount' => $bookingPayment->vendor_payout_amount,
            ]);

            // ====== RECORD IN VENDOR LEDGER ======
            // RULE: Admin commission first, then vendor earning
            [$commissionEntry, $earningEntry] = $this->settlementService->recordPaymentInLedger($bookingPayment);

            Log::info('OnPaymentCaptured: Recorded in vendor ledger', [
                'commission_entry_id' => $commissionEntry->id,
                'earning_entry_id' => $earningEntry->id,
                'is_on_hold' => $earningEntry->is_on_hold,
            ]);

            // ====== FUND SPLIT LOGIC: Check Vendor KYC Status ======
            $vendor = $booking->vendor;
            $vendorKyc = $vendor->vendorKYC;

            // Check if vendor is KYC verified (both manual approval + Razorpay verification)
            $isKycVerified = $vendorKyc 
                && $vendorKyc->isApproved() 
                && $vendorKyc->payout_status === 'verified' 
                && $vendorKyc->razorpay_subaccount_id
                && $vendor->status === 'kyc_verified';

            if ($isKycVerified) {
                // Vendor is KYC verified -> Auto-transfer to vendor sub-account
                $this->processAutoTransfer($bookingPayment, $vendorKyc, $event->paymentId);
            } else {
                // Vendor not verified -> Manual payout required
                $this->processPendingManualPayout($bookingPayment, $vendor, $vendorKyc);
            }

            // Dispatch job to confirm booking
            ScheduleBookingConfirmJob::dispatch($booking->id, $event->paymentId)
                ->onQueue('bookings');

            Log::info('OnPaymentCaptured: Job dispatched successfully', [
                'booking_id' => $booking->id,
                'payment_id' => $event->paymentId
            ]);

        } catch (\Exception $e) {
            Log::error('OnPaymentCaptured: Failed to process payment capture', [
                'payment_id' => $event->paymentId,
                'order_id' => $event->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process auto-transfer to vendor sub-account
     */
    protected function processAutoTransfer($bookingPayment, $vendorKyc, $paymentId): void
    {
        try {
            Log::info('Processing auto-transfer to vendor sub-account', [
                'booking_payment_id' => $bookingPayment->id,
                'vendor_subaccount_id' => $vendorKyc->razorpay_subaccount_id,
                'amount' => $bookingPayment->vendor_payout_amount,
            ]);

            // Transfer funds via Razorpay Route API
            $transferResponse = $this->razorpayPayoutService->createTransfer(
                paymentId: $paymentId,
                accountId: $vendorKyc->razorpay_subaccount_id,
                amount: $bookingPayment->vendor_payout_amount,
                currency: 'INR',
                notes: [
                    'booking_id' => $bookingPayment->booking_id,
                    'booking_payment_id' => $bookingPayment->id,
                    'vendor_id' => $vendorKyc->vendor_id,
                    'transfer_type' => 'vendor_payout',
                ]
            );

            // Update BookingPayment with transfer details
            $bookingPayment->update([
                'vendor_payout_status' => 'auto_paid',
                'payout_mode' => 'razorpay_transfer',
                'payout_reference' => $transferResponse['id'] ?? null,
                'razorpay_transfer_ids' => [$transferResponse['id'] ?? null],
                'paid_at' => now(),
                'metadata' => array_merge($bookingPayment->metadata ?? [], [
                    'auto_transfer' => true,
                    'transfer_response' => $transferResponse,
                    'transferred_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info('Auto-transfer completed successfully', [
                'booking_payment_id' => $bookingPayment->id,
                'transfer_id' => $transferResponse['id'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Auto-transfer failed, falling back to manual payout', [
                'booking_payment_id' => $bookingPayment->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback to pending_manual_payout if transfer fails
            $this->processPendingManualPayout($bookingPayment, $vendorKyc->vendor, $vendorKyc);
        }
    }

    /**
     * Process pending manual payout for unverified vendors
     */
    protected function processPendingManualPayout($bookingPayment, $vendor, $vendorKyc): void
    {
        try {
            // Determine reason for manual payout
            $reason = 'Vendor KYC not verified';
            $kycDetails = [];

            if (!$vendorKyc) {
                $reason = 'Vendor KYC not submitted';
            } elseif (!$vendorKyc->isApproved()) {
                $reason = 'Vendor KYC not approved (Status: ' . $vendorKyc->verification_status . ')';
                $kycDetails['kyc_status'] = $vendorKyc->verification_status;
            } elseif ($vendorKyc->payout_status !== 'verified') {
                $reason = 'Razorpay account not verified (Status: ' . $vendorKyc->payout_status . ')';
                $kycDetails['payout_status'] = $vendorKyc->payout_status;
            } elseif (!$vendorKyc->razorpay_subaccount_id) {
                $reason = 'Razorpay sub-account not created';
            }

            Log::info('Marking payout for manual processing', [
                'booking_payment_id' => $bookingPayment->id,
                'vendor_id' => $vendor->id,
                'reason' => $reason,
            ]);

            // Update BookingPayment status
            $bookingPayment->update([
                'vendor_payout_status' => 'pending_manual_payout',
                'metadata' => array_merge($bookingPayment->metadata ?? [], [
                    'manual_payout_required' => true,
                    'manual_payout_reason' => $reason,
                    'kyc_details' => $kycDetails,
                    'flagged_at' => now()->toIso8601String(),
                ]),
            ]);

            // Notify vendor about pending payout
            // Note: ManualPayoutRequired notification to be created
            // $vendor->notify(new ManualPayoutRequired($bookingPayment, $reason));

            // Notify admin about manual payout required
            // $admins = User::where('role', 'admin')->get();
            // Notification::send($admins, new ManualPayoutRequired($bookingPayment, $reason));

            Log::info('Manual payout flagged successfully', [
                'booking_payment_id' => $bookingPayment->id,
                'vendor_id' => $vendor->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process pending manual payout', [
                'booking_payment_id' => $bookingPayment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
