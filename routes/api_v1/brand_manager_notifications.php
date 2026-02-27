<?php

use Illuminate\Support\Facades\Route;

/**
 * Brand Manager Notifications API Routes (v1)
 * Base: /api/v1/brand-manager-notifications
 */

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [\Modules\Notifications\Controllers\Api\BrandManagerNotificationController::class, 'index']);
    Route::get('/unread-count', [\Modules\Notifications\Controllers\Api\BrandManagerNotificationController::class, 'unreadCount']);
    Route::post('/{id}/mark-read', [\Modules\Notifications\Controllers\Api\BrandManagerNotificationController::class, 'markAsRead']);
    Route::post('/mark-all-read', [\Modules\Notifications\Controllers\Api\BrandManagerNotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}', [\Modules\Notifications\Controllers\Api\BrandManagerNotificationController::class, 'delete']);
});
