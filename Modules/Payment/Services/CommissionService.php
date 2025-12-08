<?php

namespace Modules\Payment\Services;

use Modules\Bookings\Models\Booking;
use Modules\Payment\Models\BookingPayment;
use Modules\Payment\Models\CommissionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Services\SettingsService;

class CommissionService
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Calculate and record commission for a booking
     *
     * @param Booking $booking
     * @param string $razorpayPaymentId
     * @param string $razorpayOrderId
     * @return array [BookingPayment, CommissionLog]
     * @throws \Exception
     */
    public function calculateAndRecord(Booking $booking, string $razorpayPaymentId, string $razorpayOrderId): array
    {
        return DB::transaction(function () use ($booking, $razorpayPaymentId, $razorpayOrderId) {
            // Get commission rate from settings (default 15%)
            $commissionRate = (float) $this->settingsService->get('platform_commission_rate', 15.00);
            
            // Get PG fee percentage from settings (default 2% for Razorpay)
            $pgFeeRate = (float) $this->settingsService->get('payment_gateway_fee_rate', 2.00);

            // Gross amount (what customer paid)
            $grossAmount = (float) $booking->total_amount;

            // Calculate admin commission
            $adminCommission = round($grossAmount * ($commissionRate / 100), 2);

            // Calculate payment gateway fees (on gross amount)
            $pgFee = round($grossAmount * ($pgFeeRate / 100), 2);

            // Calculate vendor payout (gross - commission - PG fees)
            $vendorPayout = round($grossAmount - $adminCommission - $pgFee, 2);

            // Tax on commission (if applicable - default 0)
            $taxRate = (float) $this->settingsService->get('commission_tax_rate', 0.00);
            $tax = round($adminCommission * ($taxRate / 100), 2);

            // Adjust vendor payout if tax is deducted from vendor
            // For now, tax is absorbed by admin, not deducted from vendor
            // If you want to deduct from vendor: $vendorPayout -= $tax;

            // Create calculation snapshot for audit trail
            $calculationSnapshot = [
                'gross_amount' => $grossAmount,
                'commission_rate' => $commissionRate,
                'admin_commission' => $adminCommission,
                'pg_fee_rate' => $pgFeeRate,
                'pg_fee' => $pgFee,
                'tax_rate' => $taxRate,
                'tax' => $tax,
                'vendor_payout' => $vendorPayout,
                'calculated_at' => now()->toIso8601String(),
                'booking_id' => $booking->id,
                'razorpay_payment_id' => $razorpayPaymentId,
            ];

            // Create BookingPayment record
            $bookingPayment = BookingPayment::create([
                'booking_id' => $booking->id,
                'gross_amount' => $grossAmount,
                'admin_commission_amount' => $adminCommission,
                'vendor_payout_amount' => $vendorPayout,
                'pg_fee_amount' => $pgFee,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_order_id' => $razorpayOrderId,
                'vendor_payout_status' => 'pending',
                'status' => 'captured',
                'metadata' => [
                    'commission_rate_applied' => $commissionRate,
                    'pg_fee_rate_applied' => $pgFeeRate,
                ],
            ]);

            // Create CommissionLog record
            $commissionLog = CommissionLog::create([
                'booking_id' => $booking->id,
                'booking_payment_id' => $bookingPayment->id,
                'gross_amount' => $grossAmount,
                'admin_commission' => $adminCommission,
                'vendor_payout' => $vendorPayout,
                'pg_fee' => $pgFee,
                'tax' => $tax,
                'commission_rate' => $commissionRate,
                'commission_type' => 'percentage',
                'calculation_snapshot' => $calculationSnapshot,
            ]);

            // Log for monitoring
            Log::info('Commission calculated and recorded', [
                'booking_id' => $booking->id,
                'booking_payment_id' => $bookingPayment->id,
                'commission_log_id' => $commissionLog->id,
                'gross_amount' => $grossAmount,
                'admin_commission' => $adminCommission,
                'vendor_payout' => $vendorPayout,
            ]);

            return [$bookingPayment, $commissionLog];
        });
    }

    /**
     * Recalculate commission (for refunds, adjustments, etc.)
     *
     * @param BookingPayment $bookingPayment
     * @param float $refundAmount
     * @return array Updated amounts
     */
    public function recalculateForRefund(BookingPayment $bookingPayment, float $refundAmount): array
    {
        $refundPercentage = $refundAmount / $bookingPayment->gross_amount;

        $refundedCommission = round($bookingPayment->admin_commission_amount * $refundPercentage, 2);
        $refundedPgFee = round($bookingPayment->pg_fee_amount * $refundPercentage, 2);
        $refundedVendorPayout = round($bookingPayment->vendor_payout_amount * $refundPercentage, 2);

        return [
            'refund_amount' => $refundAmount,
            'refunded_commission' => $refundedCommission,
            'refunded_pg_fee' => $refundedPgFee,
            'refunded_vendor_payout' => $refundedVendorPayout,
            'remaining_commission' => $bookingPayment->admin_commission_amount - $refundedCommission,
            'remaining_vendor_payout' => $bookingPayment->vendor_payout_amount - $refundedVendorPayout,
        ];
    }

    /**
     * Get commission statistics for a date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getCommissionStats(string $startDate, string $endDate): array
    {
        $stats = CommissionLog::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(gross_amount) as total_gross,
                SUM(admin_commission) as total_commission,
                SUM(vendor_payout) as total_vendor_payout,
                SUM(pg_fee) as total_pg_fees,
                SUM(tax) as total_tax,
                AVG(commission_rate) as avg_commission_rate
            ')
            ->first();

        return [
            'total_transactions' => (int) $stats->total_transactions,
            'total_gross' => (float) ($stats->total_gross ?? 0),
            'total_commission' => (float) ($stats->total_commission ?? 0),
            'total_vendor_payout' => (float) ($stats->total_vendor_payout ?? 0),
            'total_pg_fees' => (float) ($stats->total_pg_fees ?? 0),
            'total_tax' => (float) ($stats->total_tax ?? 0),
            'avg_commission_rate' => (float) ($stats->avg_commission_rate ?? 0),
            'net_platform_revenue' => (float) (($stats->total_commission ?? 0) - ($stats->total_pg_fees ?? 0)),
        ];
    }

    /**
     * Get pending vendor payouts
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingPayouts()
    {
        return BookingPayment::with(['booking.vendor', 'booking.customer', 'commissionLog'])
            ->pendingPayout()
            ->captured()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get payout summary for a vendor
     *
     * @param int $vendorId
     * @return array
     */
    public function getVendorPayoutSummary(int $vendorId): array
    {
        $bookings = Booking::where('vendor_id', $vendorId)
            ->where('status', 'confirmed')
            ->with('bookingPayment')
            ->get();

        $pendingAmount = $bookings->sum(function ($booking) {
            return $booking->bookingPayment && $booking->bookingPayment->vendor_payout_status === 'pending'
                ? $booking->bookingPayment->vendor_payout_amount
                : 0;
        });

        $completedAmount = $bookings->sum(function ($booking) {
            return $booking->bookingPayment && $booking->bookingPayment->vendor_payout_status === 'completed'
                ? $booking->bookingPayment->vendor_payout_amount
                : 0;
        });

        return [
            'vendor_id' => $vendorId,
            'total_bookings' => $bookings->count(),
            'pending_payout_amount' => (float) $pendingAmount,
            'completed_payout_amount' => (float) $completedAmount,
            'total_payout_amount' => (float) ($pendingAmount + $completedAmount),
        ];
    }
}

