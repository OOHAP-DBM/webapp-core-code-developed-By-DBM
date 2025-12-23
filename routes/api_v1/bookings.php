<?php

use Illuminate\Support\Facades\Route;

/**
 * Booking API Routes (v1)
 * Base: /api/v1/bookings
 * 
 * Campaign bookings with lifecycle management
 * Critical operations have stricter rate limits
 */

// Customer routes with conservative rate limiting
Route::middleware(['auth:sanctum', 'role:customer', 'throttle:critical'])->group(function () {
    Route::get('/', [\Modules\Bookings\Controllers\Api\BookingController::class, 'index']);
    Route::get('/{id}', [\Modules\Bookings\Controllers\Api\BookingController::class, 'show']);
    Route::post('/{id}/void', [\Modules\Bookings\Controllers\Api\BookingController::class, 'voidBooking']);
    Route::get('/{id}/timeline', [\Modules\Bookings\Controllers\Api\BookingController::class, 'timeline']);
});

// Vendor routes with authenticated rate limiting
Route::middleware(['auth:sanctum', 'role:vendor', 'throttle:authenticated'])->group(function () {
    Route::get('/vendor/bookings', [\Modules\Bookings\Controllers\Api\BookingController::class, 'vendorBookings']);
    Route::post('/{id}/approve-pod', [\Modules\Bookings\Controllers\Api\BookingController::class, 'approvePOD']);
    Route::post('/{id}/reject-pod', [\Modules\Bookings\Controllers\Api\BookingController::class, 'rejectPOD']);
});

// Staff routes (Mounter) with authenticated rate limiting
Route::middleware(['auth:sanctum', 'role:staff', 'throttle:authenticated'])->group(function () {
    Route::post('/{id}/upload-pod', [\Modules\Bookings\Controllers\Api\BookingController::class, 'uploadPOD']);
    Route::post('/{id}/mark-mounted', [\Modules\Bookings\Controllers\Api\BookingController::class, 'markMounted']);
    Route::post('/{id}/mark-dismounted', [\Modules\Bookings\Controllers\Api\BookingController::class, 'markDismounted']);
});

// Admin routes with high authenticated rate limiting
Route::middleware(['auth:sanctum', 'role:admin', 'throttle:authenticated'])->group(function () {
    Route::get('/admin/all', [\Modules\Bookings\Controllers\Api\BookingController::class, 'adminBookings']);
    Route::post('/{id}/force-capture', [\Modules\Bookings\Controllers\Api\BookingController::class, 'forceCapture']);
});
