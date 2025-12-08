<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Modules\Payment\Jobs\CaptureExpiredHoldsJob;

// ============================================
// Scheduled Tasks
// ============================================

// Capture expired payment holds every 5 minutes
Schedule::job(new CaptureExpiredHoldsJob())
    ->everyFiveMinutes()
    ->name('capture-expired-holds')
    ->withoutOverlapping(10) // Prevent overlapping runs, wait 10 min before retry
    ->onOneServer(); // Only run on one server in multi-server setup

// ============================================
// Artisan Commands
// ============================================

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
