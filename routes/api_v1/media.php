<?php

use Illuminate\Support\Facades\Route;

/**
 * Media API Routes (v1)
 * Base: /api/v1/media
 * 
 * File uploads, media library (Spatie Media Library)
 */

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload', [\Modules\Media\Controllers\Api\MediaController::class, 'upload']);
    Route::get('/{id}', [\Modules\Media\Controllers\Api\MediaController::class, 'show']);
    Route::delete('/{id}', [\Modules\Media\Controllers\Api\MediaController::class, 'delete']);
    Route::get('/collection/{model}/{id}/{collection}', [\Modules\Media\Controllers\Api\MediaController::class, 'getCollectionMedia']);
});
