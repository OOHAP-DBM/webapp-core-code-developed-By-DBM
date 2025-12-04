<?php

use Illuminate\Support\Facades\Route;

/**
 * Settings API Routes (v1)
 * Base: /api/v1/settings
 * 
 * System configuration (booking hold, commission, grace periods, etc.)
 */

// Admin routes - Manage all settings
Route::middleware(['auth:sanctum', 'role:super_admin|admin'])->group(function () {
    Route::get('/', [\Modules\Settings\Controllers\Api\SettingsController::class, 'index']);
    Route::get('/{key}', [\Modules\Settings\Controllers\Api\SettingsController::class, 'show']);
    Route::put('/', [\Modules\Settings\Controllers\Api\SettingsController::class, 'update']);
    Route::put('/{key}', [\Modules\Settings\Controllers\Api\SettingsController::class, 'updateSingle']);
    Route::post('/clear-cache', [\Modules\Settings\Controllers\Api\SettingsController::class, 'clearCache']);
});

