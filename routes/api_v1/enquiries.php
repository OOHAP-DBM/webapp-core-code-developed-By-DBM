<?php

use Illuminate\Support\Facades\Route;

/**
 * Enquiry API Routes (v1)
 * Base: /api/v1/enquiries
 * 
 * Customer enquiries for hoardings/DOOH
 */

// Customer routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::post('/', [\Modules\Enquiry\Controllers\Api\EnquiryController::class, 'store']);
    Route::get('/', [\Modules\Enquiry\Controllers\Api\EnquiryController::class, 'index']);
    Route::get('/{id}', [\Modules\Enquiry\Controllers\Api\EnquiryController::class, 'show']);
    Route::post('/{id}/cancel', [\Modules\Enquiry\Controllers\Api\EnquiryController::class, 'cancel']);
});

// Vendor routes
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    Route::get('/vendor/enquiries', [\Modules\Enquiry\Controllers\Api\EnquiryController::class, 'vendorEnquiries']);
    Route::post('/{id}/respond', [\Modules\Enquiry\Controllers\Api\EnquiryController::class, 'respond']);
});
