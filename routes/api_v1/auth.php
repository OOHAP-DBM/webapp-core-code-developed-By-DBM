<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\Api\AuthController;

/**
 * Auth API Routes (v1)
 * Base: /api/v1/auth
 * 
 * Authentication endpoints for login, register, logout, password reset
 */

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// OTP Authentication
Route::post('/otp/send', [AuthController::class, 'sendOTP']);
Route::post('/otp/verify', [AuthController::class, 'verifyOTP']);

// Password reset (to be implemented)
// Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});
