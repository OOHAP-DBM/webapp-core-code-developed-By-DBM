<?php

use Illuminate\Support\Facades\Route;

/**
 * Offer API Routes (v1)
 * Base: /api/v1/offers
 * 
 * Vendor offers in response to enquiries
 */

// Vendor routes
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    Route::post('/', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'store']);
    Route::get('/', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'index']);
    Route::get('/{id}', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'show']);
    Route::put('/{id}', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'update']);
    Route::post('/{id}/withdraw', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'withdraw']);
});

// Customer routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/enquiry/{enquiryId}/offers', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'getOffersByEnquiry']);
});
