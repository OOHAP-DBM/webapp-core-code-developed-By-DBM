<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CaptureExpiredHoldsJob;
use App\Services\HoardingBookingService;

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

// ============================================
// Artisan Commands
// ============================================

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
