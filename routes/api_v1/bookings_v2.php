<?php

use Illuminate\Support\Facades\Route;
use Modules\Bookings\Controllers\Api\BookingController;

/**
 * Bookings Module API Routes (v1)
 * Base: /api/v1/bookings-v2
 * 
 * New Booking Module with Payment Hold System
 */

// Customer routes - Booking creation and payment
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    // Create booking from quotation
    Route::post('/quotations/{quotationId}/book', [BookingController::class, 'createFromQuotation']);
    
    // Create Razorpay order
    Route::post('/{id}/create-order', [BookingController::class, 'createOrder']);
    
    // Move to payment hold (deprecated - createOrder handles this)
    Route::patch('/{id}/payment-hold', [BookingController::class, 'moveToPaymentHold']);
    
    // Confirm booking after payment
    Route::patch('/{id}/confirm', [BookingController::class, 'confirm']);
    
    // Cancel booking
    Route::patch('/{id}/cancel', [BookingController::class, 'cancel']);
    
    // List bookings
    Route::get('/', [BookingController::class, 'index']);
    
    // Show booking details
    Route::get('/{id}', [BookingController::class, 'show']);
});

// Vendor routes
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    // List vendor bookings
    Route::get('/vendor', [BookingController::class, 'index']);
    
    // Cancel booking
    Route::patch('/{id}/cancel', [BookingController::class, 'cancel']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // List all bookings
    Route::get('/admin', [BookingController::class, 'index']);
    
    // Release expired holds (cron endpoint)
    Route::post('/release-expired-holds', [BookingController::class, 'releaseExpiredHolds']);
});
