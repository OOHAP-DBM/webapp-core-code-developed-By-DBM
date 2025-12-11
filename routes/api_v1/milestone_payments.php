<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MilestonePaymentController;

/**
 * Milestone Payment API Routes (v1)
 * Base: /api/v1
 * 
 * Milestone payment system for quotation-based bookings.
 * Does NOT interfere with existing full payment flow.
 */

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Customer routes - View and pay milestones
    Route::middleware(['role:customer'])->group(function () {
        // Get all milestones for a booking
        Route::get('/bookings/{bookingId}/milestones', [MilestonePaymentController::class, 'index']);
        
        // Get milestone invoices summary
        Route::get('/bookings/{bookingId}/milestone-invoices', [MilestonePaymentController::class, 'getInvoicesSummary']);
        
        // Get milestone details
        Route::get('/milestones/{milestoneId}', [MilestonePaymentController::class, 'show']);
        
        // Create payment order for milestone
        Route::post('/milestones/{milestoneId}/create-payment', [MilestonePaymentController::class, 'createPayment']);
        
        // Payment callback (after Razorpay payment)
        Route::post('/milestones/{milestoneId}/payment-callback', [MilestonePaymentController::class, 'paymentCallback']);
    });

    // Vendor routes - View milestone status
    Route::middleware(['role:vendor'])->group(function () {
        Route::get('/vendor/bookings/{bookingId}/milestones', [MilestonePaymentController::class, 'index']);
        Route::get('/vendor/bookings/{bookingId}/milestone-invoices', [MilestonePaymentController::class, 'getInvoicesSummary']);
    });

    // Admin routes - Full access
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/bookings/{bookingId}/milestones', [MilestonePaymentController::class, 'index']);
        Route::get('/admin/milestones/{milestoneId}', [MilestonePaymentController::class, 'show']);
        Route::get('/admin/bookings/{bookingId}/milestone-invoices', [MilestonePaymentController::class, 'getInvoicesSummary']);
    });
});
