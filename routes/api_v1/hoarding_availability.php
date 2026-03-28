<?php

use App\Http\Controllers\Api\HoardingAvailabilityController;
use Illuminate\Support\Facades\Route;

/**
 * PROMPT 104: Hoarding Availability API for Frontend Calendar
 * Routes for availability calendar endpoints
 * 
 * Authentication: All routes require auth:sanctum
 */

Route::middleware(['auth:sanctum'])->prefix('hoardings/{hoarding}')->group(function () {
    // Get availability calendar for date range
    Route::get('/availability/calendar', [HoardingAvailabilityController::class, 'getCalendar'])
        ->name('api.hoardings.availability.calendar');
    
    // Get availability summary (counts only)
    Route::get('/availability/summary', [HoardingAvailabilityController::class, 'getSummary'])
        ->name('api.hoardings.availability.summary');
    
    // Get month calendar
    Route::get('/availability/month/{year}/{month}', [HoardingAvailabilityController::class, 'getMonthCalendar'])
        ->name('api.hoardings.availability.month');
    
    // Check multiple specific dates (batch)
    Route::post('/availability/check-dates', [HoardingAvailabilityController::class, 'checkMultipleDates'])
        ->name('api.hoardings.availability.check-dates');
    
    // Get next N available dates
    Route::get('/availability/next-available', [HoardingAvailabilityController::class, 'getNextAvailable'])
        ->name('api.hoardings.availability.next-available');
    
    // Get heatmap data for visualization
    Route::get('/availability/heatmap', [HoardingAvailabilityController::class, 'getHeatmap'])
        ->name('api.hoardings.availability.heatmap');
    
    // Quick status check (single date)
    Route::get('/availability/quick-check', [HoardingAvailabilityController::class, 'quickCheck'])
        ->name('api.hoardings.availability.quick-check');
});
