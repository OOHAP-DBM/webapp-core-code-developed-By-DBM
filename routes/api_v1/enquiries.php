<?php

use Illuminate\Support\Facades\Route;

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
    
    // Create enquiry (customer only)
    Route::post('/', [\Modules\Enquiries\Controllers\Api\EnquiryController::class, 'store'])
        ->middleware('role:customer');
    
    // View single enquiry
    Route::get('/{id}', [\Modules\Enquiries\Controllers\Api\EnquiryController::class, 'show']);
    
    // Update enquiry status (vendor/admin only)
    Route::patch('/{id}/status', [\Modules\Enquiries\Controllers\Api\EnquiryController::class, 'updateStatus'])
        ->middleware('role:vendor,admin');
});
