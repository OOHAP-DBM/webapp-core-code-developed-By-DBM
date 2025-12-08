<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Controllers\Api\POSBookingController;

/**
 * POS Booking API Routes (v1)
 * Base: /api/v1/vendor/pos
 * 
 * Vendor POS booking management
 */

Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    // Dashboard & Statistics
    Route::get('/dashboard', [POSBookingController::class, 'dashboard']);
    
    // Hoarding Search
    Route::get('/search-hoardings', [POSBookingController::class, 'searchHoardings']);
    
    // Price Calculator
    Route::post('/calculate-price', [POSBookingController::class, 'calculatePrice']);
    
    // POS Bookings CRUD
    Route::get('/bookings', [POSBookingController::class, 'index']);
    Route::post('/bookings', [POSBookingController::class, 'store']);
    Route::get('/bookings/{id}', [POSBookingController::class, 'show']);
    Route::put('/bookings/{id}', [POSBookingController::class, 'update']);
    
    // Payment Actions
    Route::post('/bookings/{id}/mark-cash-collected', [POSBookingController::class, 'markCashCollected']);
    Route::post('/bookings/{id}/convert-to-credit-note', [POSBookingController::class, 'convertToCreditNote']);
    Route::post('/bookings/{id}/cancel-credit-note', [POSBookingController::class, 'cancelCreditNote']);
    
    // Booking Actions
    Route::post('/bookings/{id}/cancel', [POSBookingController::class, 'cancel']);
});
