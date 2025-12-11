<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CaptureExpiredHoldsJob;
use App\Services\HoardingBookingService;
use App\Services\SLATrackingService;

// ============================================
// Scheduled Tasks
// ============================================

// Capture expired payment holds every 5 minutes
Schedule::job(new CaptureExpiredHoldsJob())
    ->everyFiveMinutes()
    ->name('capture-expired-holds')
    ->withoutOverlapping(10) // Prevent overlapping runs, wait 10 min before retry
    ->onOneServer(); // Only run on one server in multi-server setup

// Cleanup expired booking drafts every hour
Schedule::call(function () {
    $count = app(HoardingBookingService::class)->cleanupExpiredDrafts();
    if ($count > 0) {
        info("Cleaned up {$count} expired booking drafts");
    }
})
    ->hourly()
    ->name('cleanup-expired-drafts')
    ->withoutOverlapping(5)
    ->onOneServer();

// Release expired booking holds every minute
Schedule::call(function () {
    $count = app(HoardingBookingService::class)->releaseExpiredHolds();
    if ($count > 0) {
        info("Released {$count} expired booking holds");
    }
})
    ->everyMinute()
    ->name('release-expired-holds')
    ->withoutOverlapping(2)
    ->onOneServer();

// Monitor vendor SLA deadlines hourly (PROMPT 68)
Schedule::command('sla:monitor')
    ->hourly()
    ->name('monitor-vendor-slas')
    ->withoutOverlapping(10)
    ->onOneServer();

// Check milestone due dates daily (PROMPT 70 Phase 2)
Schedule::command('milestones:check-due')
    ->dailyAt('09:00') // Run at 9 AM daily
    ->name('check-milestones-due')
    ->withoutOverlapping(15)
    ->onOneServer();

// Send upcoming milestone reminders (3 days before)
Schedule::command('milestones:check-due --days-ahead=3')
    ->dailyAt('10:00') // Run at 10 AM daily
    ->name('milestone-reminders')
    ->withoutOverlapping(15)
    ->onOneServer();

// Process daily vendor reliability score recovery (PROMPT 68)
Schedule::call(function () {
    app(SLATrackingService::class)->processDailyRecovery();
})
    ->daily()
    ->at('00:00')
    ->name('vendor-reliability-recovery')
    ->withoutOverlapping(10)
    ->onOneServer();

// Reset monthly violation counts on 1st of each month (PROMPT 68)
Schedule::call(function () {
    app(SLATrackingService::class)->resetMonthlyViolationCounts();
})
    ->monthlyOn(1, '00:00')
    ->name('reset-vendor-violations')
    ->withoutOverlapping(10)
    ->onOneServer();

// Monitor fraud activity every hour (PROMPT 73)
Schedule::command('fraud:monitor')
    ->hourly()
    ->name('fraud-monitoring')
    ->withoutOverlapping(15)
    ->onOneServer();

// Daily fraud monitoring with notifications at 8 AM
Schedule::command('fraud:monitor --notify')
    ->dailyAt('08:00')
    ->name('fraud-daily-notify')
    ->withoutOverlapping(15)
    ->onOneServer();

// Weekly cleanup of old fraud alerts (Sunday at midnight)
Schedule::command('fraud:monitor --cleanup')
    ->weekly()
    ->sundays()
    ->at('00:00')
    ->name('fraud-cleanup')
    ->withoutOverlapping(15)
    ->onOneServer();

// Generate daily revenue snapshots at 1 AM (PROMPT 74)
Schedule::command('revenue:generate-snapshots --vendors --locations')
    ->dailyAt('01:00')
    ->name('generate-revenue-snapshots')
    ->withoutOverlapping(30)
    ->onOneServer();

// ============================================
// Artisan Commands
// ============================================

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
