<?php

namespace App\Services;

use App\Models\{PayoutRequest, BookingPayment, User};
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;
use Exception;

/**
 * PROMPT 58: Payout Service
 * 
 * Handles vendor payout request creation, calculation, and processing
 */
class PayoutService
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }
    /**
     * Create a payout request for a vendor
     *
     * @param User $vendor
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @param array $options [adjustment_amount, adjustment_reason, gst_percentage]
     * @return PayoutRequest
     * @throws Exception
     */
    public function createPayoutRequest(
        User $vendor,
        Carbon $periodStart,
        Carbon $periodEnd,
        array $options = []
    ): PayoutRequest {
        return DB::transaction(function () use ($vendor, $periodStart, $periodEnd, $options) {
            // Fetch completed booking payments for the vendor in the period
            $bookingPayments = BookingPayment::with('booking')
                ->whereHas('booking', function ($query) use ($vendor) {
                    $query->where('vendor_id', $vendor->id);
                })
                ->where('status', 'captured')
                ->where('vendor_payout_status', 'pending')
                ->whereBetween('created_at', [
                    $periodStart->startOfDay(),
                    $periodEnd->endOfDay()
                ])
                ->get();

            if ($bookingPayments->isEmpty()) {
                throw new Exception('No pending payments found for the selected period');
            }

            // Calculate totals
            $bookingRevenue = $bookingPayments->sum('gross_amount');
            $commissionAmount = $bookingPayments->sum('admin_commission_amount');
            $pgFees = $bookingPayments->sum('pg_fee_amount');
            $bookingsCount = $bookingPayments->count();

            // Calculate average commission percentage
            $avgCommissionPercentage = $bookingRevenue > 0 
                ? ($commissionAmount / $bookingRevenue) * 100 
                : 0;

            // Get adjustment (can be positive or negative)
            $adjustmentAmount = $options['adjustment_amount'] ?? 0;
            $adjustmentReason = $options['adjustment_reason'] ?? null;

            // Calculate net before GST
            $netBeforeGst = $bookingRevenue - $commissionAmount - $pgFees + $adjustmentAmount;

            // Calculate GST and TDS using TaxService
            $gstResult = $this->taxService->calculateGST($netBeforeGst, [
                'applies_to' => 'payout',
                'vendor_id' => $vendor->id,
            ]);
            $gstAmount = $gstResult['gst_amount'];
            $gstPercentage = $gstResult['gst_rate'];

            // Calculate TDS if applicable
            $tdsResult = $this->taxService->calculateTDS($netBeforeGst, [
                'applies_to' => 'payout',
                'vendor_type' => 'professional',
            ]);
            $tdsAmount = $tdsResult['applies'] ? $tdsResult['tds_amount'] : 0;

            // Final payout amount
            $finalPayoutAmount = $netBeforeGst + $gstAmount - $tdsAmount;

            // Get vendor's bank details
            $vendorKyc = $vendor->vendorKYC;
            $bankDetails = [
                'bank_name' => $vendorKyc->bank_name ?? null,
                'account_number' => $vendorKyc->account_number ?? null,
                'account_holder_name' => $vendorKyc->account_holder_name ?? null,
                'ifsc_code' => $vendorKyc->ifsc_code ?? null,
                'upi_id' => $vendorKyc->upi_id ?? null,
            ];

            // Create payout request
            $payoutRequest = PayoutRequest::create([
                'vendor_id' => $vendor->id,
                'booking_revenue' => $bookingRevenue,
                'commission_amount' => $commissionAmount,
                'commission_percentage' => round($avgCommissionPercentage, 2),
                'pg_fees' => $pgFees,
                'adjustment_amount' => $adjustmentAmount,
                'adjustment_reason' => $adjustmentReason,
                'gst_amount' => round($gstAmount, 2),
                'gst_percentage' => $gstPercentage,
                'final_payout_amount' => round($finalPayoutAmount, 2),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'bookings_count' => $bookingsCount,
                'status' => PayoutRequest::STATUS_DRAFT,
                'booking_ids' => $bookingPayments->pluck('id')->toArray(),
                'metadata' => [
                    'booking_payment_ids' => $bookingPayments->pluck('id')->toArray(),
                    'calculation_timestamp' => now()->toIso8601String(),
                    'booking_details' => $bookingPayments->map(function ($bp) {
                        return [
                            'id' => $bp->id,
                            'booking_id' => $bp->booking_id,
                            'gross_amount' => $bp->gross_amount,
                            'commission' => $bp->admin_commission_amount,
                            'payout' => $bp->vendor_payout_amount,
                        ];
                    })->toArray(),
                ],
                ...$bankDetails,
            ]);

            Log::info('Payout request created', [
                'request_id' => $payoutRequest->id,
                'vendor_id' => $vendor->id,
                'amount' => $finalPayoutAmount,
                'bookings_count' => $bookingsCount,
            ]);

            return $payoutRequest;
        });
    }

    /**
     * Submit payout request for approval
     *
     * @param PayoutRequest $payoutRequest
     * @param User $user
     * @return bool
     * @throws Exception
     */
    public function submitForApproval(PayoutRequest $payoutRequest, User $user): bool
    {
        if (!$payoutRequest->canSubmit()) {
            throw new Exception('Payout request cannot be submitted');
        }

        if ($payoutRequest->final_payout_amount <= 0) {
            throw new Exception('Payout amount must be greater than zero');
        }

        $success = $payoutRequest->submit($user);

        if ($success) {
            Log::info('Payout request submitted', [
                'request_id' => $payoutRequest->id,
                'vendor_id' => $payoutRequest->vendor_id,
                'submitted_by' => $user->id,
            ]);

            // TODO: Send notification to admin
        }

        return $success;
    }

    /**
     * Approve payout request (Admin action)
     *
     * @param PayoutRequest $payoutRequest
     * @param User $admin
     * @param string|null $notes
     * @return bool
     * @throws Exception
     */
    public function approvePayoutRequest(PayoutRequest $payoutRequest, User $admin, ?string $notes = null): bool
    {
        if (!$payoutRequest->canApprove()) {
            throw new Exception('Payout request cannot be approved');
        }

        $success = $payoutRequest->approve($admin, $notes);

        if ($success) {
            Log::info('Payout request approved', [
                'request_id' => $payoutRequest->id,
                'vendor_id' => $payoutRequest->vendor_id,
                'approved_by' => $admin->id,
                'amount' => $payoutRequest->final_payout_amount,
            ]);

            // TODO: Send notification to vendor
        }

        return $success;
    }

    /**
     * Reject payout request (Admin action)
     *
     * @param PayoutRequest $payoutRequest
     * @param User $admin
     * @param string $reason
     * @return bool
     * @throws Exception
     */
    public function rejectPayoutRequest(PayoutRequest $payoutRequest, User $admin, string $reason): bool
    {
        if (!$payoutRequest->canReject()) {
            throw new Exception('Payout request cannot be rejected');
        }

        if (empty($reason)) {
            throw new Exception('Rejection reason is required');
        }

        $success = $payoutRequest->reject($admin, $reason);

        if ($success) {
            Log::info('Payout request rejected', [
                'request_id' => $payoutRequest->id,
                'vendor_id' => $payoutRequest->vendor_id,
                'rejected_by' => $admin->id,
                'reason' => $reason,
            ]);

            // TODO: Send notification to vendor
        }

        return $success;
    }

    /**
     * Process payout settlement (Admin action)
     *
     * @param PayoutRequest $payoutRequest
     * @param string $payoutMode
     * @param string $payoutReference
     * @param string|null $notes
     * @return bool
     * @throws Exception
     */
    public function processPayoutSettlement(
        PayoutRequest $payoutRequest,
        string $payoutMode,
        string $payoutReference,
        ?string $notes = null
    ): bool {
        return DB::transaction(function () use ($payoutRequest, $payoutMode, $payoutReference, $notes) {
            if (!$payoutRequest->isApproved()) {
                throw new Exception('Only approved payout requests can be processed');
            }

            // Mark as processing
            $payoutRequest->markProcessing();

            try {
                // Mark all associated booking payments as completed
                $bookingPayments = $payoutRequest->getBookingPayments();
                
                foreach ($bookingPayments as $payment) {
                    $payment->update([
                        'vendor_payout_status' => 'completed',
                        'payout_mode' => $payoutMode,
                        'payout_reference' => $payoutReference,
                        'paid_at' => now(),
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'payout_request_id' => $payoutRequest->id,
                            'settled_at' => now()->toIso8601String(),
                        ]),
                    ]);
                }

                // Mark payout request as completed
                $payoutRequest->markCompleted($payoutMode, $payoutReference, $notes);

                Log::info('Payout settlement processed', [
                    'request_id' => $payoutRequest->id,
                    'vendor_id' => $payoutRequest->vendor_id,
                    'amount' => $payoutRequest->final_payout_amount,
                    'payout_mode' => $payoutMode,
                    'reference' => $payoutReference,
                ]);

                // TODO: Send notification to vendor
                // TODO: Generate settlement receipt PDF

                return true;
            } catch (Exception $e) {
                $payoutRequest->markFailed($e->getMessage());
                
                Log::error('Payout settlement failed', [
                    'request_id' => $payoutRequest->id,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Get vendor's pending payout summary
     *
     * @param User $vendor
     * @return array
     */
    public function getVendorPayoutSummary(User $vendor): array
    {
        // Get pending payments
        $pendingPayments = BookingPayment::with('booking')
            ->whereHas('booking', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->where('status', 'captured')
            ->where('vendor_payout_status', 'pending')
            ->get();

        $pendingAmount = $pendingPayments->sum('vendor_payout_amount');
        $pendingCount = $pendingPayments->count();

        // Get completed payouts
        $completedPayouts = PayoutRequest::where('vendor_id', $vendor->id)
            ->where('status', PayoutRequest::STATUS_COMPLETED)
            ->get();

        $totalPaid = $completedPayouts->sum('final_payout_amount');
        $totalPayoutsCount = $completedPayouts->count();

        // Get pending requests
        $pendingRequests = PayoutRequest::where('vendor_id', $vendor->id)
            ->whereIn('status', [
                PayoutRequest::STATUS_SUBMITTED,
                PayoutRequest::STATUS_PENDING_APPROVAL,
                PayoutRequest::STATUS_APPROVED,
                PayoutRequest::STATUS_PROCESSING,
            ])
            ->get();

        return [
            'pending_payments' => [
                'amount' => (float) $pendingAmount,
                'count' => $pendingCount,
                'payments' => $pendingPayments,
            ],
            'pending_requests' => [
                'count' => $pendingRequests->count(),
                'total_amount' => (float) $pendingRequests->sum('final_payout_amount'),
                'requests' => $pendingRequests,
            ],
            'completed_payouts' => [
                'total_amount' => (float) $totalPaid,
                'count' => $totalPayoutsCount,
            ],
            'lifetime_earnings' => [
                'gross_revenue' => (float) BookingPayment::whereHas('booking', fn($q) => $q->where('vendor_id', $vendor->id))
                    ->sum('gross_amount'),
                'commission_deducted' => (float) BookingPayment::whereHas('booking', fn($q) => $q->where('vendor_id', $vendor->id))
                    ->sum('admin_commission_amount'),
                'net_received' => (float) $totalPaid,
            ],
        ];
    }

    /**
     * Get admin payout dashboard statistics
     *
     * @return array
     */
    public function getAdminPayoutStatistics(): array
    {
        $pendingApproval = PayoutRequest::pendingApproval()->get();
        $approved = PayoutRequest::approved()->get();
        $completed = PayoutRequest::completed()->whereYear('completed_at', now()->year)->get();

        return [
            'pending_approval' => [
                'count' => $pendingApproval->count(),
                'total_amount' => (float) $pendingApproval->sum('final_payout_amount'),
            ],
            'approved_pending_settlement' => [
                'count' => $approved->count(),
                'total_amount' => (float) $approved->sum('final_payout_amount'),
            ],
            'completed_this_year' => [
                'count' => $completed->count(),
                'total_amount' => (float) $completed->sum('final_payout_amount'),
            ],
            'total_bookings_pending_payout' => BookingPayment::where('vendor_payout_status', 'pending')->count(),
            'total_pending_amount' => (float) BookingPayment::where('vendor_payout_status', 'pending')->sum('vendor_payout_amount'),
        ];
    }

    /**
     * Calculate payout breakdown preview
     *
     * @param User $vendor
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @param float $adjustmentAmount
     * @param float $gstPercentage
     * @return array
     */
    public function calculatePayoutPreview(
        User $vendor,
        Carbon $periodStart,
        Carbon $periodEnd,
        float $adjustmentAmount = 0,
        float $gstPercentage = 0
    ): array {
        $bookingPayments = BookingPayment::with('booking')
            ->whereHas('booking', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->where('status', 'captured')
            ->where('vendor_payout_status', 'pending')
            ->whereBetween('created_at', [
                $periodStart->startOfDay(),
                $periodEnd->endOfDay()
            ])
            ->get();

        if ($bookingPayments->isEmpty()) {
            return [
                'has_payments' => false,
                'message' => 'No pending payments found for the selected period',
            ];
        }

        $bookingRevenue = $bookingPayments->sum('gross_amount');
        $commissionAmount = $bookingPayments->sum('admin_commission_amount');
        $pgFees = $bookingPayments->sum('pg_fee_amount');
        
        $netBeforeGst = $bookingRevenue - $commissionAmount - $pgFees + $adjustmentAmount;
        $gstAmount = $gstPercentage > 0 ? ($netBeforeGst * ($gstPercentage / 100)) : 0;
        $finalPayoutAmount = $netBeforeGst - $gstAmount;

        return [
            'has_payments' => true,
            'bookings_count' => $bookingPayments->count(),
            'booking_revenue' => (float) $bookingRevenue,
            'commission_amount' => (float) $commissionAmount,
            'commission_percentage' => $bookingRevenue > 0 ? ($commissionAmount / $bookingRevenue) * 100 : 0,
            'pg_fees' => (float) $pgFees,
            'adjustment_amount' => (float) $adjustmentAmount,
            'net_before_gst' => (float) $netBeforeGst,
            'gst_amount' => (float) $gstAmount,
            'gst_percentage' => (float) $gstPercentage,
            'final_payout_amount' => (float) $finalPayoutAmount,
            'payments' => $bookingPayments,
        ];
    }
}
