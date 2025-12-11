<?php

namespace App\Console\Commands;

use App\Services\RevenueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateRevenueSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:generate-snapshots 
                            {--date= : Specific date to generate snapshot for (Y-m-d)}
                            {--days=1 : Number of days to generate snapshots for}
                            {--vendors : Also update vendor revenue stats}
                            {--locations : Also update location revenue stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily revenue snapshots for dashboard analytics';

    protected RevenueService $revenueService;

    /**
     * Create a new command instance.
     */
    public function __construct(RevenueService $revenueService)
    {
        parent::__construct();
        $this->revenueService = $revenueService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Revenue Snapshot Generation...');
        
        $date = $this->option('date') 
            ? Carbon::parse($this->option('date')) 
            : Carbon::yesterday();
        
        $days = (int) $this->option('days');
        
        $this->info("📅 Generating snapshots for {$days} day(s) starting from {$date->toDateString()}");
        
        $bar = $this->output->createProgressBar($days);
        $bar->start();
        
        $generatedCount = 0;
        $errors = [];
        
        for ($i = 0; $i < $days; $i++) {
            $currentDate = $date->copy()->subDays($i);
            
            try {
                // Generate daily snapshot
                $this->revenueService->generateDailySnapshot($currentDate);
                $generatedCount++;
                
                // Update vendor stats if requested
                if ($this->option('vendors')) {
                    $this->updateVendorStats($currentDate);
                }
                
                // Update location stats if requested
                if ($this->option('locations')) {
                    $this->updateLocationStats($currentDate);
                }
                
                $bar->advance();
            } catch (\Exception $e) {
                $errors[] = "Error for {$currentDate->toDateString()}: " . $e->getMessage();
                $this->error("\n❌ Failed for {$currentDate->toDateString()}: " . $e->getMessage());
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display summary
        $this->displaySummary($generatedCount, $errors);
        
        return 0;
    }

    /**
     * Update vendor revenue stats for a specific date
     */
    protected function updateVendorStats(Carbon $date): void
    {
        $dateRange = [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
        
        // Get all vendors who had bookings on this date
        $vendorIds = DB::table('bookings')
            ->whereBetween('confirmed_at', $dateRange)
            ->where('status', 'confirmed')
            ->distinct()
            ->pluck('vendor_id');
        
        foreach ($vendorIds as $vendorId) {
            try {
                $this->revenueService->updateVendorStats($vendorId, $date);
            } catch (\Exception $e) {
                $this->warn("Failed to update vendor {$vendorId}: " . $e->getMessage());
            }
        }
        
        $this->line("  ✓ Updated stats for " . count($vendorIds) . " vendors");
    }

    /**
     * Update location revenue stats for a specific date
     */
    protected function updateLocationStats(Carbon $date): void
    {
        $dateRange = [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
        
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
            ->get();
        
        foreach ($locations as $location) {
            // Get previous day data for growth calculation
            $previousSnapshot = DB::table('location_revenue_stats')
                ->where('city', $location->city)
                ->where('state', $location->state)
                ->where('period_date', $date->copy()->subDay()->toDateString())
                ->where('period_type', 'daily')
                ->first();
            
            $revenueGrowth = 0;
            if ($previousSnapshot && $previousSnapshot->gross_revenue > 0) {
                $revenueGrowth = (($location->total_revenue - $previousSnapshot->gross_revenue) / $previousSnapshot->gross_revenue) * 100;
            }
            
            // Calculate commission (assume 15% average)
            $commission = $location->total_revenue * 0.15;
            
            DB::table('location_revenue_stats')->updateOrInsert(
                [
                    'city' => $location->city,
                    'state' => $location->state,
                    'period_date' => $date->toDateString(),
                    'period_type' => 'daily',
                ],
                [
                    'total_bookings' => $location->total_bookings,
                    'active_hoardings' => $location->active_hoardings,
                    'unique_vendors' => $location->unique_vendors,
                    'unique_customers' => $location->unique_customers,
                    'gross_revenue' => $location->total_revenue,
                    'commission_earned' => $commission,
                    'average_booking_value' => $location->average_booking_value,
                    'revenue_growth_percent' => round($revenueGrowth, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
        $this->line("  ✓ Updated stats for " . count($locations) . " locations");
    }

    /**
     * Display command summary
     */
    protected function displaySummary(int $generatedCount, array $errors): void
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Revenue Snapshot Generation Summary');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Snapshots Generated', $generatedCount],
                ['Errors', count($errors)],
                ['Vendors Updated', $this->option('vendors') ? 'Yes' : 'No'],
                ['Locations Updated', $this->option('locations') ? 'Yes' : 'No'],
            ]
        );
        
        if (count($errors) > 0) {
            $this->error('❌ Errors encountered:');
            foreach ($errors as $error) {
                $this->line("  • {$error}");
            }
        }
        
        // Get latest snapshot stats
        $latestSnapshot = DB::table('daily_revenue_snapshots')
            ->latest('snapshot_date')
            ->first();
        
        if ($latestSnapshot) {
            $this->newLine();
            $this->info('📈 Latest Snapshot Overview:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Date', $latestSnapshot->snapshot_date],
                    ['Total Bookings', $latestSnapshot->total_bookings],
                    ['Gross Revenue', '₹' . number_format($latestSnapshot->gross_revenue, 2)],
                    ['Commission Earned', '₹' . number_format($latestSnapshot->commission_earned, 2)],
                    ['Revenue Growth', $latestSnapshot->revenue_growth_percent . '%'],
                ]
            );
        }
        
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✅ Revenue snapshot generation completed!');
    }
}
