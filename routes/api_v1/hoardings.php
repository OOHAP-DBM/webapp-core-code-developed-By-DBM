<?php

use Illuminate\Support\Facades\Route;

/**
 * Hoardings API Routes (v1)
 * Base: /api/v1/hoardings
 * 
 * OOH hoarding catalog, search, filters, availability
 */

// Public routes - Browse hoardings
Route::get('/', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'index']);
Route::get('/map-pins', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'mapPins']);
Route::get('/{id}', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'show']);
Route::get('/search', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'search']);
Route::get('/availability/{id}', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'checkAvailability']);

// Vendor routes - Manage hoardings
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    Route::post('/', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'store']);
    Route::put('/{id}', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'update']);
    Route::delete('/{id}', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'destroy']);
    Route::post('/{id}/media', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'uploadMedia']);

    // Vendor-specific: Get all hoardings for authenticated vendor
    Route::get('/vendor/hoardings', [\Modules\Hoardings\Controllers\Api\Vendor\HoardingController::class, 'index']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/{id}/approve', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'approve']);
    Route::post('/{id}/reject', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'reject']);
});
