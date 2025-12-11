<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RevenueService
{
    /**
     * Get comprehensive dashboard statistics
     */
    public function getDashboardStats(string $period = 'today'): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'bookings' => $this->getBookingStats($dateRange),
            'revenue' => $this->getRevenueStats($dateRange),
            'commissions' => $this->getCommissionStats($dateRange),
            'payouts' => $this->getPayoutStats($dateRange),
            'trends' => $this->getTrendData($period),
        ];
    }

    /**
     * Get booking statistics
     */
    protected function getBookingStats(array $dateRange): array
    {
        $stats = DB::table('bookings')
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_bookings,
                SUM(CASE WHEN status IN ("pending_payment_hold", "payment_hold") THEN 1 ELSE 0 END) as pending_bookings,
                SUM(total_amount) as total_booking_value,
                AVG(total_amount) as average_booking_value
            ')
            ->first();

        $posBookings = DB::table('pos_bookings')
            ->whereBetween('created_at', $dateRange)
            ->count();

        return [
            'total' => (int) $stats->total_bookings + $posBookings,
            'confirmed' => (int) $stats->confirmed_bookings,
            'cancelled' => (int) $stats->cancelled_bookings,
            'pending' => (int) $stats->pending_bookings,
            'pos_bookings' => $posBookings,
            'total_value' => (float) $stats->total_booking_value ?? 0,
            'average_value' => (float) $stats->average_booking_value ?? 0,
        ];
    }

    /**
     * Get revenue statistics
     */
    protected function getRevenueStats(array $dateRange): array
    {
        // Regular bookings revenue
        $bookingRevenue = DB::table('bookings')
            ->whereBetween('confirmed_at', $dateRange)
            ->where('status', 'confirmed')
            ->selectRaw('
                SUM(total_amount) as gross_revenue,
                COUNT(*) as booking_count
            ')
            ->first();

        // POS bookings revenue
        $posRevenue = DB::table('pos_bookings')
            ->whereBetween('created_at', $dateRange)
            ->where('payment_status', 'paid')
            ->sum('grand_total');

        // Invoice stats
        $invoiceStats = DB::table('invoices')
            ->whereBetween('invoice_date', $dateRange)
            ->selectRaw('
                COUNT(*) as total_invoices,
                SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid_invoices,
                SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN payment_status != "paid" THEN total_amount ELSE 0 END) as pending_amount,
                SUM(igst_amount + cgst_amount + sgst_amount) as total_tax_collected
            ')
            ->first();

        $grossRevenue = ($bookingRevenue->gross_revenue ?? 0) + $posRevenue;

        return [
            'gross_revenue' => (float) $grossRevenue,
            'paid_revenue' => (float) $invoiceStats->paid_amount ?? 0,
            'pending_revenue' => (float) $invoiceStats->pending_amount ?? 0,
            'tax_collected' => (float) $invoiceStats->total_tax_collected ?? 0,
            'net_revenue' => (float) $grossRevenue - ($invoiceStats->total_tax_collected ?? 0),
            'invoices_count' => (int) $invoiceStats->total_invoices ?? 0,
            'paid_invoices' => (int) $invoiceStats->paid_invoices ?? 0,
        ];
    }

    /**
     * Get commission statistics
     */
    protected function getCommissionStats(array $dateRange): array
    {
        $commissions = DB::table('commission_transactions')
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(commission_amount) as total_commission,
                SUM(CASE WHEN status = "settled" THEN commission_amount ELSE 0 END) as settled_commission,
                SUM(CASE WHEN status = "pending" THEN commission_amount ELSE 0 END) as pending_commission,
                SUM(gst_on_commission) as total_gst_on_commission,
                SUM(tds_deducted) as total_tds,
                AVG(commission_rate) as average_commission_rate
            ')
            ->first();

        return [
            'total_earned' => (float) $commissions->total_commission ?? 0,
            'settled' => (float) $commissions->settled_commission ?? 0,
            'pending' => (float) $commissions->pending_commission ?? 0,
            'gst_collected' => (float) $commissions->total_gst_on_commission ?? 0,
            'tds_deducted' => (float) $commissions->total_tds ?? 0,
            'average_rate' => (float) $commissions->average_commission_rate ?? 0,
            'transaction_count' => (int) $commissions->total_transactions ?? 0,
        ];
    }

    /**
     * Get payout statistics
     */
    protected function getPayoutStats(array $dateRange): array
    {
        $payouts = DB::table('payout_requests')
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = "pending" THEN payout_amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = "processing" THEN payout_amount ELSE 0 END) as processing_amount,
                SUM(CASE WHEN status = "completed" THEN payout_amount ELSE 0 END) as completed_amount,
                SUM(CASE WHEN status IN ("rejected", "failed") THEN payout_amount ELSE 0 END) as failed_amount,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->first();

        return [
            'pending_payouts' => (float) $payouts->pending_amount ?? 0,
            'processing_payouts' => (float) $payouts->processing_amount ?? 0,
            'completed_payouts' => (float) $payouts->completed_amount ?? 0,
            'failed_payouts' => (float) $payouts->failed_amount ?? 0,
            'pending_count' => (int) $payouts->pending_count ?? 0,
            'completed_count' => (int) $payouts->completed_count ?? 0,
            'total_requests' => (int) $payouts->total_requests ?? 0,
        ];
    }

    /**
     * Get trend data for charts
     */
    public function getTrendData(string $period = 'month'): array
    {
        $days = match($period) {
            'today' => 1,
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 30,
        };

        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        $dailyStats = DB::table('bookings')
            ->select(
                DB::raw('DATE(confirmed_at) as date'),
                DB::raw('COUNT(*) as bookings_count'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed_count')
            )
            ->where('confirmed_at', '>=', $startDate)
            ->where('status', 'confirmed')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $labels = [];
        $bookingsData = [];
        $revenueData = [];

        foreach ($dailyStats as $stat) {
            $labels[] = Carbon::parse($stat->date)->format('M d');
            $bookingsData[] = (int) $stat->bookings_count;
            $revenueData[] = (float) $stat->revenue;
        }

        return [
            'labels' => $labels,
            'bookings' => $bookingsData,
            'revenue' => $revenueData,
        ];
    }

    /**
     * Get top performing vendors
     */
    public function getTopVendors(int $limit = 10, string $period = 'month'): array
    {
        $dateRange = $this->getDateRange($period);

        return DB::table('bookings')
            ->join('users', 'bookings.vendor_id', '=', 'users.id')
            ->whereBetween('bookings.confirmed_at', $dateRange)
            ->where('bookings.status', 'confirmed')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(bookings.id) as total_bookings'),
                DB::raw('SUM(bookings.total_amount) as total_revenue'),
                DB::raw('AVG(bookings.total_amount) as average_booking_value')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'email' => $vendor->email,
                    'bookings_count' => (int) $vendor->total_bookings,
                    'total_revenue' => (float) $vendor->total_revenue,
                    'average_value' => (float) $vendor->average_booking_value,
                ];
            })
            ->toArray();
    }

    /**
     * Get top performing locations
     */
    public function getTopLocations(int $limit = 10, string $period = 'month'): array
    {
        $dateRange = $this->getDateRange($period);

        return DB::table('bookings')
            ->join('hoardings', 'bookings.hoarding_id', '=', 'hoardings.id')
            ->whereBetween('bookings.confirmed_at', $dateRange)
            ->where('bookings.status', 'confirmed')
            ->select(
                'hoardings.city',
                'hoardings.state',
                DB::raw('COUNT(bookings.id) as total_bookings'),
                DB::raw('SUM(bookings.total_amount) as total_revenue'),
                DB::raw('COUNT(DISTINCT bookings.vendor_id) as unique_vendors'),
                DB::raw('COUNT(DISTINCT bookings.customer_id) as unique_customers')
            )
            ->groupBy('hoardings.city', 'hoardings.state')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(function ($location) {
                return [
                    'city' => $location->city,
                    'state' => $location->state,
                    'location' => $location->city . ', ' . $location->state,
                    'bookings_count' => (int) $location->total_bookings,
                    'total_revenue' => (float) $location->total_revenue,
                    'unique_vendors' => (int) $location->unique_vendors,
                    'unique_customers' => (int) $location->unique_customers,
                ];
            })
            ->toArray();
    }

    /**
     * Get revenue by payment method
     */
    public function getRevenueByPaymentMethod(string $period = 'month'): array
    {
        $dateRange = $this->getDateRange($period);

        $paymentMethods = DB::table('booking_payments')
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'success')
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('payment_method')
            ->get();

        return $paymentMethods->map(function ($method) {
            return [
                'method' => ucfirst($method->payment_method),
                'count' => (int) $method->transaction_count,
                'amount' => (float) $method->total_amount,
            ];
        })->toArray();
    }

    /**
     * Get vendor revenue breakdown
     */
    public function getVendorRevenueBreakdown(int $vendorId, string $period = 'month'): array
    {
        $dateRange = $this->getDateRange($period);

        $bookings = DB::table('bookings')
            ->where('vendor_id', $vendorId)
            ->whereBetween('confirmed_at', $dateRange)
            ->where('status', 'confirmed')
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(total_amount) as gross_revenue
            ')
            ->first();

        $commissions = DB::table('commission_transactions')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                SUM(commission_amount) as total_commission,
                SUM(vendor_amount) as net_earnings,
                SUM(tds_deducted) as total_tds
            ')
            ->first();

        $payouts = DB::table('payout_requests')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                SUM(CASE WHEN status = "pending" THEN payout_amount ELSE 0 END) as pending_payout,
                SUM(CASE WHEN status = "completed" THEN payout_amount ELSE 0 END) as completed_payout
            ')
            ->first();

        return [
            'bookings_count' => (int) $bookings->total_bookings ?? 0,
            'gross_revenue' => (float) $bookings->gross_revenue ?? 0,
            'commission_deducted' => (float) $commissions->total_commission ?? 0,
            'net_earnings' => (float) $commissions->net_earnings ?? 0,
            'tds_deducted' => (float) $commissions->total_tds ?? 0,
            'pending_payout' => (float) $payouts->pending_payout ?? 0,
            'completed_payout' => (float) $payouts->completed_payout ?? 0,
        ];
    }

    /**
     * Generate daily revenue snapshot
     */
    public function generateDailySnapshot(Carbon $date = null): void
    {
        $date = $date ?? Carbon::today();
        $dateRange = [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

        $bookingStats = $this->getBookingStats($dateRange);
        $revenueStats = $this->getRevenueStats($dateRange);
        $commissionStats = $this->getCommissionStats($dateRange);
        $payoutStats = $this->getPayoutStats($dateRange);

        // Calculate growth compared to previous day
        $previousSnapshot = DB::table('daily_revenue_snapshots')
            ->where('snapshot_date', $date->copy()->subDay()->toDateString())
            ->first();

        $revenueGrowth = 0;
        $bookingGrowth = 0;

        if ($previousSnapshot) {
            if ($previousSnapshot->gross_revenue > 0) {
                $revenueGrowth = (($revenueStats['gross_revenue'] - $previousSnapshot->gross_revenue) / $previousSnapshot->gross_revenue) * 100;
            }
            if ($previousSnapshot->total_bookings > 0) {
                $bookingGrowth = (($bookingStats['total'] - $previousSnapshot->total_bookings) / $previousSnapshot->total_bookings) * 100;
            }
        }

        DB::table('daily_revenue_snapshots')->updateOrInsert(
            ['snapshot_date' => $date->toDateString()],
            [
                'total_bookings' => $bookingStats['total'],
                'confirmed_bookings' => $bookingStats['confirmed'],
                'cancelled_bookings' => $bookingStats['cancelled'],
                'pos_bookings' => $bookingStats['pos_bookings'],
                
                'gross_revenue' => $revenueStats['gross_revenue'],
                'vendor_revenue' => $revenueStats['gross_revenue'] - $commissionStats['total_earned'],
                'commission_earned' => $commissionStats['total_earned'],
                'tax_collected' => $revenueStats['tax_collected'],
                
                'paid_amount' => $revenueStats['paid_revenue'],
                'pending_amount' => $revenueStats['pending_revenue'],
                'refunded_amount' => 0, // Calculate from refunds if needed
                
                'pending_payouts' => $payoutStats['pending_payouts'],
                'completed_payouts' => $payoutStats['completed_payouts'],
                
                'invoices_generated' => $revenueStats['invoices_count'],
                'paid_invoices' => $revenueStats['paid_invoices'],
                'pending_invoices' => $revenueStats['invoices_count'] - $revenueStats['paid_invoices'],
                
                'revenue_growth_percent' => round($revenueGrowth, 2),
                'booking_growth_percent' => round($bookingGrowth, 2),
                
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Update vendor revenue stats
     */
    public function updateVendorStats(int $vendorId, Carbon $date = null): void
    {
        $date = $date ?? Carbon::today();
        $dateRange = [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

        $breakdown = $this->getVendorRevenueBreakdown($vendorId, 'today');

        $payoutRequests = DB::table('payout_requests')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->first();

        $activeHoardings = DB::table('hoardings')
            ->where('vendor_id', $vendorId)
            ->where('status', 'approved')
            ->count();

        DB::table('vendor_revenue_stats')->updateOrInsert(
            [
                'vendor_id' => $vendorId,
                'period_date' => $date->toDateString(),
                'period_type' => 'daily',
            ],
            [
                'total_bookings' => $breakdown['bookings_count'],
                'gross_revenue' => $breakdown['gross_revenue'],
                'commission_deducted' => $breakdown['commission_deducted'],
                'net_revenue' => $breakdown['net_earnings'],
                'pending_payout' => $breakdown['pending_payout'],
                'paid_payout' => $breakdown['completed_payout'],
                'pending_payout_requests' => (int) $payoutRequests->pending_count ?? 0,
                'completed_payout_requests' => (int) $payoutRequests->completed_count ?? 0,
                'average_booking_value' => $breakdown['bookings_count'] > 0 
                    ? $breakdown['gross_revenue'] / $breakdown['bookings_count'] 
                    : 0,
                'active_hoardings' => $activeHoardings,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Get date range for period
     */
    protected function getDateRange(string $period): array
    {
        return match($period) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'quarter' => [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'last_7_days' => [Carbon::now()->subDays(7), Carbon::now()],
            'last_30_days' => [Carbon::now()->subDays(30), Carbon::now()],
            'last_90_days' => [Carbon::now()->subDays(90), Carbon::now()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }

    /**
     * Get commission rate for booking (can be customized per vendor/category)
     */
    public function calculateCommission(float $bookingAmount, int $vendorId = null): array
    {
        // Default commission rate - can be customized per vendor
        $commissionRate = 15.0; // 15%

        // Check if vendor has custom commission rate
        if ($vendorId) {
            $vendorSettings = DB::table('vendor_settings')
                ->where('vendor_id', $vendorId)
                ->value('commission_rate');
            
            if ($vendorSettings) {
                $commissionRate = (float) $vendorSettings;
            }
        }

        $commissionAmount = ($bookingAmount * $commissionRate) / 100;
        $vendorAmount = $bookingAmount - $commissionAmount;

        // Calculate GST on commission (18%)
        $gstOnCommission = ($commissionAmount * 18) / 100;

        return [
            'booking_amount' => $bookingAmount,
            'commission_rate' => $commissionRate,
            'commission_amount' => round($commissionAmount, 2),
            'vendor_amount' => round($vendorAmount, 2),
            'gst_on_commission' => round($gstOnCommission, 2),
        ];
    }

    /**
     * Record commission transaction
     */
    public function recordCommission(int $bookingId, int $vendorId, float $bookingAmount): void
    {
        $commission = $this->calculateCommission($bookingAmount, $vendorId);

        DB::table('commission_transactions')->insert([
            'booking_id' => $bookingId,
            'vendor_id' => $vendorId,
            'booking_amount' => $commission['booking_amount'],
            'commission_rate' => $commission['commission_rate'],
            'commission_amount' => $commission['commission_amount'],
            'vendor_amount' => $commission['vendor_amount'],
            'gst_on_commission' => $commission['gst_on_commission'],
            'status' => 'calculated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
