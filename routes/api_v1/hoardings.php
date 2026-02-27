<?php

use Illuminate\Support\Facades\Route;
use Modules\Hoardings\Http\Controllers\Api\Vendor\OOHListingController;
use Modules\Hoardings\Http\Controllers\Api\HoardingAttributeController;
use Modules\Hoardings\Http\Controllers\Api\HoardingController;
use Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController as VendorHoardingController;
use Modules\Hoardings\Http\Controllers\Api\Vendor\OOHUpdateController;
/**
 * Hoardings API Routes (v1)
 * Base: /api/v1/hoardings
 * 
 * OOH hoarding catalog, search, filters, availability
 */

Route::get('/categories', [Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'getCategories']);
Route::get('/live-categories', [HoardingController::class, 'getLiveCategories']);
Route::get('/by-type', [HoardingController::class, 'activeOOHAndDOOH']);

// Public routes - Browse hoardings
Route::get('/', [HoardingController::class, 'index']);
Route::get('/cities', [HoardingController::class, 'getCitiesWithActiveHoardings']);
Route::get('/map-pins', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'mapPins']);
Route::get('/{id}', [HoardingController::class, 'show']);
Route::get('/search', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'search']);
Route::get('/availability/{id}', [\Modules\Http\Hoardings\Controllers\Api\HoardingController::class, 'checkAvailability']);

// Vendor routes - Manage hoardings
Route::middleware(['auth:sanctum', 'role:vendor', 'vendor.approved'])->prefix('vendor')->group(function () {
    Route::post('/', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'store']);
    Route::put('/{id}', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'update']);
    Route::delete('/{id}', [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'destroy']);
    Route::post('/{id}/media', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'uploadMedia']);

    // Vendor-specific: Get all hoardings for authenticated vendor
    Route::get('/hoardings', [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'index']);
    Route::get('/all/{id}', [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'showALlHoarding']);  
    Route::post('ooh/step-1', [OOHListingController::class, 'storeStep1']);
    Route::post('ooh/step-2/{ooh_id}', [OOHListingController::class, 'storeStep2']);
    Route::post('ooh/step-3/{ooh_id}', [OOHListingController::class, 'storeStep3']);
    Route::get('/draft', [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'getDrafts']);
    Route::get('/{id}', [OOHListingController::class, 'show']);

    Route::post('/{id}/activate',  [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class,'activate']);
    Route::post('/{id}/deactivate', [\Modules\Hoardings\Http\Controllers\Api\Vendor\HoardingController::class, 'deactivate']);


    Route::get('/show/{id}', [OOHUpdateController::class, 'show']);
    // Step 1: Basic info + media
    // multipart/form-data — supports file uploads via media[]
    Route::post('/edit/{id}/step1', [OOHUpdateController::class, 'updateStep1']);
    // Step 2: Visibility / legal / brand logos
    // multipart/form-data — supports file uploads via brand_logos[]
    Route::post('/edit/{id}/step2', [OOHUpdateController::class, 'updateStep2']);
    // Step 3: Pricing / add-on charges / packages
    // application/json or form-data
    Route::post('/edit/{id}/step3', [OOHUpdateController::class, 'updateStep3']);
    // Delete individual media item
    Route::delete('/{id}/media/{mediaId}', [OOHUpdateController::class, 'deleteMedia']);
    // Delete individual brand logo
    Route::delete('/{id}/brand-logos/{logoId}', [OOHUpdateController::class, 'deleteBrandLogo']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/{id}/approve', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'approve']);
    Route::post('/{id}/reject', [\Modules\Hoardings\Http\Controllers\Api\HoardingController::class, 'reject']);
});