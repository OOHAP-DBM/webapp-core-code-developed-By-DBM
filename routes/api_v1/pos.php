<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Controllers\Api\POSBookingController;

/**
 * POS Booking API Routes (v1)
 * Base: /api/v1/vendor/pos
 * 
 * Vendor POS booking management
 */

Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    // Dashboard & Statistics
    Route::get('/dashboard', [POSBookingController::class, 'dashboard']);
    
    // Hoarding Search
    Route::get('/search-hoardings', [POSBookingController::class, 'searchHoardings']);
    Route::get('/hoardings', [POSBookingController::class, 'getHoardings']);
    
    // Price Calculator
    Route::post('/calculate-price', [POSBookingController::class, 'calculatePrice']);
    
    // POS Bookings CRUD
    Route::get('/bookings', [POSBookingController::class, 'index']);
    Route::post('/bookings', [POSBookingController::class, 'store']);
    Route::get('/bookings/{id}', [POSBookingController::class, 'show']);
    Route::put('/bookings/{id}', [POSBookingController::class, 'update']);

 

    // ============================================
    // CRITICAL: Payment Status Management
    // ============================================
    
    /**
     * Mark booking payment as received (PENDING → PAID)
     * POST /api/v1/vendor/pos/bookings/{id}/mark-paid
     * Body: { amount, payment_date?, notes? }
     */
    // Route::post('/bookings/{id}/mark-paid', [POSBookingController::class, 'markAsPaid']);
    
    /**
     * Release booking hold (free hoarding, cancel order)
     * POST /api/v1/vendor/pos/bookings/{id}/release
     * Body: { reason }
     */
    // Route::post('/bookings/{id}/release', [POSBookingController::class, 'releaseBooking']);
    
    /**
     * Get all bookings with pending payments (for dashboard)
     * GET /api/v1/vendor/pos/pending-payments
     */
    // Route::get('/pending-payments', [POSBookingController::class, 'getPendingPayments']);
    
    /**
     * Send payment reminder for pending booking
     * POST /api/v1/vendor/pos/bookings/{id}/send-reminder
     */
    // Route::post('/bookings/{id}/send-reminder', [POSBookingController::class, 'sendReminder']);
    
    // ============================================
    // Legacy Payment Actions (kept for compatibility)
    // ============================================
    
    Route::post('/bookings/{id}/mark-cash-collected', [POSBookingController::class, 'markCashCollected']);
    Route::post('/bookings/{id}/convert-to-credit-note', [POSBookingController::class, 'convertToCreditNote']);
    Route::post('/bookings/{id}/cancel-credit-note', [POSBookingController::class, 'cancelCreditNote']);
    
    // Booking Actions
    Route::post('/bookings/{id}/cancel', [POSBookingController::class, 'cancel']);
});
