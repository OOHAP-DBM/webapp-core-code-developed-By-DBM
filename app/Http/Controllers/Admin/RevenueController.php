<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueController extends Controller
{
    protected RevenueService $revenueService;

    public function __construct(RevenueService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    /**
     * Display revenue dashboard
     */
    public function dashboard(Request $request)
    {
        $period = $request->input('period', 'today');
        
        // Get comprehensive statistics
        $stats = $this->revenueService->getDashboardStats($period);
        
        // Get top performers
        $topVendors = $this->revenueService->getTopVendors(10, $period);
        $topLocations = $this->revenueService->getTopLocations(10, $period);
        
        // Get trend data for charts
        $trends = $this->revenueService->getTrendData($period === 'today' ? 'week' : $period);
        
        // Get payment method breakdown
        $paymentMethods = $this->revenueService->getRevenueByPaymentMethod($period);
        
        // Get recent high-value bookings
        $highValueBookings = $this->getRecentHighValueBookings(10);
        
        // Get commission distribution
        $commissionDistribution = $this->getCommissionDistribution($period);
        
        // Get daily snapshots for comparison
        $dailySnapshots = $this->getDailySnapshots(7);
        
        return view('admin.revenue.dashboard', compact(
            'stats',
            'topVendors',
            'topLocations',
            'trends',
            'paymentMethods',
            'highValueBookings',
            'commissionDistribution',
            'dailySnapshots',
            'period'
        ));
    }

    /**
     * Get vendor revenue details
     */
    public function vendorRevenue(Request $request)
    {
        $period = $request->input('period', 'month');
        $search = $request->input('search');
        
        $query = DB::table('bookings')
            ->join('users', 'bookings.vendor_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                DB::raw('COUNT(bookings.id) as total_bookings'),
                DB::raw('SUM(bookings.total_amount) as total_revenue'),
                DB::raw('SUM(CASE WHEN bookings.status = "confirmed" THEN 1 ELSE 0 END) as confirmed_bookings'),
                DB::raw('SUM(CASE WHEN bookings.status = "cancelled" THEN 1 ELSE 0 END) as cancelled_bookings')
            );
        
        $dateRange = $this->revenueService->getDateRange($period);
        $query->whereBetween('bookings.created_at', $dateRange);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }
        
        $vendors = $query->groupBy('users.id', 'users.name', 'users.email', 'users.phone')
            ->orderByDesc('total_revenue')
            ->paginate(20);
        
        // Enhance with commission data
        foreach ($vendors as $vendor) {
            $commissionData = DB::table('commission_transactions')
                ->where('vendor_id', $vendor->id)
                ->whereBetween('created_at', $dateRange)
                ->selectRaw('
                    SUM(commission_amount) as total_commission,
                    SUM(vendor_amount) as net_earnings
                ')
                ->first();
            
            $vendor->commission_deducted = $commissionData->total_commission ?? 0;
            $vendor->net_earnings = $commissionData->net_earnings ?? 0;
            
            // Get pending payouts
            $vendor->pending_payout = DB::table('payout_requests')
                ->where('vendor_id', $vendor->id)
                ->where('status', 'pending')
                ->sum('payout_amount');
        }
        
        return view('admin.revenue.vendor-revenue', compact('vendors', 'period', 'search'));
    }

    /**
     * Get location-wise revenue
     */
    public function locationRevenue(Request $request)
    {
        $period = $request->input('period', 'month');
        $dateRange = $this->revenueService->getDateRange($period);
        
        $locations = DB::table('bookings')
            ->join('hoardings', 'bookings.hoarding_id', '=', 'hoardings.id')
            ->whereBetween('bookings.confirmed_at', $dateRange)
            ->where('bookings.status', 'confirmed')
            ->select(
                'hoardings.city',
                'hoardings.state',
                DB::raw('COUNT(bookings.id) as total_bookings'),
                DB::raw('SUM(bookings.total_amount) as total_revenue'),
                DB::raw('AVG(bookings.total_amount) as average_booking_value'),
                DB::raw('COUNT(DISTINCT bookings.vendor_id) as unique_vendors'),
                DB::raw('COUNT(DISTINCT bookings.customer_id) as unique_customers'),
                DB::raw('COUNT(DISTINCT hoardings.id) as active_hoardings')
            )
            ->groupBy('hoardings.city', 'hoardings.state')
            ->orderByDesc('total_revenue')
            ->paginate(20);
        
        // Get map data for visualization
        $mapData = $locations->map(function($location) {
            return [
                'location' => $location->city . ', ' . $location->state,
                'revenue' => (float) $location->total_revenue,
                'bookings' => (int) $location->total_bookings,
            ];
        });
        
        return view('admin.revenue.location-revenue', compact('locations', 'mapData', 'period'));
    }

    /**
     * Get commission analytics
     */
    public function commissionAnalytics(Request $request)
    {
        $period = $request->input('period', 'month');
        $dateRange = $this->revenueService->getDateRange($period);
        
        $commissions = DB::table('commission_transactions')
            ->join('bookings', 'commission_transactions.booking_id', '=', 'bookings.id')
            ->join('users', 'commission_transactions.vendor_id', '=', 'users.id')
            ->whereBetween('commission_transactions.created_at', $dateRange)
            ->select(
                'commission_transactions.*',
                'bookings.start_date',
                'bookings.end_date',
                'users.name as vendor_name',
                'users.email as vendor_email'
            )
            ->orderBy('commission_transactions.created_at', 'desc')
            ->paginate(50);
        
        // Summary statistics
        $summary = DB::table('commission_transactions')
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(commission_amount) as total_commission,
                SUM(gst_on_commission) as total_gst,
                SUM(tds_deducted) as total_tds,
                AVG(commission_rate) as avg_commission_rate,
                SUM(CASE WHEN status = "settled" THEN commission_amount ELSE 0 END) as settled_amount,
                SUM(CASE WHEN status = "pending" THEN commission_amount ELSE 0 END) as pending_amount
            ')
            ->first();
        
        return view('admin.revenue.commission-analytics', compact('commissions', 'summary', 'period'));
    }

    /**
     * Get payout management view
     */
    public function payoutManagement(Request $request)
    {
        $status = $request->input('status', 'pending');
        
        $payouts = DB::table('payout_requests')
            ->join('users', 'payout_requests.vendor_id', '=', 'users.id')
            ->select(
                'payout_requests.*',
                'users.name as vendor_name',
                'users.email as vendor_email',
                'users.phone as vendor_phone'
            )
            ->when($status !== 'all', function($query) use ($status) {
                $query->where('payout_requests.status', $status);
            })
            ->orderBy('payout_requests.created_at', 'desc')
            ->paginate(20);
        
        // Get summary
        $summary = DB::table('payout_requests')
            ->selectRaw('
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = "pending" THEN payout_amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = "processing" THEN payout_amount ELSE 0 END) as processing_amount,
                SUM(CASE WHEN status = "completed" THEN payout_amount ELSE 0 END) as completed_amount,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->first();
        
        return view('admin.revenue.payout-management', compact('payouts', 'summary', 'status'));
    }

    /**
     * Export revenue report
     */
    public function export(Request $request)
    {
        $period = $request->input('period', 'month');
        $type = $request->input('type', 'summary'); // summary, bookings, commissions, payouts
        
        $dateRange = $this->revenueService->getDateRange($period);
        
        $filename = "revenue_report_{$type}_" . Carbon::now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($type, $dateRange) {
            $file = fopen('php://output', 'w');
            
            if ($type === 'summary') {
                $this->exportSummary($file, $dateRange);
            } elseif ($type === 'bookings') {
                $this->exportBookings($file, $dateRange);
            } elseif ($type === 'commissions') {
                $this->exportCommissions($file, $dateRange);
            } elseif ($type === 'payouts') {
                $this->exportPayouts($file, $dateRange);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get recent high-value bookings
     */
    protected function getRecentHighValueBookings(int $limit = 10): array
    {
        return DB::table('bookings')
            ->join('users as customers', 'bookings.customer_id', '=', 'customers.id')
            ->join('users as vendors', 'bookings.vendor_id', '=', 'vendors.id')
            ->join('hoardings', 'bookings.hoarding_id', '=', 'hoardings.id')
            ->where('bookings.status', 'confirmed')
            ->where('bookings.total_amount', '>=', 50000)
            ->select(
                'bookings.id',
                'bookings.total_amount',
                'bookings.confirmed_at',
                'customers.name as customer_name',
                'vendors.name as vendor_name',
                'hoardings.city',
                'hoardings.location_name'
            )
            ->orderBy('bookings.confirmed_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get commission distribution
     */
    protected function getCommissionDistribution(string $period): array
    {
        $dateRange = $this->revenueService->getDateRange($period);
        
        $distribution = DB::table('commission_transactions')
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                CASE 
                    WHEN commission_rate < 10 THEN "0-10%"
                    WHEN commission_rate >= 10 AND commission_rate < 15 THEN "10-15%"
                    WHEN commission_rate >= 15 AND commission_rate < 20 THEN "15-20%"
                    ELSE "20%+"
                END as rate_range,
                COUNT(*) as count,
                SUM(commission_amount) as total_commission
            ')
            ->groupBy('rate_range')
            ->get()
            ->toArray();
        
        return array_map(function($item) {
            return [
                'range' => $item->rate_range,
                'count' => (int) $item->count,
                'total' => (float) $item->total_commission,
            ];
        }, $distribution);
    }

    /**
     * Get daily snapshots
     */
    protected function getDailySnapshots(int $days = 7): array
    {
        return DB::table('daily_revenue_snapshots')
            ->where('snapshot_date', '>=', Carbon::now()->subDays($days - 1)->toDateString())
            ->orderBy('snapshot_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Export summary report
     */
    protected function exportSummary($file, array $dateRange): void
    {
        fputcsv($file, ['Revenue Summary Report']);
        fputcsv($file, ['Period', Carbon::parse($dateRange[0])->format('Y-m-d') . ' to ' . Carbon::parse($dateRange[1])->format('Y-m-d')]);
        fputcsv($file, []);
        
        $stats = $this->revenueService->getDashboardStats('custom');
        
        fputcsv($file, ['Metric', 'Value']);
        fputcsv($file, ['Total Bookings', $stats['bookings']['total']]);
        fputcsv($file, ['Confirmed Bookings', $stats['bookings']['confirmed']]);
        fputcsv($file, ['Gross Revenue', '₹' . number_format($stats['revenue']['gross_revenue'], 2)]);
        fputcsv($file, ['Commission Earned', '₹' . number_format($stats['commissions']['total_earned'], 2)]);
        fputcsv($file, ['Pending Payouts', '₹' . number_format($stats['payouts']['pending_payouts'], 2)]);
    }

    /**
     * Export bookings report
     */
    protected function exportBookings($file, array $dateRange): void
    {
        fputcsv($file, ['Booking ID', 'Customer', 'Vendor', 'Location', 'Amount', 'Status', 'Date']);
        
        DB::table('bookings')
            ->join('users as customers', 'bookings.customer_id', '=', 'customers.id')
            ->join('users as vendors', 'bookings.vendor_id', '=', 'vendors.id')
            ->join('hoardings', 'bookings.hoarding_id', '=', 'hoardings.id')
            ->whereBetween('bookings.created_at', $dateRange)
            ->select(
                'bookings.id',
                'customers.name as customer',
                'vendors.name as vendor',
                DB::raw('CONCAT(hoardings.city, " - ", hoardings.location_name) as location'),
                'bookings.total_amount',
                'bookings.status',
                'bookings.created_at'
            )
            ->orderBy('bookings.created_at', 'desc')
            ->chunk(1000, function($bookings) use ($file) {
                foreach ($bookings as $booking) {
                    fputcsv($file, [
                        $booking->id,
                        $booking->customer,
                        $booking->vendor,
                        $booking->location,
                        '₹' . number_format($booking->total_amount, 2),
                        $booking->status,
                        Carbon::parse($booking->created_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });
    }

    /**
     * Export commissions report
     */
    protected function exportCommissions($file, array $dateRange): void
    {
        fputcsv($file, ['Transaction ID', 'Vendor', 'Booking Amount', 'Commission Rate', 'Commission Amount', 'Vendor Amount', 'Status', 'Date']);
        
        DB::table('commission_transactions')
            ->join('users', 'commission_transactions.vendor_id', '=', 'users.id')
            ->whereBetween('commission_transactions.created_at', $dateRange)
            ->select(
                'commission_transactions.id',
                'users.name as vendor',
                'commission_transactions.booking_amount',
                'commission_transactions.commission_rate',
                'commission_transactions.commission_amount',
                'commission_transactions.vendor_amount',
                'commission_transactions.status',
                'commission_transactions.created_at'
            )
            ->orderBy('commission_transactions.created_at', 'desc')
            ->chunk(1000, function($commissions) use ($file) {
                foreach ($commissions as $commission) {
                    fputcsv($file, [
                        $commission->id,
                        $commission->vendor,
                        '₹' . number_format($commission->booking_amount, 2),
                        $commission->commission_rate . '%',
                        '₹' . number_format($commission->commission_amount, 2),
                        '₹' . number_format($commission->vendor_amount, 2),
                        $commission->status,
                        Carbon::parse($commission->created_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });
    }

    /**
     * Export payouts report
     */
    protected function exportPayouts($file, array $dateRange): void
    {
        fputcsv($file, ['Payout ID', 'Vendor', 'Amount', 'Status', 'Requested Date', 'Completed Date']);
        
        DB::table('payout_requests')
            ->join('users', 'payout_requests.vendor_id', '=', 'users.id')
            ->whereBetween('payout_requests.created_at', $dateRange)
            ->select(
                'payout_requests.id',
                'users.name as vendor',
                'payout_requests.payout_amount',
                'payout_requests.status',
                'payout_requests.created_at',
                'payout_requests.completed_at'
            )
            ->orderBy('payout_requests.created_at', 'desc')
            ->chunk(1000, function($payouts) use ($file) {
                foreach ($payouts as $payout) {
                    fputcsv($file, [
                        $payout->id,
                        $payout->vendor,
                        '₹' . number_format($payout->payout_amount, 2),
                        $payout->status,
                        Carbon::parse($payout->created_at)->format('Y-m-d H:i:s'),
                        $payout->completed_at ? Carbon::parse($payout->completed_at)->format('Y-m-d H:i:s') : 'N/A',
                    ]);
                }
            });
    }
}
