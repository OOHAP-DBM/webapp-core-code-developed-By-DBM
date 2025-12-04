<?php

use Illuminate\Support\Facades\Route;

/**
 * Staff API Routes (v1)
 * Base: /api/v1/staff
 * 
 * Staff management: Designer, Printer, Mounter, Surveyor
 */

// Staff routes
Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    Route::get('/dashboard', [\Modules\Staff\Controllers\Api\StaffController::class, 'dashboard']);
    Route::get('/assignments', [\Modules\Staff\Controllers\Api\StaffController::class, 'assignments']);
    Route::get('/assignments/{id}', [\Modules\Staff\Controllers\Api\StaffController::class, 'assignmentDetails']);
    Route::post('/assignments/{id}/accept', [\Modules\Staff\Controllers\Api\StaffController::class, 'acceptAssignment']);
    Route::post('/assignments/{id}/complete', [\Modules\Staff\Controllers\Api\StaffController::class, 'completeAssignment']);
    Route::post('/assignments/{id}/upload-proof', [\Modules\Staff\Controllers\Api\StaffController::class, 'uploadProof']);
});

// Vendor routes - Manage staff
Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
    Route::get('/vendor/staff', [\Modules\Staff\Controllers\Api\StaffController::class, 'vendorStaff']);
    Route::post('/vendor/staff/invite', [\Modules\Staff\Controllers\Api\StaffController::class, 'inviteStaff']);
    Route::post('/assignments/create', [\Modules\Staff\Controllers\Api\StaffController::class, 'createAssignment']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [\Modules\Staff\Controllers\Api\StaffController::class, 'index']);
    Route::post('/{id}/verify', [\Modules\Staff\Controllers\Api\StaffController::class, 'verify']);
});
