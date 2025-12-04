<?php

use Illuminate\Support\Facades\Route;

/**
 * Auth API Routes (v1)
 * Base: /api/v1/auth
 * 
 * Authentication endpoints for login, register, logout, password reset
 */

// Public routes
Route::post('/register', [\Modules\Auth\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [\Modules\Auth\Controllers\Api\AuthController::class, 'login']);
Route::post('/forgot-password', [\Modules\Auth\Controllers\Api\AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [\Modules\Auth\Controllers\Api\AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\Modules\Auth\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/me', [\Modules\Auth\Controllers\Api\AuthController::class, 'me']);
    Route::post('/refresh', [\Modules\Auth\Controllers\Api\AuthController::class, 'refresh']);
});
