<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\Api\AuthController;

/**
 * Auth API Routes (v1)
 * Base: /api/v1/auth
 * 
 * Authentication endpoints for login, register, logout, password reset
 */

// Public routes with rate limiting
Route::middleware(['throttle:register'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// OTP Authentication with strict rate limiting
Route::middleware(['throttle:otp'])->group(function () {
    Route::post('/otp/send', [AuthController::class, 'sendOTP']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOTP']);
});

// Password reset (to be implemented)
// Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Role Switching (PROMPT 96)
    Route::get('/roles/available', [\Modules\Auth\Controllers\Api\RoleSwitchController::class, 'getAvailableRoles']);
    Route::post('/roles/switch', [\Modules\Auth\Controllers\Api\RoleSwitchController::class, 'switchRole']);
    Route::get('/roles/permissions', [\Modules\Auth\Controllers\Api\RoleSwitchController::class, 'getActivePermissions']);
});
