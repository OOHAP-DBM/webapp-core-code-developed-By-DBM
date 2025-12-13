<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookingHoldController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Api\Admin\AdminKYCController;
use App\Http\Controllers\Api\Admin\BookingRulesController;

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
    Route::post('/booking-payments/{id}/process-manual-payout', [FinanceController::class, 'processManualPayout']);
    Route::get('/commission-stats', [FinanceController::class, 'getCommissionStats']);
    Route::get('/pending-payouts', [FinanceController::class, 'getPendingPayouts']);
    Route::get('/vendors/{vendorId}/payout-summary', [FinanceController::class, 'getVendorPayoutSummary']);
    
    // KYC Verification Management
    Route::get('/kyc', [AdminKYCController::class, 'index']);
    Route::get('/kyc/stats', [AdminKYCController::class, 'stats']);
    Route::get('/kyc/{id}', [AdminKYCController::class, 'show']);
    Route::post('/kyc/{id}/approve', [AdminKYCController::class, 'approve']);
    Route::post('/kyc/{id}/reject', [AdminKYCController::class, 'reject']);
    Route::post('/kyc/{id}/request-resubmission', [AdminKYCController::class, 'requestResubmission']);
    
    // KYC Manual Override & Razorpay Management
    Route::post('/kyc/{id}/sync-razorpay', [AdminKYCController::class, 'syncRazorpayStatus']);
    Route::post('/kyc/{id}/manual-override', [AdminKYCController::class, 'manualOverride']);
    Route::post('/kyc/{id}/retry-razorpay', [AdminKYCController::class, 'retryRazorpay']);
    
    // Booking Rules Configuration
    Route::get('/booking-rules', [BookingRulesController::class, 'index']);
    Route::put('/booking-rules', [BookingRulesController::class, 'update']);
    
    // PROMPT 100: Admin Override System
    Route::prefix('overrides')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'index']);
        Route::get('/{override}', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'show']);
        Route::get('/history', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'history']);
        
        // Override specific entities
        Route::post('/booking/{booking}', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'overrideBooking']);
        Route::post('/payment/{payment}', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'overridePayment']);
        Route::post('/offer/{offer}', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'overrideOffer']);
        Route::post('/quote/{quote}', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'overrideQuote']);
        Route::post('/commission/{commission}', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'overrideCommission']);
        
        // Revert override (super admin only)
        Route::post('/{override}/revert', [\App\Http\Controllers\Admin\AdminOverrideController::class, 'revert']);
    });
});
