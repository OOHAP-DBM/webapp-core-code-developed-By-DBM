<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingOverlapController;

/**
 * PROMPT 101: Booking Overlap Validation Engine Routes
 * Base: /api/v1/booking-overlap
 * 
 * Endpoints for checking booking date conflicts and availability
 */

// Public routes (accessible to all authenticated users)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Main overlap check - detailed validation
    Route::post('/check', [BookingOverlapController::class, 'checkOverlap'])
        ->name('api.booking-overlap.check');
    
    // Quick boolean availability check
    Route::get('/is-available', [BookingOverlapController::class, 'isAvailable'])
        ->name('api.booking-overlap.is-available');
    
    // Batch check multiple date ranges
    Route::post('/batch-check', [BookingOverlapController::class, 'batchCheck'])
        ->name('api.booking-overlap.batch-check');
    
    // Get occupied dates in a range (for calendar views)
    Route::get('/occupied-dates', [BookingOverlapController::class, 'getOccupiedDates'])
        ->name('api.booking-overlap.occupied-dates');
    
    // Find next available slot
    Route::get('/find-next-slot', [BookingOverlapController::class, 'findNextSlot'])
        ->name('api.booking-overlap.find-next-slot');
    
    // Get conflicts (detailed)
    Route::get('/conflicts', [BookingOverlapController::class, 'getConflicts'])
        ->name('api.booking-overlap.conflicts');
    
    // Comprehensive availability report
    Route::get('/availability-report', [BookingOverlapController::class, 'getAvailabilityReport'])
        ->name('api.booking-overlap.availability-report');
});
