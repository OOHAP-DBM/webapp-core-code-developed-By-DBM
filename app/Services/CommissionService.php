<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\CommissionLog;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Services\SettingsService;

class CommissionService
{
    protected SettingsService $settingsService;
    protected CommissionRuleService $ruleService;
    protected TaxService $taxService;

    public function __construct(
        SettingsService $settingsService, 
        CommissionRuleService $ruleService,
        TaxService $taxService
    ) {
        $this->settingsService = $settingsService;
        $this->ruleService = $ruleService;
        $this->taxService = $taxService;
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
            // Get commission using rule engine
            $grossAmount = (float) $booking->total_amount;
            $ruleResult = $this->ruleService->calculateCommission($booking, $grossAmount);
            
            $adminCommission = $ruleResult['commission_amount'];
            $commissionRate = $ruleResult['commission_rate'];
            $ruleId = $ruleResult['rule_id'];
            $ruleName = $ruleResult['rule_name'];
            
            // Get PG fee percentage from settings (default 2% for Razorpay)
            $pgFeeRate = (float) $this->settingsService->get('payment_gateway_fee_rate', 2.00);

            // Calculate payment gateway fees (on gross amount)
            $pgFee = round($grossAmount * ($pgFeeRate / 100), 2);

            // Calculate vendor payout (gross - commission - PG fees)
            $vendorPayout = round($grossAmount - $adminCommission - $pgFee, 2);

            // Tax on commission using dynamic TaxService
            $taxResult = $this->taxService->applyTax(
                $booking,
                $adminCommission,
                'commission',
                [
                    'calculated_by' => 'CommissionService',
                    'booking_id' => $booking->id,
                ]
            );
            $tax = $taxResult['tax_amount'];
            $taxRate = $taxResult['calculations'][0]->tax_rate ?? 0.00;

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
                    'commission_rule_id' => $ruleId,
                    'commission_rule_name' => $ruleName,
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
                'commission_type' => $ruleResult['commission_type'] ?? 'percentage',
                'calculation_snapshot' => array_merge($calculationSnapshot, [
                    'rule_id' => $ruleId,
                    'rule_name' => $ruleName,
                    'rule_type' => $ruleResult['rule_type'] ?? null,
                ]),
            ]);

            // Log for monitoring
            Log::info('Commission calculated and recorded', [
                'booking_id' => $booking->id,
                'booking_payment_id' => $bookingPayment->id,
                'commission_log_id' => $commissionLog->id,
                'gross_amount' => $grossAmount,
                'admin_commission' => $adminCommission,
                'vendor_payout' => $vendorPayout,
                'rule_id' => $ruleId,
                'rule_name' => $ruleName,
            ]);

            // PROMPT 64: Auto-generate GST-compliant invoice
            try {
                $invoiceService = app(\App\Services\InvoiceService::class);
                $invoice = $invoiceService->generateInvoiceForBooking(
                    $booking,
                    $bookingPayment,
                    \App\Models\Invoice::TYPE_FULL_PAYMENT
                );
                
                Log::info('Invoice generated automatically', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'booking_id' => $booking->id,
                ]);

                // Auto-send email if enabled
                if (Setting::getValue('invoice_auto_send_email', true)) {
                    $invoiceService->sendInvoiceEmail($invoice);
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate invoice after payment', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the transaction if invoice generation fails
            }

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
