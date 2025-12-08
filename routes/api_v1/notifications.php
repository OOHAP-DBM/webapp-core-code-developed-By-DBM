<?php

use Illuminate\Support\Facades\Route;

/**
 * Notifications API Routes (v1)
 * Base: /api/v1/notifications
 * 
 * In-app notifications, preferences, mark as read
 */

// TODO: NotificationController not implemented yet
Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/', [\Modules\Notifications\Controllers\Api\NotificationController::class, 'index']);
    // Route::get('/unread-count', [\Modules\Notifications\Controllers\Api\NotificationController::class, 'unreadCount']);
    // Route::post('/{id}/mark-read', [\Modules\Notifications\Controllers\Api\NotificationController::class, 'markAsRead']);
    // Route::post('/mark-all-read', [\Modules\Notifications\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    // Route::delete('/{id}', [\Modules\Notifications\Controllers\Api\NotificationController::class, 'delete']);
    // Route::get('/preferences', [\Modules\Notifications\Controllers\Api\NotificationController::class, 'preferences']);
    // Route::put('/preferences', [\Modules\Notifications\Controllers\Api\NotificationController::class, 'updatePreferences']);
});
