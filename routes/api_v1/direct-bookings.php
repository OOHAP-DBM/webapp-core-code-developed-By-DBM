<?php

use Illuminate\Support\Facades\Route;
use Modules\Bookings\Controllers\Api\DirectBookingController;
use Modules\Campaigns\Controllers\Api\PODController;

/*
|--------------------------------------------------------------------------
| Direct Booking API Routes
|--------------------------------------------------------------------------
|
| Customer can book hoardings directly without quotation
| Payment via Razorpay with 30-minute hold
| Auto-refund if cancelled within 30 minutes
|
*/

Route::middleware(['auth:sanctum', 'role:customer'])->prefix('customer')->group(function () {
    
    // Direct Booking Routes
    Route::prefix('direct-bookings')->group(function () {
        // Search & Availability
        Route::get('/available-hoardings', [DirectBookingController::class, 'getAvailableHoardings']);
        Route::post('/check-availability', [DirectBookingController::class, 'checkAvailability']);
        
        // Create booking
        Route::post('/', [DirectBookingController::class, 'store']);
        
        // View booking
        Route::get('/{id}', [DirectBookingController::class, 'show']);
        
        // Payment flow
        Route::post('/{id}/initiate-payment', [DirectBookingController::class, 'initiatePayment']);
        Route::post('/{id}/confirm-payment', [DirectBookingController::class, 'confirmPayment']);
        
        // Cancellation (auto-refund within 30 mins)
        Route::post('/{id}/cancel', [DirectBookingController::class, 'cancel']);
    });

    // POD Submission (Customer can view)
    Route::prefix('bookings/{booking_id}/pod')->group(function () {
        Route::get('/', [PODController::class, 'index']); // View POD submissions for booking
        Route::get('/{id}', [PODController::class, 'show']); // View specific POD
    });
});

// Vendor POD Management
Route::middleware(['auth:sanctum', 'role:vendor'])->prefix('vendor')->group(function () {
    Route::prefix('pod')->group(function () {
        // View pending PODs
        Route::get('/pending', [PODController::class, 'getPendingForVendor']);
        
        // Approve/Reject POD
        Route::post('/{id}/approve', [PODController::class, 'approve']);
        Route::post('/{id}/reject', [PODController::class, 'reject']);
    });
});

// Mounter POD Submission
Route::middleware(['auth:sanctum', 'role:mounter'])->prefix('mounter')->group(function () {
    Route::prefix('bookings/{booking_id}/pod')->group(function () {
        Route::post('/submit', [PODController::class, 'submit']); // Submit POD
    });
});
