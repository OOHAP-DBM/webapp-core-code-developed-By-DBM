<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\Api\PaymentController;
// use Modules\Payment\Controllers\Api\PaymentWebhookController;
use Modules\Payment\Http\Controllers\Api\PaymentWebhookController;


/**
 * Payment API Routes (v1)
 * Base: /api/v1/payments
 * 
 * Razorpay integration, holds, captures, voids, payouts
 * Critical rate limits to prevent payment abuse
 */

// Customer routes with critical rate limiting
Route::middleware(['auth:sanctum', 'role:customer', 'throttle:critical'])->group(function () {
    Route::post('/create-order', [PaymentController::class, 'createOrder']);
    Route::post('/verify', [PaymentController::class, 'verifyPayment']);
    Route::get('/history', [PaymentController::class, 'paymentHistory']);
});

// Webhooks (no auth - verified by signature) with high rate limit
Route::middleware(['throttle:webhooks'])->group(function () {
    Route::post('/webhook/razorpay', [PaymentWebhookController::class, 'handle']);
});

// Admin routes with authenticated rate limiting
Route::middleware(['auth:sanctum', 'role:admin', 'throttle:authenticated'])->group(function () {
    Route::get('/admin/transactions', [PaymentController::class, 'allTransactions']);
    Route::post('/{id}/refund', [PaymentController::class, 'refund']);
    Route::post('/process-payouts', [PaymentController::class, 'processVendorPayouts']);
});
