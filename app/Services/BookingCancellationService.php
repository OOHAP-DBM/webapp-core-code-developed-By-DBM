<?php

namespace App\Services;

use App\Models\BookingRefund;
use App\Models\CancellationPolicy;
use App\Models\Booking;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingCancellationService
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Process booking cancellation with refund
     */
    public function cancelBooking(
        $booking,
        int $cancelledBy,
        string $cancelledByRole,
        string $reason,
        ?int $policyId = null,
        bool $adminOverride = false
    ): BookingRefund {
        return DB::transaction(function () use ($booking, $cancelledBy, $cancelledByRole, $reason, $policyId, $adminOverride) {
            // Determine booking type
            $bookingType = $this->determineBookingType($booking);
            
            // Calculate hours before start
            $hoursBeforeStart = $this->calculateHoursBeforeStart($booking);
            
            // Find applicable policy
            $policy = $policyId 
                ? CancellationPolicy::findOrFail($policyId)
                : $this->findApplicablePolicy($bookingType, $cancelledByRole, $booking->total_amount, $hoursBeforeStart);

            if (!$policy && !$adminOverride) {
                throw new \Exception('No applicable cancellation policy found');
            }

            // Calculate refund
            $calculation = $policy 
                ? $policy->calculateRefund($booking->total_amount, $hoursBeforeStart, $cancelledByRole)
                : ['refund_amount' => $booking->total_amount, 'customer_fee' => 0, 'vendor_penalty' => 0];

            // Create refund record
            $refund = BookingRefund::create([
                'booking_id' => $booking->id,
                'booking_type' => get_class($booking),
                'cancellation_policy_id' => $policy->id ?? null,
                'refund_type' => $calculation['refund_amount'] >= $booking->total_amount ? 'full' : 'partial',
                'refund_method' => ($policy && $policy->allowsAutoRefund($bookingType)) ? 'auto' : 'manual',
                'booking_amount' => $booking->total_amount,
                'refundable_amount' => $calculation['refundable_amount'] ?? $booking->total_amount,
                'customer_fee' => $calculation['customer_fee'] ?? 0,
                'vendor_penalty' => $calculation['vendor_penalty'] ?? 0,
                'refund_amount' => $adminOverride ? $booking->total_amount : $calculation['refund_amount'],
                'pg_payment_id' => $booking->razorpay_payment_id ?? null,
                'cancelled_by_role' => $cancelledByRole,
                'cancelled_by' => $cancelledBy,
                'cancellation_reason' => $reason,
                'hours_before_start' => $hoursBeforeStart,
                'policy_snapshot' => $policy ? $policy->toArray() : null,
                'calculation_details' => $calculation,
                'admin_override' => $adminOverride,
                'status' => $adminOverride ? 'approved' : 'pending',
            ]);

            // Update booking status
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Process auto-refund if enabled
            if ($refund->refund_method === 'auto' && $refund->status === 'approved' && $bookingType !== 'pos') {
                $this->processAutoRefund($refund);
            }

            Log::info('Booking cancellation processed', [
                'booking_id' => $booking->id,
                'refund_id' => $refund->id,
                'refund_amount' => $refund->refund_amount,
            ]);

            return $refund;
        });
    }

    /**
     * Process automatic refund through payment gateway
     */
    public function processAutoRefund(BookingRefund $refund): void
    {
        if (!$refund->pg_payment_id) {
            throw new \Exception('No payment ID found for refund');
        }

        $refund->markAsProcessing();

        try {
            // Create refund via PaymentService
            $refundResult = $this->paymentService->createRefund(
                $refund->pg_payment_id,
                $refund->refund_amount,
                [
                    'reason' => 'Booking cancellation',
                    'notes' => "Refund for booking #{$refund->booking_id}",
                ]
            );

            if (!$refundResult['success']) {
                throw new \Exception($refundResult['error'] ?? 'Refund creation failed');
            }

            $refundData = $refundResult['refund_data'];
            $refund->markAsCompleted($refundData['id'] ?? 'manual');

            // Update original booking
            $refund->booking->update([
                'refund_id' => $refundData['id'] ?? null,
                'refund_amount' => $refund->refund_amount,
                'refunded_at' => now(),
                'payment_status' => 'refunded',
            ]);

            Log::info('Auto-refund processed successfully', [
                'refund_id' => $refund->id,
                'pg_refund_id' => $refundData['id'] ?? null,
                'transaction_id' => $refundResult['refund_transaction']->id ?? null,
            ]);

        } catch (\Exception $e) {
            $refund->markAsFailed($e->getMessage());
            Log::error('Auto-refund failed', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Find applicable cancellation policy
     */
    protected function findApplicablePolicy(
        string $bookingType,
        string $role,
        float $amount,
        int $hoursBeforeStart
    ): ?CancellationPolicy {
        return CancellationPolicy::active()
            ->forRole($role)
            ->forBookingType($bookingType)
            ->get()
            ->first(function ($policy) use ($amount, $hoursBeforeStart) {
                return $policy->appliesTo([
                    'amount' => $amount,
                    'hours_before_start' => $hoursBeforeStart,
                ]);
            });
    }

    /**
     * Calculate hours before booking start
     */
    protected function calculateHoursBeforeStart($booking): int
    {
        $startDate = $booking->start_date ?? $booking->campaign_start_date ?? null;
        
        if (!$startDate) {
            return 0;
        }

        $start = Carbon::parse($startDate);
        $now = Carbon::now();

        return max(0, $now->diffInHours($start, false));
    }

    /**
     * Determine booking type
     */
    protected function determineBookingType($booking): string
    {
        $class = get_class($booking);
        
        if (str_contains($class, 'POSBooking')) {
            return 'pos';
        } elseif (str_contains($class, 'DOOHBooking')) {
            return 'dooh';
        } else {
            return 'ooh';
        }
    }

    /**
     * Get refund statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_refunds' => BookingRefund::count(),
            'pending_refunds' => BookingRefund::pending()->count(),
            'approved_refunds' => BookingRefund::approved()->count(),
            'completed_refunds' => BookingRefund::completed()->count(),
            'failed_refunds' => BookingRefund::failed()->count(),
            'total_refund_amount' => BookingRefund::completed()->sum('refund_amount'),
            'total_customer_fees' => BookingRefund::sum('customer_fee'),
            'total_vendor_penalties' => BookingRefund::sum('vendor_penalty'),
            'auto_refunds' => BookingRefund::autoRefund()->count(),
            'manual_refunds' => BookingRefund::manualRefund()->count(),
        ];
    }
}
