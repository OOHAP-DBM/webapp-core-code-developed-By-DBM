<?php

namespace App\Services;

use App\Models\{SettlementBatch, VendorLedger, BookingPayment, User};
use Illuminate\Support\Facades\{DB, Log};
use Exception;

class PaymentSettlementService
{
    protected RazorpayPayoutService $razorpayPayoutService;

    public function __construct(RazorpayPayoutService $razorpayPayoutService)
    {
        $this->razorpayPayoutService = $razorpayPayoutService;
    }

    /**
     * Record payment in vendor ledger (called after commission calculation)
     * RULE: Admin commission deducted first, then vendor earning recorded
     * 
     * @param BookingPayment $bookingPayment
     * @return array [VendorLedger commission, VendorLedger earning]
     */
    public function recordPaymentInLedger(BookingPayment $bookingPayment): array
    {
        $vendor = $bookingPayment->booking->vendor;
        $vendorKyc = $vendor->vendorKYC;

        // Check if KYC is complete and verified
        $isKycVerified = $vendorKyc 
            && $vendorKyc->isApproved() 
            && $vendorKyc->payout_status === 'verified' 
            && $vendorKyc->razorpay_subaccount_id;

        // RULE 1: Admin commission deducted first
        $commissionEntry = VendorLedger::recordTransaction([
            'vendor_id' => $vendor->id,
            'transaction_type' => 'commission_deduction',
            'amount' => -$bookingPayment->admin_commission_amount, // Negative for debit
            'booking_payment_id' => $bookingPayment->id,
            'related_type' => get_class($bookingPayment->booking),
            'related_id' => $bookingPayment->booking->id,
            'description' => "Admin commission deduction for Booking #{$bookingPayment->booking_id}",
            'metadata' => [
                'booking_id' => $bookingPayment->booking_id,
                'gross_amount' => $bookingPayment->gross_amount,
                'commission_rate' => $bookingPayment->metadata['commission_rate'] ?? null,
            ],
        ]);

        // RULE 2: Record vendor earning
        $earningEntry = VendorLedger::recordTransaction([
            'vendor_id' => $vendor->id,
            'transaction_type' => 'booking_earning',
            'amount' => $bookingPayment->vendor_payout_amount, // Positive for credit
            'booking_payment_id' => $bookingPayment->id,
            'related_type' => get_class($bookingPayment->booking),
            'related_id' => $bookingPayment->booking->id,
            'description' => "Earning from Booking #{$bookingPayment->booking_id}",
            // RULE 3: If KYC incomplete, hold the amount
            'is_on_hold' => !$isKycVerified,
            'metadata' => [
                'booking_id' => $bookingPayment->booking_id,
                'vendor_payout_amount' => $bookingPayment->vendor_payout_amount,
                'kyc_incomplete_reason' => $isKycVerified ? null : $this->getKycIncompleteReason($vendorKyc),
            ],
        ]);

        Log::info('Payment recorded in vendor ledger', [
            'vendor_id' => $vendor->id,
            'booking_payment_id' => $bookingPayment->id,
            'commission_entry_id' => $commissionEntry->id,
            'earning_entry_id' => $earningEntry->id,
            'is_on_hold' => $earningEntry->is_on_hold,
        ]);

        return [$commissionEntry, $earningEntry];
    }

    /**
     * Get reason why KYC is incomplete
     */
    protected function getKycIncompleteReason($vendorKyc): string
    {
        if (!$vendorKyc) {
            return 'KYC not submitted';
        }
        if (!$vendorKyc->isApproved()) {
            return "KYC not approved (Status: {$vendorKyc->verification_status})";
        }
        if ($vendorKyc->payout_status !== 'verified') {
            return "Razorpay account not verified (Status: {$vendorKyc->payout_status})";
        }
        if (!$vendorKyc->razorpay_subaccount_id) {
            return 'Razorpay sub-account not created';
        }
        return 'Unknown reason';
    }

    /**
     * Release held amounts when vendor completes KYC
     */
    public function releaseHeldAmounts(int $vendorId, int $releasedBy): array
    {
        $heldEntries = VendorLedger::where('vendor_id', $vendorId)
            ->onHold()
            ->get();

        $released = [];
        foreach ($heldEntries as $entry) {
            $entry->releaseHold($releasedBy, 'KYC verification completed');
            $released[] = $entry->id;
        }

        Log::info('Released held amounts', [
            'vendor_id' => $vendorId,
            'released_count' => count($released),
            'released_by' => $releasedBy,
        ]);

        return $released;
    }

