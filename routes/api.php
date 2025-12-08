<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Payment\Controllers\Api\RazorpayWebhookController;

/**
 * OOHAPP API v1 Routes
 * 
 * All API v1 endpoints are prefixed with /api/v1
 * Module-specific routes are loaded from routes/api_v1/ directory
 */

// Razorpay Webhook (No auth middleware - verified via signature)
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle']);

Route::prefix('v1')->group(function () {
    
    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0'
        ]);
    });

    // Load module-specific API routes
    Route::prefix('auth')->group(base_path('routes/api_v1/auth.php'));
    Route::prefix('hoardings')->group(base_path('routes/api_v1/hoardings.php'));
    // Route::prefix('dooh')->group(base_path('routes/api_v1/dooh.php')); // Coming soon
    Route::prefix('enquiries')->group(base_path('routes/api_v1/enquiries.php'));
    Route::prefix('offers')->group(base_path('routes/api_v1/offers.php'));
    Route::prefix('quotations')->group(base_path('routes/api_v1/quotations.php'));
    Route::prefix('bookings')->group(base_path('routes/api_v1/bookings.php'));
    Route::prefix('bookings-v2')->group(base_path('routes/api_v1/bookings_v2.php')); // New Bookings Module
    Route::prefix('payments')->group(base_path('routes/api_v1/payments.php'));
    Route::prefix('vendors')->group(base_path('routes/api_v1/vendors.php'));
    Route::prefix('kyc')->group(base_path('routes/api_v1/kyc.php'));
    Route::prefix('staff')->group(base_path('routes/api_v1/staff.php'));
    Route::prefix('admin')->group(base_path('routes/api_v1/admin.php'));
    Route::prefix('settings')->group(base_path('routes/api_v1/settings.php'));
    Route::prefix('notifications')->group(base_path('routes/api_v1/notifications.php'));
    Route::prefix('reports')->group(base_path('routes/api_v1/reports.php'));
    Route::prefix('media')->group(base_path('routes/api_v1/media.php'));
    Route::prefix('search')->group(base_path('routes/api_v1/search.php'));
});
