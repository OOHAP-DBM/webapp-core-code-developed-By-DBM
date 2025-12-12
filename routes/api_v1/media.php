<?php

use Illuminate\Support\Facades\Route;

/**
 * Media API Routes (v1)
 * Base: /api/v1/media
 * 
 * File uploads, media library (Spatie Media Library)
 * Rate limited by user role to prevent storage abuse
 */

Route::middleware(['auth:sanctum', 'throttle:uploads'])->group(function () {
    Route::post('/upload', [\Modules\Media\Controllers\Api\MediaController::class, 'upload']);
});

Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::get('/{id}', [\Modules\Media\Controllers\Api\MediaController::class, 'show']);
    Route::delete('/{id}', [\Modules\Media\Controllers\Api\MediaController::class, 'delete']);
    Route::get('/collection/{model}/{id}/{collection}', [\Modules\Media\Controllers\Api\MediaController::class, 'getCollectionMedia']);
});
