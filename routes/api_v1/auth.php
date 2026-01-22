<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\AuthController;
use Modules\Auth\Http\Controllers\Api\RoleSwitchController;
use Modules\Auth\Http\Controllers\Api\VendorOnboardingController;
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

// http: //127.0.0.1:8000/api/v1/auth/register/otp/verify
// OTP Authentication with strict rate limiting
Route::middleware(['throttle:otp'])->group(function () {
    Route::post('/register/otp/verify', [AuthController::class, 'verifyRegisterOTP']);
    Route::post('/register/otp/send', [AuthController::class, 'sendRegisterOTP']);


    Route::post('/otp/send', [AuthController::class, 'sendOTP']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOTP']);
    Route::post('/otp/resend', [AuthController::class, 'resendOTP']);
});

// Password reset (to be implemented)
Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/password/verify-otp', [AuthController::class, 'verifyForgotPasswordOTP']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);


// Protected routes
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Role Switching (PROMPT 96)
    Route::get('/roles/available', [RoleSwitchController::class, 'getAvailableRoles']);
    Route::post('/roles/switch', [RoleSwitchController::class, 'switchRole']);
    Route::get('/roles/permissions', [RoleSwitchController::class, 'getActivePermissions']);
});



// Vendor Specific Onboarding Routes
Route::middleware(['auth:sanctum','role:vendor'])->prefix('vendor/onboarding')->group(function () {
    Route::post('/send-otp', [VendorOnboardingController::class, 'sendOtp']);
    Route::post('/verify-otp', [VendorOnboardingController::class, 'verifyOtp']);
    Route::post('/business-info', [VendorOnboardingController::class, 'submitBusinessInfo']);
    Route::post('/skip-business-info', [VendorOnboardingController::class, 'skipBusinessInfo']);
    Route::post('/skip-contact', [VendorOnboardingController::class, 'skipContactVerification']);
});
