<?php

use Illuminate\Support\Facades\Route;

/**
 * KYC API Routes (v1)
 * Base: /api/v1/kyc
 * 
 * KYC verification for vendors (documents, status)
 */

// Vendor routes
// TODO: KYCController not implemented yet - using VendorKYCController
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    // Route::get('/status', [\Modules\KYC\Controllers\Api\KYCController::class, 'status']);
    // Route::post('/submit', [\Modules\KYC\Controllers\Api\KYCController::class, 'submit']);
    // Route::post('/upload-document', [\Modules\KYC\Controllers\Api\KYCController::class, 'uploadDocument']);
});

// Admin routes
// TODO: KYCController not implemented yet - using AdminKYCController
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Route::get('/pending', [\Modules\KYC\Controllers\Api\KYCController::class, 'pendingApplications']);
    // Route::get('/{id}', [\Modules\KYC\Controllers\Api\KYCController::class, 'show']);
    // Route::post('/{id}/approve', [\Modules\KYC\Controllers\Api\KYCController::class, 'approve']);
    // Route::post('/{id}/reject', [\Modules\KYC\Controllers\Api\KYCController::class, 'reject']);
    // Route::post('/{id}/request-changes', [\Modules\KYC\Controllers\Api\KYCController::class, 'requestChanges']);
});
