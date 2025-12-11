<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VendorPerformanceService
{
    /**
     * Get comprehensive vendor performance dashboard
     */
    public function getVendorDashboard(int $vendorId, string $period = 'month'): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'bookings' => $this->getBookingMetrics($vendorId, $dateRange),
            'enquiries' => $this->getEnquiryMetrics($vendorId, $dateRange),
            'response_time' => $this->getResponseTimeMetrics($vendorId, $dateRange),
            'sla' => $this->getSLAMetrics($vendorId, $dateRange),
            'ratings' => $this->getRatingMetrics($vendorId, $dateRange),
            'disputes' => $this->getDisputeMetrics($vendorId, $dateRange),
            'revenue' => $this->getRevenueMetrics($vendorId, $dateRange),
            'trends' => $this->getTrendData($vendorId, $period),
        ];
    }

    /**
     * Get booking metrics
     */
    protected function getBookingMetrics(int $vendorId, array $dateRange): array
    {
        $stats = DB::table('bookings')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status IN ("pending_payment_hold", "payment_hold") THEN 1 ELSE 0 END) as pending
            ')
            ->first();

        $disputed = DB::table('booking_disputes')
            ->where('vendor_id', $vendorId)
            ->whereBetween('disputed_at', $dateRange)
            ->count();

        $total = (int) $stats->total_bookings;
        $cancellationRate = $total > 0 ? (($stats->cancelled / $total) * 100) : 0;
        $disputeRate = $total > 0 ? (($disputed / $total) * 100) : 0;

        return [
            'total' => $total,
            'confirmed' => (int) $stats->confirmed,
            'cancelled' => (int) $stats->cancelled,
            'pending' => (int) $stats->pending,
            'disputed' => $disputed,
            'cancellation_rate' => round($cancellationRate, 2),
            'dispute_rate' => round($disputeRate, 2),
        ];
    }

    /**
     * Get enquiry and conversion metrics
     */
    protected function getEnquiryMetrics(int $vendorId, array $dateRange): array
    {
        // Get quotations sent to this vendor
        $enquiries = DB::table('quotations')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_enquiries,
                SUM(CASE WHEN quote_sent_at IS NOT NULL THEN 1 ELSE 0 END) as quotations_sent,
                SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as quotations_accepted
            ')
            ->first();

        // Get bookings created from quotations
        $conversions = DB::table('bookings')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->whereNotNull('quotation_id')
            ->count();

        $totalEnquiries = (int) $enquiries->total_enquiries;
        $quotationsSent = (int) $enquiries->quotations_sent;
        $quotationsAccepted = (int) $enquiries->quotations_accepted;

        $enquiryToBookingRatio = $totalEnquiries > 0 ? (($conversions / $totalEnquiries) * 100) : 0;
        $quoteAcceptanceRate = $quotationsSent > 0 ? (($quotationsAccepted / $quotationsSent) * 100) : 0;

        return [
            'total_enquiries' => $totalEnquiries,
            'quotations_sent' => $quotationsSent,
            'quotations_accepted' => $quotationsAccepted,
            'converted_to_bookings' => $conversions,
            'enquiry_to_booking_ratio' => round($enquiryToBookingRatio, 2),
            'quote_acceptance_rate' => round($quoteAcceptanceRate, 2),
            'pending_responses' => $totalEnquiries - $quotationsSent,
        ];
    }

    /**
     * Get quote response time metrics
     */
    protected function getResponseTimeMetrics(int $vendorId, array $dateRange): array
    {
        $responses = DB::table('quotations')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->whereNotNull('quote_sent_at')
            ->whereNotNull('response_time_minutes')
            ->selectRaw('
                AVG(response_time_minutes) as avg_response_time,
                MIN(response_time_minutes) as min_response_time,
                MAX(response_time_minutes) as max_response_time,
                COUNT(*) as total_responses
            ')
            ->first();

        // Get median (50th percentile)
        $medianSubquery = DB::table('quotations')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->whereNotNull('response_time_minutes')
            ->orderBy('response_time_minutes')
            ->limit(1)
            ->offset(DB::raw('(SELECT COUNT(*)/2 FROM quotations WHERE vendor_id = ' . $vendorId . ' AND response_time_minutes IS NOT NULL)'))
            ->value('response_time_minutes');

        // Count responses within time thresholds
        $within24h = DB::table('quotations')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->where('response_time_minutes', '<=', 1440)
            ->count();

        $within12h = DB::table('quotations')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->where('response_time_minutes', '<=', 720)
            ->count();

        $totalResponses = (int) $responses->total_responses ?? 0;
        $compliance24h = $totalResponses > 0 ? (($within24h / $totalResponses) * 100) : 0;

        return [
            'avg_response_time' => (int) ($responses->avg_response_time ?? 0),
            'median_response_time' => (int) ($medianSubquery ?? 0),
            'min_response_time' => (int) ($responses->min_response_time ?? 0),
            'max_response_time' => (int) ($responses->max_response_time ?? 0),
            'total_responses' => $totalResponses,
            'within_24h' => $within24h,
            'within_12h' => $within12h,
            'compliance_24h_percent' => round($compliance24h, 2),
            'avg_response_time_formatted' => $this->formatMinutes($responses->avg_response_time ?? 0),
        ];
    }

    /**
     * Get SLA metrics
     */
    protected function getSLAMetrics(int $vendorId, array $dateRange): array
    {
        // Get SLA tracking data
        $slaStats = DB::table('vendor_sla_tracking')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                AVG(current_reliability_score) as avg_sla_score,
                SUM(CASE WHEN violation_type IS NOT NULL THEN 1 ELSE 0 END) as total_violations,
                SUM(CASE WHEN status = "compliant" THEN 1 ELSE 0 END) as compliant_deliveries
            ')
            ->first();

        // Get latest reliability score
        $latestScore = DB::table('vendor_sla_tracking')
            ->where('vendor_id', $vendorId)
            ->latest('created_at')
            ->value('current_reliability_score') ?? 100;

        $totalDeliveries = ($slaStats->compliant_deliveries ?? 0) + ($slaStats->total_violations ?? 0);
        $complianceRate = $totalDeliveries > 0 
            ? (($slaStats->compliant_deliveries / $totalDeliveries) * 100) 
            : 100;

        return [
            'current_score' => round($latestScore, 2),
            'average_score' => round($slaStats->avg_sla_score ?? 100, 2),
            'total_violations' => (int) ($slaStats->total_violations ?? 0),
            'compliant_deliveries' => (int) ($slaStats->compliant_deliveries ?? 0),
            'compliance_rate' => round($complianceRate, 2),
            'score_status' => $this->getSLAScoreStatus($latestScore),
        ];
    }

    /**
     * Get rating metrics
     */
    protected function getRatingMetrics(int $vendorId, array $dateRange): array
    {
        $ratings = DB::table('bookings')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->whereNotNull('customer_rating')
            ->selectRaw('
                AVG(customer_rating) as avg_rating,
                COUNT(*) as total_ratings,
                SUM(CASE WHEN customer_rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN customer_rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN customer_rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN customer_rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN customer_rating = 1 THEN 1 ELSE 0 END) as one_star
            ')
            ->first();

        $totalRatings = (int) ($ratings->total_ratings ?? 0);
        $avgRating = $ratings->avg_rating ?? 0;

        return [
            'average_rating' => round($avgRating, 2),
            'total_ratings' => $totalRatings,
            'five_star' => (int) ($ratings->five_star ?? 0),
            'four_star' => (int) ($ratings->four_star ?? 0),
            'three_star' => (int) ($ratings->three_star ?? 0),
            'two_star' => (int) ($ratings->two_star ?? 0),
            'one_star' => (int) ($ratings->one_star ?? 0),
            'rating_distribution' => $this->getRatingDistribution($ratings),
            'rating_status' => $this->getRatingStatus($avgRating),
        ];
    }

    /**
     * Get dispute metrics
     */
    protected function getDisputeMetrics(int $vendorId, array $dateRange): array
    {
        $disputes = DB::table('booking_disputes')
            ->where('vendor_id', $vendorId)
            ->whereBetween('disputed_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_disputes,
                SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as open_disputes,
                SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved_disputes,
                SUM(CASE WHEN resolution = "vendor_favor" THEN 1 ELSE 0 END) as vendor_favor,
                SUM(CASE WHEN resolution = "customer_favor" THEN 1 ELSE 0 END) as customer_favor,
                SUM(disputed_amount) as total_disputed_amount,
                SUM(refund_amount) as total_refunded
            ')
            ->first();

        // Get disputes by type
        $disputesByType = DB::table('booking_disputes')
            ->where('vendor_id', $vendorId)
            ->whereBetween('disputed_at', $dateRange)
            ->select('dispute_type', DB::raw('COUNT(*) as count'))
            ->groupBy('dispute_type')
            ->orderByDesc('count')
            ->get();

        $totalDisputes = (int) ($disputes->total_disputes ?? 0);
        $resolved = (int) ($disputes->resolved_disputes ?? 0);
        $resolutionRate = $totalDisputes > 0 ? (($resolved / $totalDisputes) * 100) : 0;
        
        $vendorFavorRate = $resolved > 0 
            ? ((($disputes->vendor_favor ?? 0) / $resolved) * 100) 
            : 0;

        return [
            'total_disputes' => $totalDisputes,
            'open_disputes' => (int) ($disputes->open_disputes ?? 0),
            'resolved_disputes' => $resolved,
            'vendor_favor' => (int) ($disputes->vendor_favor ?? 0),
            'customer_favor' => (int) ($disputes->customer_favor ?? 0),
            'resolution_rate' => round($resolutionRate, 2),
            'vendor_favor_rate' => round($vendorFavorRate, 2),
            'total_disputed_amount' => (float) ($disputes->total_disputed_amount ?? 0),
            'total_refunded' => (float) ($disputes->total_refunded ?? 0),
            'disputes_by_type' => $disputesByType->toArray(),
        ];
    }

    /**
     * Get revenue metrics
     */
    protected function getRevenueMetrics(int $vendorId, array $dateRange): array
    {
        $revenue = DB::table('bookings')
            ->where('vendor_id', $vendorId)
            ->whereBetween('confirmed_at', $dateRange)
            ->where('status', 'confirmed')
            ->selectRaw('
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_booking_value,
                COUNT(*) as confirmed_bookings
            ')
            ->first();

        $commission = DB::table('commission_transactions')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', $dateRange)
            ->sum('commission_amount');

        return [
            'total_revenue' => (float) ($revenue->total_revenue ?? 0),
            'avg_booking_value' => (float) ($revenue->avg_booking_value ?? 0),
            'commission_paid' => (float) $commission,
            'net_revenue' => (float) (($revenue->total_revenue ?? 0) - $commission),
        ];
    }

    /**
     * Get trend data for charts
     */
    public function getTrendData(int $vendorId, string $period = 'month'): array
    {
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365,
            default => 30,
        };

        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        $dailyStats = DB::table('bookings')
            ->where('vendor_id', $vendorId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as bookings_count'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('AVG(customer_rating) as avg_rating')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $labels = [];
        $bookingsData = [];
        $revenueData = [];
        $ratingData = [];

        foreach ($dailyStats as $stat) {
            $labels[] = Carbon::parse($stat->date)->format('M d');
            $bookingsData[] = (int) $stat->bookings_count;
            $revenueData[] = (float) $stat->revenue;
            $ratingData[] = round($stat->avg_rating ?? 0, 2);
        }

        return [
            'labels' => $labels,
            'bookings' => $bookingsData,
            'revenue' => $revenueData,
            'ratings' => $ratingData,
        ];
    }

    /**
     * Update vendor performance metrics for a period
     */
    public function updatePerformanceMetrics(int $vendorId, Carbon $date = null, string $periodType = 'monthly'): void
    {
        $date = $date ?? Carbon::now();
        
        $dateRange = match($periodType) {
            'daily' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'weekly' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'monthly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'yearly' => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
            default => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
        };

        $bookings = $this->getBookingMetrics($vendorId, $dateRange);
        $enquiries = $this->getEnquiryMetrics($vendorId, $dateRange);
        $responseTime = $this->getResponseTimeMetrics($vendorId, $dateRange);
        $sla = $this->getSLAMetrics($vendorId, $dateRange);
        $ratings = $this->getRatingMetrics($vendorId, $dateRange);
        $revenue = $this->getRevenueMetrics($vendorId, $dateRange);

        // Calculate growth vs previous period
        $previousPeriodDate = $this->getPreviousPeriodDate($date, $periodType);
        $previousMetrics = DB::table('vendor_performance_metrics')
            ->where('vendor_id', $vendorId)
            ->where('period_date', $previousPeriodDate->toDateString())
            ->where('period_type', $periodType)
            ->first();

        $bookingGrowth = 0;
        $revenueGrowth = 0;
        $ratingTrend = 0;

        if ($previousMetrics) {
            if ($previousMetrics->total_bookings > 0) {
                $bookingGrowth = ((($bookings['total'] - $previousMetrics->total_bookings) / $previousMetrics->total_bookings) * 100);
            }
            if ($previousMetrics->total_revenue > 0) {
                $revenueGrowth = ((($revenue['total_revenue'] - $previousMetrics->total_revenue) / $previousMetrics->total_revenue) * 100);
            }
            $ratingTrend = $ratings['average_rating'] - $previousMetrics->average_rating;
        }

        DB::table('vendor_performance_metrics')->updateOrInsert(
            [
                'vendor_id' => $vendorId,
                'period_date' => $date->toDateString(),
                'period_type' => $periodType,
            ],
            [
                // Bookings
                'total_bookings' => $bookings['total'],
                'confirmed_bookings' => $bookings['confirmed'],
                'cancelled_bookings' => $bookings['cancelled'],
                'disputed_bookings' => $bookings['disputed'],
                'cancellation_rate' => $bookings['cancellation_rate'],
                'dispute_rate' => $bookings['dispute_rate'],
                
                // Enquiries
                'total_enquiries' => $enquiries['total_enquiries'],
                'quotations_sent' => $enquiries['quotations_sent'],
                'quotations_accepted' => $enquiries['quotations_accepted'],
                'enquiry_to_booking_ratio' => $enquiries['enquiry_to_booking_ratio'],
                'quote_acceptance_rate' => $enquiries['quote_acceptance_rate'],
                
                // Response time
                'avg_quote_response_time' => $responseTime['avg_response_time'],
                'median_quote_response_time' => $responseTime['median_response_time'],
                'min_quote_response_time' => $responseTime['min_response_time'],
                'max_quote_response_time' => $responseTime['max_response_time'],
                'quotes_within_24h' => $responseTime['within_24h'],
                'response_time_compliance' => $responseTime['compliance_24h_percent'],
                
                // SLA
                'overall_sla_score' => $sla['current_score'],
                'sla_violations' => $sla['total_violations'],
                'sla_compliant_deliveries' => $sla['compliant_deliveries'],
                'sla_compliance_rate' => $sla['compliance_rate'],
                
                // Ratings
                'average_rating' => $ratings['average_rating'],
                'total_ratings' => $ratings['total_ratings'],
                'five_star_ratings' => $ratings['five_star'],
                'four_star_ratings' => $ratings['four_star'],
                'three_star_ratings' => $ratings['three_star'],
                'two_star_ratings' => $ratings['two_star'],
                'one_star_ratings' => $ratings['one_star'],
                
                // Revenue
                'total_revenue' => $revenue['total_revenue'],
                'avg_booking_value' => $revenue['avg_booking_value'],
                'commission_paid' => $revenue['commission_paid'],
                
                // Trends
                'booking_growth_percent' => round($bookingGrowth, 2),
                'revenue_growth_percent' => round($revenueGrowth, 2),
                'rating_trend' => round($ratingTrend, 2),
                
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
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'quarter' => [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    /**
     * Get previous period date
     */
    protected function getPreviousPeriodDate(Carbon $date, string $periodType): Carbon
    {
        return match($periodType) {
            'daily' => $date->copy()->subDay(),
            'weekly' => $date->copy()->subWeek(),
            'monthly' => $date->copy()->subMonth(),
            'yearly' => $date->copy()->subYear(),
            default => $date->copy()->subMonth(),
        };
    }

    /**
     * Format minutes to human readable
     */
    protected function formatMinutes(float $minutes): string
    {
        if ($minutes < 60) {
            return round($minutes) . ' min';
        } elseif ($minutes < 1440) {
            return round($minutes / 60, 1) . ' hrs';
        } else {
            return round($minutes / 1440, 1) . ' days';
        }
    }

    /**
     * Get SLA score status
     */
    protected function getSLAScoreStatus(float $score): string
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'fair';
        return 'poor';
    }

    /**
     * Get rating status
     */
    protected function getRatingStatus(float $rating): string
    {
        if ($rating >= 4.5) return 'excellent';
        if ($rating >= 4.0) return 'good';
        if ($rating >= 3.5) return 'fair';
        if ($rating >= 3.0) return 'average';
        return 'poor';
    }

    /**
     * Get rating distribution as percentages
     */
    protected function getRatingDistribution($ratings): array
    {
        $total = (int) ($ratings->total_ratings ?? 0);
        
        if ($total === 0) {
            return [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        }

        return [
            5 => round((($ratings->five_star ?? 0) / $total) * 100, 1),
            4 => round((($ratings->four_star ?? 0) / $total) * 100, 1),
            3 => round((($ratings->three_star ?? 0) / $total) * 100, 1),
            2 => round((($ratings->two_star ?? 0) / $total) * 100, 1),
            1 => round((($ratings->one_star ?? 0) / $total) * 100, 1),
        ];
    }
}
