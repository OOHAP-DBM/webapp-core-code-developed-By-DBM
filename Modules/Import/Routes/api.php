<?php

use Illuminate\Support\Facades\Route;
use Modules\Import\Http\Controllers\ImportController;
use Modules\Import\Http\Controllers\ImportApprovalController;

/**
 * Import Module API Routes
 *
 * Prefix: /api
 * Middleware: api, auth:sanctum
 */

Route::middleware(['auth:sanctum'])->group(function () {
    /**
     * Inventory Import endpoints
     */
    Route::prefix('import')->name('import.')->group(function () {
        // Upload inventory import (Excel + PowerPoint)
        Route::post('/upload', [ImportController::class, 'uploadInventoryImport'])
            ->name('upload');

        // List user's imports
        Route::get('/list', [ImportController::class, 'listImports'])
            ->name('list');

        // Get import status
        Route::get('/{batch}/status', [ImportController::class, 'getImportStatus'])
            ->name('status');

        // Get import details with invalid records
        Route::get('/{batch}/details', [ImportController::class, 'getImportDetails'])
            ->name('details');

        // Approve batch and create hoardings
        Route::post('/{batch}/approve', [ImportApprovalController::class, 'approve'])
            ->name('approve');

        // Cancel import
        Route::post('/{batch}/cancel', [ImportController::class, 'cancelImport'])
            ->name('cancel');
    });
});
