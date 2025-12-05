<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookingHoldController;
use App\Http\Controllers\Admin\FinanceController;

/**
 * Admin API Routes (v1)
 * Base: /api/v1/admin
 * 
 * Admin dashboard, reports, system management
 */

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/dashboard', [\Modules\Admin\Controllers\Api\AdminController::class, 'dashboard']);
    Route::get('/stats', [\Modules\Admin\Controllers\Api\AdminController::class, 'stats']);
    Route::get('/revenue', [\Modules\Admin\Controllers\Api\AdminController::class, 'revenueReport']);
    Route::get('/users', [\Modules\Admin\Controllers\Api\AdminController::class, 'users']);
    Route::post('/users/{id}/toggle-status', [\Modules\Admin\Controllers\Api\AdminController::class, 'toggleUserStatus']);
    Route::get('/activity-log', [\Modules\Admin\Controllers\Api\AdminController::class, 'activityLog']);
    
    // Payment holds management
    Route::post('/bookings/{id}/manual-capture', [BookingHoldController::class, 'manualCapture']);
    Route::post('/bookings/run-capture-job', [BookingHoldController::class, 'runCaptureJob']);
    
    // Financial management - Commission & Payouts
    Route::get('/booking-payments/{id}', [FinanceController::class, 'getPaymentDetails']);
    Route::post('/booking-payments/{id}/mark-paid', [FinanceController::class, 'markPayoutPaid']);
    Route::post('/booking-payments/{id}/hold', [FinanceController::class, 'holdPayout']);
    Route::get('/commission-stats', [FinanceController::class, 'getCommissionStats']);
    Route::get('/pending-payouts', [FinanceController::class, 'getPendingPayouts']);
    Route::get('/vendors/{vendorId}/payout-summary', [FinanceController::class, 'getVendorPayoutSummary']);
});