    /**
     * Create settlement batch for a date range
     */
    public function createSettlementBatch(\Carbon\Carbon $periodStart, \Carbon\Carbon $periodEnd, int $createdBy, ?string $batchName = null): SettlementBatch
    {
        // Get all booking payments in period
        $bookingPayments = BookingPayment::whereBetween('created_at', [
            $periodStart->startOfDay(),
            $periodEnd->endOfDay()
        ])->get();

        // Calculate totals
        $totals = [
            'total_bookings_amount' => $bookingPayments->sum('gross_amount'),
            'total_admin_commission' => $bookingPayments->sum('admin_commission_amount'),
            'total_vendor_payout' => $bookingPayments->sum('vendor_payout_amount'),
            'total_pg_fees' => $bookingPayments->sum('pg_fee_amount'),
            'total_bookings_count' => $bookingPayments->count(),
        ];

        // Count vendors and pending KYC
        $vendors = $bookingPayments->pluck('booking.vendor')->unique('id');
        $pendingKycCount = $vendors->filter(function ($vendor) {
            $kyc = $vendor->vendorKYC;
            return !$kyc || !$kyc->isApproved() || $kyc->payout_status !== 'verified';
        })->count();

        $batch = SettlementBatch::create([
            'batch_name' => $batchName ?? "Settlement {$periodStart->format('M d')} - {$periodEnd->format('M d, Y')}",
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'created_by' => $createdBy,
            'vendors_count' => $vendors->count(),
            'pending_kyc_count' => $pendingKycCount,
            ...$totals,
        ]);

        Log::info('Settlement batch created', [
            'batch_id' => $batch->id,
            'batch_reference' => $batch->batch_reference,
            'period' => "{$periodStart->format('Y-m-d')} to {$periodEnd->format('Y-m-d')}",
            'totals' => $totals,
        ]);

        return $batch;
    }

    /**
     * Submit batch for approval
     */
    public function submitForApproval(SettlementBatch $batch): bool
    {
        if (!$batch->isDraft()) {
            throw new Exception('Only draft batches can be submitted for approval');
        }

        return $batch->markAsPendingApproval();
    }

    /**
     * Approve settlement batch
     */
    public function approveBatch(SettlementBatch $batch, int $approvedBy, ?string $notes = null): bool
    {
        if (!$batch->isPendingApproval()) {
            throw new Exception('Only pending batches can be approved');
        }

        return $batch->markAsApproved($approvedBy, $notes);
    }

