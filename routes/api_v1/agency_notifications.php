<?php

use Illuminate\Support\Facades\Route;

/**
 * Agency Notifications API Routes (v1)
 * Base: /api/v1/agency-notifications
 */

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [\Modules\Notifications\Controllers\Api\AgencyNotificationController::class, 'index']);
    Route::get('/unread-count', [\Modules\Notifications\Controllers\Api\AgencyNotificationController::class, 'unreadCount']);
    Route::post('/{id}/mark-read', [\Modules\Notifications\Controllers\Api\AgencyNotificationController::class, 'markAsRead']);
    Route::post('/mark-all-read', [\Modules\Notifications\Controllers\Api\AgencyNotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}', [\Modules\Notifications\Controllers\Api\AgencyNotificationController::class, 'delete']);
});
