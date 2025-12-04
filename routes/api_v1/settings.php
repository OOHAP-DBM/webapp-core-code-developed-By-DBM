<?php

use Illuminate\Support\Facades\Route;

/**
 * Settings API Routes (v1)
 * Base: /api/v1/settings
 * 
 * System configuration (booking hold, commission, grace periods, etc.)
 */

// Public routes - Get public settings
Route::get('/public', [\Modules\Settings\Controllers\Api\SettingController::class, 'publicSettings']);

// Admin routes - Manage all settings
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [\Modules\Settings\Controllers\Api\SettingController::class, 'index']);
    Route::get('/{key}', [\Modules\Settings\Controllers\Api\SettingController::class, 'show']);
    Route::put('/{key}', [\Modules\Settings\Controllers\Api\SettingController::class, 'update']);
    Route::post('/bulk-update', [\Modules\Settings\Controllers\Api\SettingController::class, 'bulkUpdate']);
});

// Tenant routes - Override tenant-specific settings
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('tenant')->group(function () {
    Route::get('/{tenantId}/settings', [\Modules\Settings\Controllers\Api\SettingController::class, 'tenantSettings']);
    Route::put('/{tenantId}/settings/{key}', [\Modules\Settings\Controllers\Api\SettingController::class, 'updateTenantSetting']);
});
