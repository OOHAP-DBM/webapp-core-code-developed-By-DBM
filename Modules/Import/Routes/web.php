<?php

use Illuminate\Support\Facades\Route;
use Modules\Import\Http\Controllers\ImportController;

/**
 * Import Module Web Routes
 *
 * Prefix: /import
 * Middleware: web, auth, permission
 */

Route::middleware(['permission:import.manage'])->group(function () {
    // Import dashboard - requires import.manage permission
    // Routes are prefixed with vendor/import or admin/import in RouteServiceProvider
    // Route names are prefixed as: vendor.import.dashboard or admin.import.dashboard
    Route::get('/', [ImportController::class, 'dashboard'])
        ->name('dashboard');
});