<?php

use Illuminate\Support\Facades\Route;
use Modules\Quotations\Controllers\Api\QuotationController;

/**
 * Quotation API Routes (v1)
 * Base: /api/v1
 * 
 * Versioned quotations from offers with immutable snapshots on approval
 */

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Vendor routes - Create and manage quotations
    Route::middleware(['role:vendor'])->group(function () {
        Route::post('/offers/{offerId}/quotations', [QuotationController::class, 'createForOffer']);
        Route::post('/offers/{offerId}/quotations/auto', [QuotationController::class, 'createFromOffer']);
        Route::patch('/quotations/{id}/send', [QuotationController::class, 'send']);
        Route::post('/quotations/{id}/revise', [QuotationController::class, 'revise']);
    });

    // Customer routes - Approve and reject quotations
    Route::middleware(['role:customer'])->group(function () {
        Route::patch('/quotations/{id}/approve', [QuotationController::class, 'approve']);
        Route::patch('/quotations/{id}/reject', [QuotationController::class, 'reject']);
    });

    // Shared routes - View quotations
    Route::middleware(['role:vendor,customer,admin'])->group(function () {
        Route::get('/quotations', [QuotationController::class, 'index']);
        Route::get('/quotations/{id}', [QuotationController::class, 'show']);
        Route::get('/offers/{offerId}/quotations', [QuotationController::class, 'getByOffer']);
    });
});
