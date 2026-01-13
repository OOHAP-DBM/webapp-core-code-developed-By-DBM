<?php

use Illuminate\Support\Facades\Route;
use Modules\Hoardings\Http\Controllers\Api\Vendor\OOHListingController;
use Modules\Hoardings\Http\Controllers\Api\HoardingAttributeController;
/**
 * Hoardings API Routes (v1)
 * Base: /api/v1/hoardings
 * 
 * OOH hoarding catalog, search, filters, availability
 */

Route::get('/categories', [Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'getCategories']);


// Public routes - Browse hoardings
Route::get('/', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'index']);
Route::get('/map-pins', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'mapPins']);
Route::get('/{id}', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'show']);
Route::get('/search', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'search']);
Route::get('/availability/{id}', [\Modules\Hoardings\Controllers\Api\HoardingController::class, 'checkAvailability']);

// Vendor routes - Manage hoardings
Route::middleware(['auth:sanctum', 'role:vendor', 'vendor.approved'])->prefix('vendor')->group(function () {
    Route::post('/', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'store']);
    Route::put('/{id}', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'update']);
    Route::delete('/{id}', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'destroy']);
    Route::post('/{id}/media', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'uploadMedia']);

    // Vendor-specific: Get all hoardings for authenticated vendor
    Route::get('/hoardings', [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'index']);
    Route::get('/all/{id}', [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'showALlHoarding']);  
    Route::post('ooh/step-1', [OOHListingController::class, 'storeStep1']);
    Route::post('ooh/step-2/{ooh_id}', [OOHListingController::class, 'storeStep2']);
    Route::post('ooh/step-3/{ooh_id}', [OOHListingController::class, 'storeStep3']);
    Route::get('/draft', [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'getDrafts']);
    Route::get('/{id}', [OOHListingController::class, 'show']);

});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/{id}/approve', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'approve']);
    Route::post('/{id}/reject', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'reject']);
});