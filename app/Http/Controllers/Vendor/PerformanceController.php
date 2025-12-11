<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\VendorPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    protected VendorPerformanceService $performanceService;

    public function __construct(VendorPerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Display vendor performance dashboard
     */
    public function dashboard(Request $request)
    {
        $vendor = auth()->user();
        $period = $request->input('period', 'month');
        
        // Get comprehensive performance data
        $performance = $this->performanceService->getVendorDashboard($vendor->id, $period);
        
        // Get recent disputes
        $recentDisputes = $this->getRecentDisputes($vendor->id, 5);
        
        // Get recent ratings with feedback
        $recentRatings = $this->getRecentRatings($vendor->id, 10);
        
        // Get pending responses
        $pendingEnquiries = $this->getPendingEnquiries($vendor->id);
        
        // Get performance insights/recommendations
        $insights = $this->generateInsights($performance);
        
        return view('vendor.performance.dashboard', compact(
            'performance',
            'recentDisputes',
            'recentRatings',
            'pendingEnquiries',
            'insights',
            'period'
        ));
    }

    /**
     * Get detailed booking metrics
     */
    public function bookings(Request $request)
    {
        $vendor = auth()->user();
        $period = $request->input('period', 'month');
        
        $dateRange = $this->getDateRange($period);
        
        $bookings = DB::table('bookings')
            ->join('users', 'bookings.customer_id', '=', 'users.id')
            ->join('hoardings', 'bookings.hoarding_id', '=', 'hoardings.id')
            ->where('bookings.vendor_id', $vendor->id)
            ->whereBetween('bookings.created_at', $dateRange)
            ->select(
                'bookings.*',
                'users.name as customer_name',
                'hoardings.location_name',
                'hoardings.city'
            )
            ->orderBy('bookings.created_at', 'desc')
            ->paginate(20);
        
        $stats = DB::table('bookings')
            ->where('vendor_id', $vendor->id)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                SUM(total_amount) as total_value,
                AVG(total_amount) as avg_value
            ')
            ->first();
        
        return view('vendor.performance.bookings', compact('bookings', 'stats', 'period'));
    }

    /**
     * Get enquiry and response details
     */
    public function enquiries(Request $request)
    {
        $vendor = auth()->user();
        $status = $request->input('status', 'all');
        
        $query = DB::table('quotations')
            ->join('users', 'quotations.customer_id', '=', 'users.id')
            ->where('quotations.vendor_id', $vendor->id)
            ->select(
                'quotations.*',
                'users.name as customer_name',
                'users.email as customer_email'
            );
        
        if ($status !== 'all') {
            if ($status === 'pending') {
                $query->whereNull('quote_sent_at');
            } elseif ($status === 'responded') {
                $query->whereNotNull('quote_sent_at');
            } else {
                $query->where('quotations.status', $status);
            }
        }
        
        $enquiries = $query->orderBy('quotations.created_at', 'desc')
            ->paginate(20);
        
        // Calculate stats
        $stats = DB::table('quotations')
            ->where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN quote_sent_at IS NULL THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted,
                AVG(response_time_minutes) as avg_response_time
            ')
            ->first();
        
        return view('vendor.performance.enquiries', compact('enquiries', 'stats', 'status'));
    }

    /**
     * Get SLA performance details
     */
    public function sla(Request $request)
    {
        $vendor = auth()->user();
        $period = $request->input('period', 'month');
        
        $dateRange = $this->getDateRange($period);
        
        // Get SLA tracking records
        $slaRecords = DB::table('vendor_sla_tracking')
            ->join('bookings', 'vendor_sla_tracking.booking_id', '=', 'bookings.id')
            ->where('vendor_sla_tracking.vendor_id', $vendor->id)
            ->whereBetween('vendor_sla_tracking.created_at', $dateRange)
            ->select(
                'vendor_sla_tracking.*',
                'bookings.start_date',
                'bookings.end_date'
            )
            ->orderBy('vendor_sla_tracking.created_at', 'desc')
            ->paginate(20);
        
        // Get current reliability score
        $currentScore = DB::table('vendor_sla_tracking')
            ->where('vendor_id', $vendor->id)
            ->latest('created_at')
            ->value('current_reliability_score') ?? 100;
        
        // Get violations breakdown
        $violationsByType = DB::table('vendor_sla_tracking')
            ->where('vendor_id', $vendor->id)
            ->whereBetween('created_at', $dateRange)
            ->whereNotNull('violation_type')
            ->select('violation_type', DB::raw('COUNT(*) as count'))
            ->groupBy('violation_type')
            ->get();
        
        return view('vendor.performance.sla', compact(
            'slaRecords',
            'currentScore',
            'violationsByType',
            'period'
        ));
    }

    /**
     * Get ratings and reviews
     */
    public function ratings(Request $request)
    {
        $vendor = auth()->user();
        $period = $request->input('period', 'month');
        
        $dateRange = $this->getDateRange($period);
        
        $ratings = DB::table('bookings')
            ->join('users', 'bookings.customer_id', '=', 'users.id')
            ->where('bookings.vendor_id', $vendor->id)
            ->whereBetween('bookings.created_at', $dateRange)
            ->whereNotNull('bookings.customer_rating')
            ->select(
                'bookings.id',
                'bookings.customer_rating',
                'bookings.customer_feedback',
                'bookings.rated_at',
                'users.name as customer_name',
                'bookings.start_date',
                'bookings.end_date'
            )
            ->orderBy('bookings.rated_at', 'desc')
            ->paginate(20);
        
        // Get rating distribution
        $distribution = DB::table('bookings')
            ->where('vendor_id', $vendor->id)
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
        
        return view('vendor.performance.ratings', compact('ratings', 'distribution', 'period'));
    }

    /**
     * Get dispute details
     */
    public function disputes(Request $request)
    {
        $vendor = auth()->user();
        $status = $request->input('status', 'all');
        
        $query = DB::table('booking_disputes')
            ->join('bookings', 'booking_disputes.booking_id', '=', 'bookings.id')
            ->join('users', 'booking_disputes.customer_id', '=', 'users.id')
            ->where('booking_disputes.vendor_id', $vendor->id)
            ->select(
                'booking_disputes.*',
                'bookings.start_date',
                'bookings.end_date',
                'users.name as customer_name'
            );
        
        if ($status !== 'all') {
            $query->where('booking_disputes.status', $status);
        }
        
        $disputes = $query->orderBy('booking_disputes.disputed_at', 'desc')
            ->paginate(20);
        
        // Get summary stats
        $stats = DB::table('booking_disputes')
            ->where('vendor_id', $vendor->id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN resolution = "vendor_favor" THEN 1 ELSE 0 END) as vendor_favor,
                SUM(disputed_amount) as total_disputed
            ')
            ->first();
        
        return view('vendor.performance.disputes', compact('disputes', 'stats', 'status'));
    }

    /**
     * Show dispute details
     */
    public function showDispute($id)
    {
        $vendor = auth()->user();
        
        $dispute = DB::table('booking_disputes')
            ->join('bookings', 'booking_disputes.booking_id', '=', 'bookings.id')
            ->join('users as customers', 'booking_disputes.customer_id', '=', 'customers.id')
            ->leftJoin('users as resolvers', 'booking_disputes.resolved_by', '=', 'resolvers.id')
            ->where('booking_disputes.vendor_id', $vendor->id)
            ->where('booking_disputes.id', $id)
            ->select(
                'booking_disputes.*',
                'bookings.start_date',
                'bookings.end_date',
                'bookings.total_amount',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'resolvers.name as resolver_name'
            )
            ->firstOrFail();
        
        return view('vendor.performance.dispute-details', compact('dispute'));
    }

    /**
     * Respond to dispute
     */
    public function respondToDispute(Request $request, $id)
    {
        $request->validate([
            'response' => 'required|string|min:20',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|max:5120', // 5MB max
        ]);
        
        $vendor = auth()->user();
        
        $dispute = DB::table('booking_disputes')
            ->where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->where('status', 'open')
            ->first();
        
        if (!$dispute) {
            return redirect()->back()->with('error', 'Dispute not found or already processed.');
        }
        
        // Upload evidence files if provided
        $evidenceUrls = [];
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('dispute-evidence', 'public');
                $evidenceUrls[] = $path;
            }
        }
        
        DB::table('booking_disputes')
            ->where('id', $id)
            ->update([
                'vendor_response' => $request->response,
                'vendor_evidence' => json_encode($evidenceUrls),
                'status' => 'under_review',
                'updated_at' => now(),
            ]);
        
        return redirect()->route('vendor.performance.disputes')
            ->with('success', 'Your response has been submitted for review.');
    }

    /**
     * Get recent disputes
     */
    protected function getRecentDisputes(int $vendorId, int $limit = 5): array
    {
        return DB::table('booking_disputes')
            ->join('users', 'booking_disputes.customer_id', '=', 'users.id')
            ->where('booking_disputes.vendor_id', $vendorId)
            ->where('booking_disputes.status', '!=', 'closed')
            ->select(
                'booking_disputes.id',
                'booking_disputes.dispute_type',
                'booking_disputes.status',
                'booking_disputes.disputed_at',
                'users.name as customer_name'
            )
            ->orderBy('booking_disputes.disputed_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get recent ratings
     */
    protected function getRecentRatings(int $vendorId, int $limit = 10): array
    {
        return DB::table('bookings')
            ->join('users', 'bookings.customer_id', '=', 'users.id')
            ->where('bookings.vendor_id', $vendorId)
            ->whereNotNull('bookings.customer_rating')
            ->select(
                'bookings.customer_rating',
                'bookings.customer_feedback',
                'bookings.rated_at',
                'users.name as customer_name'
            )
            ->orderBy('bookings.rated_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get pending enquiries
     */
    protected function getPendingEnquiries(int $vendorId): array
    {
        return DB::table('quotations')
            ->join('users', 'quotations.customer_id', '=', 'users.id')
            ->where('quotations.vendor_id', $vendorId)
            ->whereNull('quotations.quote_sent_at')
            ->select(
                'quotations.id',
                'quotations.created_at',
                'users.name as customer_name',
                DB::raw('TIMESTAMPDIFF(HOUR, quotations.created_at, NOW()) as hours_waiting')
            )
            ->orderBy('quotations.created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Generate performance insights
     */
    protected function generateInsights(array $performance): array
    {
        $insights = [];
        
        // Response time insights
        if ($performance['response_time']['avg_response_time'] > 1440) { // > 24 hours
            $insights[] = [
                'type' => 'warning',
                'icon' => 'clock',
                'message' => 'Your average response time is over 24 hours. Try to respond to enquiries faster to improve conversion.',
            ];
        } elseif ($performance['response_time']['compliance_24h_percent'] >= 90) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'check-circle',
                'message' => 'Excellent! You\'re responding to ' . $performance['response_time']['compliance_24h_percent'] . '% of enquiries within 24 hours.',
            ];
        }
        
        // SLA insights
        if ($performance['sla']['current_score'] < 75) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'exclamation-triangle',
                'message' => 'Your SLA score is low (' . $performance['sla']['current_score'] . '). Focus on meeting deadlines to improve.',
            ];
        } elseif ($performance['sla']['current_score'] >= 90) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'star',
                'message' => 'Great SLA performance! Your reliability score is ' . $performance['sla']['current_score'] . '.',
            ];
        }
        
        // Rating insights
        if ($performance['ratings']['average_rating'] < 3.5) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'star-half',
                'message' => 'Your average rating is below 3.5. Consider improving service quality.',
            ];
        } elseif ($performance['ratings']['average_rating'] >= 4.5) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'star-fill',
                'message' => 'Outstanding! Your average rating is ' . $performance['ratings']['average_rating'] . '/5.0.',
            ];
        }
        
        // Conversion insights
        if ($performance['enquiries']['enquiry_to_booking_ratio'] < 20) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'graph-down',
                'message' => 'Your conversion rate is ' . $performance['enquiries']['enquiry_to_booking_ratio'] . '%. Consider competitive pricing.',
            ];
        } elseif ($performance['enquiries']['enquiry_to_booking_ratio'] >= 40) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'graph-up',
                'message' => 'Excellent conversion rate of ' . $performance['enquiries']['enquiry_to_booking_ratio'] . '%!',
            ];
        }
        
        // Dispute insights
        if ($performance['disputes']['total_disputes'] > 0 && $performance['disputes']['vendor_favor_rate'] < 50) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'shield-exclamation',
                'message' => 'You have ' . $performance['disputes']['open_disputes'] . ' open disputes. Respond promptly to improve resolution.',
            ];
        }
        
        return $insights;
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
}
