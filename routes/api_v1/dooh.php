<?php

use Illuminate\Support\Facades\Route;
use Modules\DOOH\Controllers\Api\DOOHPackageBookingController;
use Modules\DOOH\Controllers\Api\DOOHScreenController;

/**
 * DOOH Package Booking API Routes (v1)
 * Base: /api/v1/dooh
 * 
 * Digital OOH screens with package-based bookings
 */

// ============================================
// CUSTOMER ROUTES
// ============================================
Route::middleware(['auth:sanctum', 'role:customer'])->prefix('customer')->group(function () {
    
    // Browse screens and packages
    Route::get('/dooh/screens', [DOOHPackageBookingController::class, 'getScreens']);
    Route::get('/dooh/screens/{id}', [DOOHPackageBookingController::class, 'getScreenDetails']);
    
    // Check package availability
    Route::post('/dooh/packages/{id}/check-availability', [DOOHPackageBookingController::class, 'checkAvailability']);
    
    // Bookings
    Route::get('/dooh/bookings', [DOOHPackageBookingController::class, 'getCustomerBookings']);
    Route::post('/dooh/bookings', [DOOHPackageBookingController::class, 'createBooking']);
    Route::get('/dooh/bookings/{id}', [DOOHPackageBookingController::class, 'getBooking']);
    
    // Payment flow
    Route::post('/dooh/bookings/{id}/initiate-payment', [DOOHPackageBookingController::class, 'initiatePayment']);
    Route::post('/dooh/bookings/{id}/confirm-payment', [DOOHPackageBookingController::class, 'confirmPayment']);
    
    // Content upload
    Route::post('/dooh/bookings/{id}/upload-content', [DOOHPackageBookingController::class, 'uploadContent']);
    
    // Cancellation
    Route::post('/dooh/bookings/{id}/cancel', [DOOHPackageBookingController::class, 'cancelBooking']);
});

// ============================================
// VENDOR ROUTES
// ============================================
Route::middleware(['auth:sanctum', 'role:vendor'])->prefix('vendor')->group(function () {
    
    Route::get('/dooh/draft', [DOOHScreenController::class, 'getDraft']);
    Route::post('dooh/store', [DOOHScreenController::class, 'store']);
    // Vendor's bookings
    Route::get('/dooh/bookings', [DOOHPackageBookingController::class, 'getVendorBookings']);
    
    // Content approval
    Route::post('/dooh/bookings/{id}/approve-content', [DOOHPackageBookingController::class, 'approveContent']);
    Route::post('/dooh/bookings/{id}/reject-content', [DOOHPackageBookingController::class, 'rejectContent']);
    
    // API integration
    Route::post('/dooh/sync-screens', [DOOHPackageBookingController::class, 'syncScreens']);
    Route::get('/dooh/test-connection', [DOOHPackageBookingController::class, 'testConnection']);
});


