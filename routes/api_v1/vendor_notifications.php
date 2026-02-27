<?php

use Illuminate\Support\Facades\Route;

/**
 * Vendor Notifications API Routes (v1)
 * Base: /api/v1/vendor-notifications
 */

Route::middleware('auth:sanctum', 'role:vendor')->group(function () {
    Route::get('/', [\Modules\Notifications\Controllers\Api\VendorNotificationController::class, 'index']);
    Route::get('/unread-count', [\Modules\Notifications\Controllers\Api\VendorNotificationController::class, 'unreadCount']);
    Route::post('/{id}/mark-read', [\Modules\Notifications\Controllers\Api\VendorNotificationController::class, 'markAsRead']);
    Route::post('/mark-all-read', [\Modules\Notifications\Controllers\Api\VendorNotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}', [\Modules\Notifications\Controllers\Api\VendorNotificationController::class, 'delete']);
});
