<?php

use Illuminate\Support\Facades\Route;

/**
 * Booking API Routes (v1)
 * Base: /api/v1/bookings
 * 
 * Campaign bookings with lifecycle management
 */

// Customer routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/', [\Modules\Bookings\Controllers\Api\BookingController::class, 'index']);
    Route::get('/{id}', [\Modules\Bookings\Controllers\Api\BookingController::class, 'show']);
    Route::post('/{id}/void', [\Modules\Bookings\Controllers\Api\BookingController::class, 'voidBooking']);
    Route::get('/{id}/timeline', [\Modules\Bookings\Controllers\Api\BookingController::class, 'timeline']);
});

// Vendor routes
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    Route::get('/vendor/bookings', [\Modules\Bookings\Controllers\Api\BookingController::class, 'vendorBookings']);
    Route::post('/{id}/approve-pod', [\Modules\Bookings\Controllers\Api\BookingController::class, 'approvePOD']);
    Route::post('/{id}/reject-pod', [\Modules\Bookings\Controllers\Api\BookingController::class, 'rejectPOD']);
});

// Staff routes (Mounter)
Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    Route::post('/{id}/upload-pod', [\Modules\Bookings\Controllers\Api\BookingController::class, 'uploadPOD']);
    Route::post('/{id}/mark-mounted', [\Modules\Bookings\Controllers\Api\BookingController::class, 'markMounted']);
    Route::post('/{id}/mark-dismounted', [\Modules\Bookings\Controllers\Api\BookingController::class, 'markDismounted']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/all', [\Modules\Bookings\Controllers\Api\BookingController::class, 'adminBookings']);
    Route::post('/{id}/force-capture', [\Modules\Bookings\Controllers\Api\BookingController::class, 'forceCapture']);
});


