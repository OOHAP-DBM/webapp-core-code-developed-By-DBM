<?php

use Illuminate\Support\Facades\Route;
use Modules\Import\Http\Controllers\ImportController;
use Modules\Import\Http\Controllers\ImportApprovalController;

/**
 * Import Module API Routes
 *
 * Prefix: /api
 * Middleware: web, auth:sanctum (supports both session and token auth)
 */

Route::middleware(['web', 'auth:sanctum'])->group(function () {
    /**
     * Inventory Import endpoints
     */
    Route::prefix('import')->name('import.')->group(function () {
        // List user's imports
        Route::get('/', [ImportController::class, 'listImports'])
            ->name('list');

        // Upload inventory import (Excel + PowerPoint)
        Route::post('/upload', [ImportController::class, 'uploadInventoryImport'])
            ->name('upload');

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
        Route::delete('/{batch}', [ImportController::class, 'cancelImport'])
            ->name('cancel');
    });
});
