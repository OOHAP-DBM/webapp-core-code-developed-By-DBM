<?php

namespace App\Console\Commands;

use App\Models\FraudAlert;
use App\Models\RiskProfile;
use App\Models\User;
use App\Services\FraudDetectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitorFraudActivity extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fraud:monitor 
                            {--cleanup : Clean up old resolved alerts}
                            {--recalculate : Recalculate all risk profiles}
                            {--notify : Send notifications for pending critical alerts}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor fraud activity, update risk profiles, and manage alerts';

    private FraudDetectionService $fraudService;

    public function __construct(FraudDetectionService $fraudService)
    {
        parent::__construct();
        $this->fraudService = $fraudService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🛡️  Starting Fraud Monitoring Task...');
        $startTime = now();

        $stats = [
            'critical_alerts_found' => 0,
            'risk_profiles_updated' => 0,
            'users_flagged' => 0,
            'auto_blocks' => 0,
            'notifications_sent' => 0,
            'old_alerts_cleaned' => 0,
        ];

        try {
            // 1. Check for new critical alerts
            $this->info('Checking for critical alerts...');
            $stats['critical_alerts_found'] = $this->checkCriticalAlerts();

            // 2. Update risk profiles
            $this->info('Updating risk profiles...');
            $stats['risk_profiles_updated'] = $this->updateRiskProfiles();

            // 3. Flag high-risk users
            $this->info('Flagging high-risk users...');
            $stats['users_flagged'] = $this->flagHighRiskUsers();

            // 4. Auto-block excessive fraud attempts
            $this->info('Checking for auto-block conditions...');
            $stats['auto_blocks'] = $this->autoBlockExcessiveFraud();

            // 5. Send notifications if requested
            if ($this->option('notify')) {
                $this->info('Sending notifications for critical alerts...');
                $stats['notifications_sent'] = $this->notifyCriticalAlerts();
            }

            // 6. Cleanup old alerts if requested
            if ($this->option('cleanup')) {
                $this->info('Cleaning up old resolved alerts...');
                $stats['old_alerts_cleaned'] = $this->cleanupOldAlerts();
            }

            // 7. Recalculate all risk profiles if requested
            if ($this->option('recalculate')) {
                $this->info('Recalculating all risk profiles...');
                $this->recalculateAllRiskProfiles();
            }

            // Display summary
            $this->displaySummary($stats, $startTime);

            Log::info('Fraud monitoring completed', $stats);
            return 0;

        } catch (\Exception $e) {
            $this->error('Error during fraud monitoring: ' . $e->getMessage());
            Log::error('Fraud monitoring failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Check for critical alerts that need immediate attention
     */
    private function checkCriticalAlerts(): int
    {
        $criticalAlerts = FraudAlert::where('severity', 'critical')
            ->where('status', 'pending')
            ->whereNull('reviewed_at')
            ->get();

        if ($criticalAlerts->count() > 0) {
            $this->warn("⚠️  Found {$criticalAlerts->count()} critical alerts requiring attention!");
            
            $this->table(
                ['ID', 'Type', 'User', 'Risk Score', 'Created'],
                $criticalAlerts->map(fn($alert) => [
                    $alert->id,
                    $alert->alert_type,
                    $alert->user_email,
                    $alert->risk_score,
                    $alert->created_at->diffForHumans(),
                ])
            );
        }

        return $criticalAlerts->count();
    }

    /**
     * Update risk profiles for active users
     */
    private function updateRiskProfiles(): int
    {
        // Get users with recent activity (last 7 days)
        $activeUsers = User::whereHas('bookings', function($query) {
            $query->where('created_at', '>=', now()->subDays(7));
        })->get();

        $updated = 0;
        foreach ($activeUsers as $user) {
            $riskProfile = $this->fraudService->getOrCreateRiskProfile($user);
            
            // Update account age
            $riskProfile->update([
                'account_age_days' => $user->created_at->diffInDays(now()),
            ]);

            // Recalculate risk score
            $riskProfile->recalculateRiskScore();
            $updated++;

            if ($riskProfile->isHighRisk()) {
                $this->line("  → High-risk user detected: {$user->email} (Score: {$riskProfile->overall_risk_score})");
            }
        }

        return $updated;
    }

    /**
     * Flag high-risk users for manual review
     */
    private function flagHighRiskUsers(): int
    {
        $highRiskProfiles = RiskProfile::where('risk_level', 'high')
            ->orWhere('risk_level', 'critical')
            ->where('requires_manual_review', false)
            ->where('is_blocked', false)
            ->get();

        $flagged = 0;
        foreach ($highRiskProfiles as $profile) {
            // Check if multiple alerts exist
            $alertCount = FraudAlert::where('user_id', $profile->user_id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            if ($alertCount >= 3 || $profile->overall_risk_score >= 85) {
                $profile->update(['requires_manual_review' => true]);
                $flagged++;

                $this->warn("  → Flagged user #{$profile->user_id} for manual review (Score: {$profile->overall_risk_score})");
            }
        }

        return $flagged;
    }

    /**
     * Auto-block users with excessive fraud attempts
     */
    private function autoBlockExcessiveFraud(): int
    {
        $blocked = 0;

        // Get users with confirmed fraud
        $confirmedFraudUsers = FraudAlert::where('status', 'confirmed_fraud')
            ->where('created_at', '>=', now()->subDays(7))
            ->pluck('user_id')
            ->unique();

        foreach ($confirmedFraudUsers as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $riskProfile = $this->fraudService->getOrCreateRiskProfile($user);

            // Block if not already blocked
            if (!$riskProfile->is_blocked) {
                $riskProfile->blockUser('Auto-blocked: Confirmed fraud activity detected');
                $blocked++;

                $this->error("  🚫 Auto-blocked user #{$userId} ({$user->email})");
                
                Log::warning('User auto-blocked for fraud', [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'risk_score' => $riskProfile->overall_risk_score,
                ]);
            }
        }

        // Check for excessive failed payments
        $excessiveFailures = DB::table('payment_anomalies')
            ->select('user_id', DB::raw('COUNT(*) as failure_count'))
            ->where('anomaly_type', 'repeated_failure')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('user_id')
            ->having('failure_count', '>=', 15)
            ->get();

        foreach ($excessiveFailures as $record) {
            $user = User::find($record->user_id);
            if (!$user) continue;

            $riskProfile = $this->fraudService->getOrCreateRiskProfile($user);

            if (!$riskProfile->is_blocked) {
                $riskProfile->blockUser("Auto-blocked: {$record->failure_count} failed payment attempts in 7 days");
                $blocked++;

                $this->error("  🚫 Auto-blocked user #{$record->user_id} for excessive payment failures");
            }
        }

        return $blocked;
    }

    /**
     * Send notifications for critical alerts
     */
    private function notifyCriticalAlerts(): int
    {
        // This would integrate with notification system
        // For now, just log
        $pendingCritical = FraudAlert::where('severity', 'critical')
            ->where('status', 'pending')
            ->count();

        if ($pendingCritical > 0) {
            Log::warning('Critical fraud alerts pending', [
                'count' => $pendingCritical,
                'timestamp' => now(),
            ]);
        }

        return $pendingCritical;
    }

    /**
     * Cleanup old resolved alerts
     */
    private function cleanupOldAlerts(): int
    {
        // Soft delete resolved/false positive alerts older than 90 days
        $deleted = FraudAlert::whereIn('status', ['resolved', 'false_positive'])
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        if ($deleted > 0) {
            $this->info("  ✓ Cleaned up {$deleted} old alerts");
        }

        return $deleted;
    }

    /**
     * Recalculate all risk profiles
     */
    private function recalculateAllRiskProfiles(): void
    {
        $this->info('Recalculating all risk profiles...');
        
        $profiles = RiskProfile::all();
        $bar = $this->output->createProgressBar($profiles->count());
        $bar->start();

        foreach ($profiles as $profile) {
            $profile->recalculateRiskScore();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Recalculated {$profiles->count()} risk profiles");
    }

    /**
     * Display summary statistics
     */
    private function displaySummary(array $stats, $startTime): void
    {
        $duration = $startTime->diffInSeconds(now());

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Fraud Monitoring Summary');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Critical Alerts Found', $stats['critical_alerts_found']],
                ['Risk Profiles Updated', $stats['risk_profiles_updated']],
                ['Users Flagged for Review', $stats['users_flagged']],
                ['Auto-Blocked Users', $stats['auto_blocks']],
                ['Notifications Sent', $stats['notifications_sent']],
                ['Old Alerts Cleaned', $stats['old_alerts_cleaned']],
            ]
        );

        $this->info("⏱️  Completed in {$duration} seconds");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Show current system status
        $totalAlerts = FraudAlert::count();
        $pendingAlerts = FraudAlert::where('status', 'pending')->count();
        $blockedUsers = RiskProfile::where('is_blocked', true)->count();

        $this->newLine();
        $this->info('📈 Current System Status:');
        $this->line("  • Total Alerts: {$totalAlerts}");
        $this->line("  • Pending Alerts: {$pendingAlerts}");
        $this->line("  • Blocked Users: {$blockedUsers}");
    }
}