    /**
     * Process approved settlement batch
     * This will initiate Razorpay transfers for all KYC-verified vendors
     */
    public function processBatch(SettlementBatch $batch): array
    {
        if (!$batch->isApproved()) {
            throw new Exception('Only approved batches can be processed');
        }

        $batch->markAsProcessing();

        $results = [
            'success' => [],
            'failed' => [],
            'held' => [],
        ];

        try {
            DB::beginTransaction();

            // Get all booking payments in batch period
            $bookingPayments = BookingPayment::whereBetween('created_at', [
                $batch->period_start->startOfDay(),
                $batch->period_end->endOfDay()
            ])->where('vendor_payout_status', 'pending')->get();

            foreach ($bookingPayments as $payment) {
                try {
                    $vendor = $payment->booking->vendor;
                    $vendorKyc = $vendor->vendorKYC;

                    // Check KYC status
                    $isKycVerified = $vendorKyc 
                        && $vendorKyc->isApproved() 
                        && $vendorKyc->payout_status === 'verified' 
                        && $vendorKyc->razorpay_subaccount_id;

                    if ($isKycVerified) {
                        // RULE: KYC verified → Auto payout via Razorpay
                        $transferResponse = $this->razorpayPayoutService->createTransfer(
                            paymentId: $payment->razorpay_payment_id,
                            accountId: $vendorKyc->razorpay_subaccount_id,
                            amount: $payment->vendor_payout_amount,
                            currency: 'INR',
                            notes: [
                                'booking_id' => $payment->booking_id,
                                'booking_payment_id' => $payment->id,
                                'settlement_batch_id' => $batch->id,
                                'batch_reference' => $batch->batch_reference,
                            ]
                        );

                        // Update booking payment
                        $payment->update([
                            'vendor_payout_status' => 'auto_paid',
                            'payout_mode' => 'razorpay_transfer',
                            'payout_reference' => $transferResponse['id'] ?? null,
                            'paid_at' => now(),
                            'metadata' => array_merge($payment->metadata ?? [], [
                                'settlement_batch_id' => $batch->id,
                                'razorpay_transfer_response' => $transferResponse,
                            ]),
                        ]);

                        // Record payout in ledger
                        VendorLedger::recordTransaction([
                            'vendor_id' => $vendor->id,
                            'transaction_type' => 'payout',
                            'amount' => -$payment->vendor_payout_amount,
                            'booking_payment_id' => $payment->id,
                            'settlement_batch_id' => $batch->id,
                            'description' => "Auto payout for Booking #{$payment->booking_id} via Razorpay",
                            'metadata' => [
                                'razorpay_transfer_id' => $transferResponse['id'] ?? null,
                                'batch_reference' => $batch->batch_reference,
                            ],
                        ]);

                        $results['success'][] = [
                            'payment_id' => $payment->id,
                            'vendor_id' => $vendor->id,
                            'amount' => $payment->vendor_payout_amount,
                            'transfer_id' => $transferResponse['id'] ?? null,
                        ];

                    } else {
                        // RULE: KYC incomplete → Hold in admin account, mark for manual payout
                        $payment->update([
                            'vendor_payout_status' => 'pending_manual_payout',
                            'metadata' => array_merge($payment->metadata ?? [], [
                                'settlement_batch_id' => $batch->id,
                                'manual_payout_reason' => $this->getKycIncompleteReason($vendorKyc),
                                'held_at' => now()->toIso8601String(),
                            ]),
                        ]);

                        $results['held'][] = [
                            'payment_id' => $payment->id,
                            'vendor_id' => $vendor->id,
                            'amount' => $payment->vendor_payout_amount,
                            'reason' => $this->getKycIncompleteReason($vendorKyc),
                        ];
                    }

                } catch (Exception $e) {
                    Log::error('Failed to process payment in settlement batch', [
                        'payment_id' => $payment->id,
                        'batch_id' => $batch->id,
                        'error' => $e->getMessage(),
                    ]);

                    $results['failed'][] = [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            // Mark batch as completed
            if (empty($results['failed'])) {
                $batch->markAsCompleted();
            } else {
                $batch->markAsFailed($results['failed']);
            }

            Log::info('Settlement batch processed', [
                'batch_id' => $batch->id,
                'batch_reference' => $batch->batch_reference,
                'success_count' => count($results['success']),
                'held_count' => count($results['held']),
                'failed_count' => count($results['failed']),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            $batch->markAsFailed(['error' => $e->getMessage()]);
            throw $e;
        }

        return $results;
    }

    /**
     * Get settlement statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_batches' => SettlementBatch::count(),
            'pending_approval' => SettlementBatch::pendingApproval()->count(),
            'approved_batches' => SettlementBatch::approved()->count(),
            'processing_batches' => SettlementBatch::processing()->count(),
            'completed_batches' => SettlementBatch::completed()->count(),
            'failed_batches' => SettlementBatch::where('status', 'failed')->count(),
            'total_settled_amount' => SettlementBatch::completed()->sum('total_vendor_payout'),
            'pending_settlement_amount' => SettlementBatch::whereIn('status', ['draft', 'pending_approval', 'approved'])->sum('total_vendor_payout'),
        ];
    }

    /**
     * Get vendor ledger summary
     */
    public function getVendorLedgerSummary(int $vendorId, ?\Carbon\Carbon $startDate = null, ?\Carbon\Carbon $endDate = null): array
    {
        $query = VendorLedger::where('vendor_id', $vendorId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $entries = $query->orderBy('transaction_date', 'desc')->get();
        $balance = VendorLedger::calculateVendorBalance($vendorId);

        return [
            'balance' => $balance,
            'entries' => $entries,
            'total_entries' => $entries->count(),
        ];
    }
}
