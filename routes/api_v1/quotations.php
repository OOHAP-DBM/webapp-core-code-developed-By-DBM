<?php

use Illuminate\Support\Facades\Route;

/**
 * Quotation API Routes (v1)
 * Base: /api/v1/quotations
 * 
 * Finalized quotations (snapshot pricing)
 */

// Customer routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/', [\Modules\Quotation\Controllers\Api\QuotationController::class, 'index']);
    Route::get('/{id}', [\Modules\Quotation\Controllers\Api\QuotationController::class, 'show']);
    Route::post('/{id}/accept', [\Modules\Quotation\Controllers\Api\QuotationController::class, 'accept']);
    Route::post('/{id}/reject', [\Modules\Quotation\Controllers\Api\QuotationController::class, 'reject']);
    Route::get('/{id}/download', [\Modules\Quotation\Controllers\Api\QuotationController::class, 'downloadPDF']);
});

// Vendor routes
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    Route::post('/', [\Modules\Quotation\Controllers\Api\QuotationController::class, 'store']);
    Route::get('/vendor/quotations', [\Modules\Quotation\Controllers\Api\QuotationController::class, 'vendorQuotations']);
});
