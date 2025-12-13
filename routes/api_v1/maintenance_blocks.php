<?php

use App\Http\Controllers\Api\MaintenanceBlockController;
use Illuminate\Support\Facades\Route;

/**
 * PROMPT 102: Admin Blocking Periods (Maintenance/Repairs)
 * Routes for managing maintenance blocks
 * 
 * Authentication: All routes require auth:sanctum
 * Authorization: Admin can manage all, Vendor can manage own hoardings
 */

Route::middleware(['auth:sanctum'])->group(function () {
    // List blocks for a hoarding
    Route::get('/', [MaintenanceBlockController::class, 'index'])->name('api.maintenance-blocks.index');
    
    // Get specific block
    Route::get('/{maintenanceBlock}', [MaintenanceBlockController::class, 'show'])->name('api.maintenance-blocks.show');
    
    // Create new block
    Route::post('/', [MaintenanceBlockController::class, 'store'])->name('api.maintenance-blocks.store');
    
    // Update block
    Route::put('/{maintenanceBlock}', [MaintenanceBlockController::class, 'update'])->name('api.maintenance-blocks.update');
    
    // Delete block
    Route::delete('/{maintenanceBlock}', [MaintenanceBlockController::class, 'destroy'])->name('api.maintenance-blocks.destroy');
    
    // Mark block as completed
    Route::post('/{maintenanceBlock}/complete', [MaintenanceBlockController::class, 'markCompleted'])->name('api.maintenance-blocks.complete');
    
    // Mark block as cancelled
    Route::post('/{maintenanceBlock}/cancel', [MaintenanceBlockController::class, 'markCancelled'])->name('api.maintenance-blocks.cancel');
    
    // Check availability (no active blocks)
    Route::get('/check/availability', [MaintenanceBlockController::class, 'checkAvailability'])->name('api.maintenance-blocks.check-availability');
    
    // Get blocked dates for calendar
    Route::get('/check/blocked-dates', [MaintenanceBlockController::class, 'getBlockedDates'])->name('api.maintenance-blocks.blocked-dates');
    
    // Get statistics
    Route::get('/check/statistics', [MaintenanceBlockController::class, 'getStatistics'])->name('api.maintenance-blocks.statistics');
    
    // Get conflicting bookings
    Route::get('/check/conflicting-bookings', [MaintenanceBlockController::class, 'getConflictingBookings'])->name('api.maintenance-blocks.conflicting-bookings');
});