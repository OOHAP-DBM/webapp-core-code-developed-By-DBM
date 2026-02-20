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
            ->middleware('permission:import.manage|import.batch.view')
            ->name('list');

        // Upload inventory import (Excel + PowerPoint)
        Route::post('/upload', [ImportController::class, 'uploadInventoryImport'])
            ->middleware('permission:import.manage|import.batch.create')
            ->name('upload');

        // Get complete batch details with rows
        Route::get('/{batch}', [ImportController::class, 'showBatch'])
            ->middleware('permission:import.manage|import.batch.view')
            ->name('show');

        // Update batch
        Route::put('/{batch}', [ImportController::class, 'updateBatch'])
            ->middleware('permission:import.manage|import.batch.update')
            ->name('update');

        // Delete batch
        Route::delete('/{batch}/destroy', [ImportController::class, 'deleteBatch'])
            ->middleware('permission:import.manage|import.batch.delete')
            ->name('delete');

        // Get import status
        Route::get('/{batch}/status', [ImportController::class, 'getImportStatus'])
            ->middleware('permission:import.manage|import.batch.view')
            ->name('status');

        // Get import details with invalid records
        Route::get('/{batch}/details', [ImportController::class, 'getImportDetails'])
            ->middleware('permission:import.manage|import.batch.view')
            ->name('details');

        // List rows for a batch
        Route::get('/{batch}/rows', [ImportController::class, 'listBatchRows'])
            ->middleware('permission:import.manage|import.row.view')
            ->name('rows.list');

        // Create row under a batch
        Route::post('/{batch}/rows', [ImportController::class, 'createBatchRow'])
            ->middleware('permission:import.manage|import.row.create')
            ->name('rows.create');

        // Update row under a batch
        Route::put('/{batch}/rows/{row}', [ImportController::class, 'updateBatchRow'])
            ->middleware('permission:import.manage|import.row.update')
            ->name('rows.update');

        // Delete row under a batch
        Route::delete('/{batch}/rows/{row}', [ImportController::class, 'deleteBatchRow'])
            ->middleware('permission:import.manage|import.row.delete')
            ->name('rows.delete');

        // Admin: list import permissions for all roles
        Route::get('/roles/permissions', [ImportController::class, 'listRoleImportPermissions'])
            ->middleware('role:admin')
            ->name('roles.permissions.list');

        // Admin: update import permissions for a role
        Route::put('/roles/{role}/permissions', [ImportController::class, 'updateRoleImportPermissions'])
            ->middleware('role:admin')
            ->name('roles.permissions.update');

        // Approve batch and create hoardings
        Route::post('/{batch}/approve', [ImportApprovalController::class, 'approve'])
            ->middleware('permission:import.manage|import.batch.approve')
            ->name('approve');

        // Cancel import
        Route::delete('/{batch}', [ImportController::class, 'cancelImport'])
            ->middleware('permission:import.manage|import.batch.update')
            ->name('cancel');
    });
});
