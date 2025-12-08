<?php

use Illuminate\Support\Facades\Route;

/**
 * Reports API Routes (v1)
 * Base: /api/v1/reports
 * 
 * Analytics, exports, business intelligence
 */

// Vendor reports
Route::middleware(['auth:sanctum', 'role:vendor'])->prefix('vendor')->group(function () {
    // Route::get('/bookings', [\Modules\Reports\Controllers\Api\ReportController::class, 'vendorBookingReport']);
    // Route::get('/revenue', [\Modules\Reports\Controllers\Api\ReportController::class, 'vendorRevenueReport']);
    // Route::get('/hoardings-performance', [\Modules\Reports\Controllers\Api\ReportController::class, 'hoardingPerformance']);
});

// Customer reports
Route::middleware(['auth:sanctum', 'role:customer'])->prefix('customer')->group(function () {
    // Route::get('/campaigns', [\Modules\Reports\Controllers\Api\ReportController::class, 'customerCampaignReport']);
    // Route::get('/spend-analysis', [\Modules\Reports\Controllers\Api\ReportController::class, 'spendAnalysis']);
});

// Admin reports
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Route::get('/overview', [\Modules\Reports\Controllers\Api\ReportController::class, 'adminOverview']);
    // Route::get('/revenue-breakdown', [\Modules\Reports\Controllers\Api\ReportController::class, 'revenueBreakdown']);
    // Route::get('/vendor-performance', [\Modules\Reports\Controllers\Api\ReportController::class, 'vendorPerformanceReport']);
    // Route::post('/export', [\Modules\Reports\Controllers\Api\ReportController::class, 'exportReport']);
});

