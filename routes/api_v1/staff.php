<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\AssignmentController;

/**
 * Staff API Routes (v1) - PROMPT 27
 * Base: /api/v1/staff
 * 
 * For Graphics Designer, Printer, Mounter, Surveyor Mobile Apps
 */

// Staff routes (Mobile App Authentication)
Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Assignments
    Route::get('/assignments', [AssignmentController::class, 'index']);
    Route::get('/assignments/{id}', [AssignmentController::class, 'show']);
    Route::post('/assignments/{id}/accept', [AssignmentController::class, 'accept']);
    Route::post('/assignments/{id}/complete', [AssignmentController::class, 'complete']);
    Route::post('/assignments/{id}/upload-proof', [AssignmentController::class, 'uploadProof']);
    Route::post('/assignments/{id}/send-update', [AssignmentController::class, 'sendUpdate']);
    
    // Profile
    Route::get('/profile', function(\Illuminate\Http\Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });
    
    // Stats
    Route::get('/stats', function(\Illuminate\Http\Request $request) {
        $user = $request->user();
        $stats = [
            'total_assignments' => $user->assignments()->count(),
            'pending' => $user->assignments()->where('status', 'pending')->count(),
            'in_progress' => $user->assignments()->where('status', 'in_progress')->count(),
            'completed' => $user->assignments()->where('status', 'completed')->count(),
        ];
        return response()->json(['success' => true, 'data' => $stats]);
    });
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
