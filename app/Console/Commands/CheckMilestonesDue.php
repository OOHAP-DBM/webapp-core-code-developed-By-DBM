<?php

namespace App\Console\Commands;

use App\Models\QuotationMilestone;
use App\Notifications\MilestoneDueNotification;
use App\Notifications\MilestoneOverdueNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * CheckMilestonesDue
 * 
 * PROMPT 70 Phase 2: Scheduled command to detect overdue milestones
 * and send notifications
 * 
 * Schedule: Run daily to check milestone due dates
 */
class CheckMilestonesDue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'milestones:check-due
                            {--days-ahead=3 : Days ahead to check for upcoming due dates}
                            {--force : Force run even if already run today}';

    /**
     * The console command description.
     */
    protected $description = 'Check for overdue and upcoming milestone payments, send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking milestone due dates...');
        $daysAhead = (int) $this->option('days-ahead');

        $stats = [
            'overdue_detected' => 0,
            'overdue_updated' => 0,
            'due_soon' => 0,
            'notifications_sent' => 0,
        ];

        // 1. Check for overdue milestones
        $stats = $this->checkOverdueMilestones($stats);

        // 2. Check for upcoming due milestones
        $stats = $this->checkUpcomingDue($daysAhead, $stats);

        // 3. Update milestone statuses
        $stats = $this->updateMilestoneStatuses($stats);

        // Summary
        $this->info("\n✅ Milestone check complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Overdue Detected', $stats['overdue_detected']],
                ['Overdue Updated', $stats['overdue_updated']],
                ['Due Soon (next ' . $daysAhead . ' days)', $stats['due_soon']],
                ['Notifications Sent', $stats['notifications_sent']],
            ]
        );

        Log::info('Milestone due check completed', $stats);

        return 0;
    }

    /**
     * Check for overdue milestones and send notifications
     */
    protected function checkOverdueMilestones(array $stats): array
    {
        $this->line("\n📌 Checking overdue milestones...");

        // Get all unpaid milestones past their due date
        $overdueMilestones = QuotationMilestone::with(['quotation.enquiry.customer', 'quotation.enquiry.vendor'])
            ->whereIn('status', [QuotationMilestone::STATUS_DUE, QuotationMilestone::STATUS_PENDING])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today())
            ->get();

        $stats['overdue_detected'] = $overdueMilestones->count();

        foreach ($overdueMilestones as $milestone) {
            $daysOverdue = Carbon::parse($milestone->due_date)->diffInDays(Carbon::today());
            
            // Update status to overdue
            if ($milestone->status !== QuotationMilestone::STATUS_OVERDUE) {
                $milestone->update(['status' => QuotationMilestone::STATUS_OVERDUE]);
                $stats['overdue_updated']++;
                
                $this->warn("  ⚠️  Milestone #{$milestone->id} - {$milestone->title} is {$daysOverdue} day(s) overdue");
                
                // Send notifications (only on first detection or every 3 days)
                if ($daysOverdue <= 1 || $daysOverdue % 3 === 0) {
                    try {
                        // Notify customer
                        $customer = $milestone->quotation->enquiry->customer;
                        if ($customer) {
                            $customer->notify(new MilestoneOverdueNotification($milestone, $daysOverdue));
                            $stats['notifications_sent']++;
                        }

                        // Notify vendor
                        $vendor = $milestone->quotation->enquiry->vendor;
                        if ($vendor) {
                            $vendor->notify(new MilestoneOverdueNotification($milestone, $daysOverdue));
                            $stats['notifications_sent']++;
                        }

                        $this->line("    📧 Notifications sent to customer and vendor");
                    } catch (\Exception $e) {
                        $this->error("    ❌ Failed to send notifications: " . $e->getMessage());
                        Log::error('Failed to send overdue notification', [
                            'milestone_id' => $milestone->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Check for milestones due in the next N days
     */
    protected function checkUpcomingDue(int $daysAhead, array $stats): array
    {
        $this->line("\n📅 Checking milestones due in next {$daysAhead} days...");

        for ($day = 1; $day <= $daysAhead; $day++) {
            $targetDate = Carbon::today()->addDays($day);
            
            $upcomingMilestones = QuotationMilestone::with(['quotation.enquiry.customer'])
                ->whereIn('status', [QuotationMilestone::STATUS_PENDING, QuotationMilestone::STATUS_DUE])
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($upcomingMilestones as $milestone) {
                $stats['due_soon']++;
                
                $this->info("  📅 Milestone #{$milestone->id} - {$milestone->title} due in {$day} day(s)");
                
                // Send reminder notification
                try {
                    $customer = $milestone->quotation->enquiry->customer;
                    if ($customer) {
                        $customer->notify(new MilestoneDueNotification($milestone, $day));
                        $stats['notifications_sent']++;
                        $this->line("    📧 Reminder sent to customer");
                    }
                } catch (\Exception $e) {
                    $this->error("    ❌ Failed to send reminder: " . $e->getMessage());
                    Log::error('Failed to send due notification', [
                        'milestone_id' => $milestone->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $stats;
    }

    /**
     * Update milestone statuses based on due dates
     */
    protected function updateMilestoneStatuses(array $stats): array
    {
        $this->line("\n🔄 Updating milestone statuses...");

        // Set milestones as "due" if due date is today or earlier (and not paid)
        $pendingDue = QuotationMilestone::where('status', QuotationMilestone::STATUS_PENDING)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', Carbon::today())
            ->update(['status' => QuotationMilestone::STATUS_DUE]);

        if ($pendingDue > 0) {
            $this->line("  ✓ Updated {$pendingDue} milestones to DUE status");
        }

        return $stats;
    }
}
