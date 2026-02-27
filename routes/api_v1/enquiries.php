<?php

use Illuminate\Support\Facades\Route;
use Modules\Enquiries\Controllers\Api\DirectEnquiryApiController;
/**
 * Enquiries API Routes (v1)
 * Base: /api/v1/enquiries
 * 
 * Customer enquiry submission and management
 */

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // List enquiries (customer sees theirs, vendor sees their hoarding's, admin sees all)
    Route::get('/', [\Modules\Enquiries\Controllers\Api\EnquiryController::class, 'index']);
    
    // Create enquiry (customer and vendor)
    Route::post('/', [\Modules\Enquiries\Controllers\Api\EnquiryController::class, 'store']);
    
    // View single enquiry
    Route::get('/{id}', [\Modules\Enquiries\Controllers\Api\EnquiryController::class, 'show']);
    
    // Update enquiry status (vendor/admin only)
    Route::patch('/{id}/status', [\Modules\Enquiries\Controllers\Api\EnquiryController::class, 'updateStatus'])
        ->middleware('role:vendor,admin');

    Route::get('vendor/all', [\Modules\Enquiries\Controllers\Api\VendorEnquiryController::class, 'index'])
        ->middleware('role:vendor');
        Route::get('vendor/{id}', [\Modules\Enquiries\Controllers\Api\VendorEnquiryController::class, 'show'])
            ->where('id', '[0-9]+');
});


Route::prefix('direct')->group(function () {

    // Step 1: Send OTP to phone
    // POST { phone: "9876543210" }
        Route::post('otp-send', [DirectEnquiryApiController::class, 'sendOtp']);

    // Step 2: Verify OTP
    // POST { phone: "9876543210", otp: "1234" }
        Route::post('otp-verify', [DirectEnquiryApiController::class, 'verifyOtp']);

    // Step 3: Submit enquiry (phone_verified must be true + OTP verified in DB)
    // POST multipart/form-data or application/json
    Route::post('/', [DirectEnquiryApiController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'role:vendor'])
    ->prefix('/vendor/direct-enquiries')
    ->group(function () {

    // GET  /api/v1/vendor/enquiries?status=pending&viewed=false&per_page=15
    Route::get('/',                [DirectEnquiryApiController::class, 'vendorIndex']);

    // GET  /api/v1/vendor/enquiries/statistics
    Route::get('statistics',       [DirectEnquiryApiController::class, 'statistics']);

    // GET  /api/v1/vendor/enquiries/{id}
    Route::get('{id}',             [DirectEnquiryApiController::class, 'vendorShow']);

    // POST /api/v1/vendor/enquiries/{id}/respond
    // Body: { response_status, vendor_notes?, quoted_price? }
    Route::post('{id}/respond',    [DirectEnquiryApiController::class, 'respond']);

    // PATCH /api/v1/vendor/enquiries/{id}/notes
    // Body: { notes: "..." }
    Route::patch('{id}/notes',     [DirectEnquiryApiController::class, 'updateNotes']);

    // PATCH /api/v1/vendor/enquiries/{id}/mark-viewed
    Route::patch('{id}/mark-viewed', [DirectEnquiryApiController::class, 'markViewed']);
});
