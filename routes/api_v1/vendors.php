<?php

use Illuminate\Support\Facades\Route;

/**
 * Vendor API Routes (v1)
 * Base: /api/v1/vendors
 * 
 * Vendor profile, onboarding, KYC, dashboard
 */

// Public routes
Route::get('/{id}/profile', [\Modules\Vendor\Controllers\Api\VendorController::class, 'publicProfile']);

// Vendor routes
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    Route::get('/dashboard', [\Modules\Vendor\Controllers\Api\VendorController::class, 'dashboard']);
    Route::get('/profile', [\Modules\Vendor\Controllers\Api\VendorController::class, 'profile']);
    Route::put('/profile', [\Modules\Vendor\Controllers\Api\VendorController::class, 'updateProfile']);
    Route::get('/earnings', [\Modules\Vendor\Controllers\Api\VendorController::class, 'earnings']);
    Route::get('/analytics', [\Modules\Vendor\Controllers\Api\VendorController::class, 'analytics']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [\Modules\Vendor\Controllers\Api\VendorController::class, 'index']);
    Route::post('/{id}/approve', [\Modules\Vendor\Controllers\Api\VendorController::class, 'approve']);
    Route::post('/{id}/suspend', [\Modules\Vendor\Controllers\Api\VendorController::class, 'suspend']);
});
