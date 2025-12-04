<?php

use Illuminate\Support\Facades\Route;

/**
 * Payment API Routes (v1)
 * Base: /api/v1/payments
 * 
 * Razorpay integration, holds, captures, voids, payouts
 */

// Customer routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::post('/create-order', [\Modules\Payment\Controllers\Api\PaymentController::class, 'createOrder']);
    Route::post('/verify', [\Modules\Payment\Controllers\Api\PaymentController::class, 'verifyPayment']);
    Route::get('/history', [\Modules\Payment\Controllers\Api\PaymentController::class, 'paymentHistory']);
});

// Webhooks (no auth - verified by signature)
Route::post('/webhook/razorpay', [\Modules\Payment\Controllers\Api\PaymentWebhookController::class, 'handle']);

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/transactions', [\Modules\Payment\Controllers\Api\PaymentController::class, 'allTransactions']);
    Route::post('/{id}/refund', [\Modules\Payment\Controllers\Api\PaymentController::class, 'refund']);
    Route::post('/process-payouts', [\Modules\Payment\Controllers\Api\PaymentController::class, 'processVendorPayouts']);
});
