<?php

/**
 * VENDOR SIGNUP ROUTES CONFIGURATION
 * 
 * This file shows the routes needed for the refactored OTP vendor signup flow.
 * Add these routes to your routes/api.php or routes/web.php
 */

use App\Http\Controllers\Auth\VendorRegisterController;
use App\Http\Controllers\Vendor\MobileOTPController;
use App\Http\Controllers\Vendor\EmailVerificationController;

// ============================================================================
// PUBLIC ROUTES - Vendor Signup
// ============================================================================

Route::prefix('vendor')->name('vendor.')->group(function () {
    // Vendor Registration
    Route::post('/register', [VendorRegisterController::class, 'register'])->name('register');
    
    // Email verification during signup
    Route::post('/verify-email', [VendorRegisterController::class, 'verifyEmail'])->name('verify-email');
    
    // Mobile verification during signup
    Route::post('/verify-mobile', [VendorRegisterController::class, 'verifyMobile'])->name('verify-mobile');
});

// ============================================================================
// AUTHENTICATED ROUTES - Vendor Account Management
// ============================================================================

Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    
    // ========================================================================
    // MOBILE OTP ROUTES
    // ========================================================================
    
    Route::prefix('mobile')->name('mobile.')->group(function () {
        // Show mobile verification page
        Route::get('/verify', [MobileOTPController::class, 'show'])->name('show');
        
        // Send OTP to mobile
        Route::post('/send-otp', [MobileOTPController::class, 'sendOTP'])->name('send-otp');
        
        // Verify mobile OTP
        Route::post('/verify', [MobileOTPController::class, 'verify'])->name('verify');
        
        // Resend OTP
        Route::post('/resend-otp', [MobileOTPController::class, 'resendOTP'])->name('resend-otp');
        
        // Get mobile verification status
        Route::get('/status', [MobileOTPController::class, 'getStatus'])->name('status');
    });
    
    // ========================================================================
    // EMAIL VERIFICATION ROUTES
    // ========================================================================
    
    Route::prefix('emails')->name('emails.')->group(function () {
        // Get all vendor emails (verified & pending)
        Route::get('/', [EmailVerificationController::class, 'index'])->name('index');
        
        // Add new email
        Route::post('/add', [EmailVerificationController::class, 'store'])->name('add');
        
        // Verify email OTP
        Route::post('/verify', [EmailVerificationController::class, 'verify'])->name('verify');
        
        // Resend OTP to email
        Route::post('/resend-otp', [EmailVerificationController::class, 'resendOTP'])->name('resend-otp');
        
        // Get email verification status
        Route::get('/status', [EmailVerificationController::class, 'getStatus'])->name('status');
        
        // Delete email
        Route::delete('/', [EmailVerificationController::class, 'destroy'])->name('destroy');
    });
});

/**
 * ============================================================================
 * API ENDPOINT SUMMARY
 * ============================================================================
 * 
 * PUBLIC ENDPOINTS (No Authentication Required)
 * 
 * 1. Register Vendor
 *    POST /vendor/register
 *    Body: { name, email, phone, password, password_confirmation, business_name }
 *    Response: { success, message, vendor_id, email, phone, next_step }
 * 
 * 2. Verify Email OTP (Signup)
 *    POST /vendor/verify-email
 *    Body: { vendor_id, email, otp }
 *    Response: { success, message, verified_at }
 * 
 * 3. Verify Mobile OTP (Signup)
 *    POST /vendor/verify-mobile
 *    Body: { vendor_id, otp }
 *    Response: { success, message, verified_at }
 * 
 * ============================================================================
 * AUTHENTICATED ENDPOINTS (Authentication Required)
 * 
 * MOBILE OTP
 * 
 * 1. Show Mobile Verification Page
 *    GET /vendor/mobile/verify
 *    Response: HTML form with mobile verification
 * 
 * 2. Send OTP to Mobile
 *    POST /vendor/mobile/send-otp
 *    Body: { phone (optional) }
 *    Response: { success, message, phone }
 * 
 * 3. Verify Mobile OTP
 *    POST /vendor/mobile/verify
 *    Body: { otp }
 *    Response: { success, message, verified_at }
 * 
 * 4. Resend OTP
 *    POST /vendor/mobile/resend-otp
 *    Response: { success, message, retry_after (if rate limited) }
 * 
 * 5. Get Mobile Status
 *    GET /vendor/mobile/status
 *    Response: { is_verified, phone, verified_at }
 * 
 * ============================================================================
 * EMAIL VERIFICATION
 * 
 * 1. Get All Emails (Verified & Pending)
 *    GET /vendor/emails
 *    Response: { success, verified: [...], pending: [...] }
 * 
 * 2. Add New Email
 *    POST /vendor/emails/add
 *    Body: { email }
 *    Response: { success, message, email }
 * 
 * 3. Verify Email OTP
 *    POST /vendor/emails/verify
 *    Body: { email, otp }
 *    Response: { success, message, email }
 * 
 * 4. Resend OTP
 *    POST /vendor/emails/resend-otp
 *    Body: { email }
 *    Response: { success, message, retry_after (if rate limited) }
 * 
 * 5. Get Email Status
 *    GET /vendor/emails/status
 *    Query: ?email=email@example.com
 *    Response: { email, is_verified, has_pending }
 * 
 * 6. Delete Email
 *    DELETE /vendor/emails
 *    Body: { email }
 *    Response: { success, message }
 * 
 * ============================================================================
 * SIGNUP FLOW
 * ============================================================================
 * 
 * STEP 1: Vendor Registration
 *   POST /vendor/register
 *   → Email OTP and Mobile OTP are automatically sent
 *   ← Vendor gets vendor_id for next steps
 * 
 * STEP 2: Verify Email (Primary Email)
 *   POST /vendor/verify-email
 *   → Verify OTP sent to users.email
 * 
 * STEP 3: Verify Mobile
 *   POST /vendor/verify-mobile
 *   → Verify OTP sent via Twilio SMS
 * 
 * STEP 4: Add Secondary Emails (Optional, After Login)
 *   POST /vendor/emails/add
 *   → Add another email to vendor_emails table
 * 
 * STEP 5: Verify Secondary Email
 *   POST /vendor/emails/verify
 *   → Verify OTP sent to secondary email
 * 
 * ============================================================================
 * DATABASE FLOW
 * ============================================================================
 * 
 * REGISTRATION:
 * users table: { id, email, phone, password, email_verified_at: null, phone_verified_at: null }
 * user_otps table: { user_id, identifier: email, otp_hash, purpose: 'vendor_email_verification' }
 * user_otps table: { user_id, identifier: phone, otp_hash, purpose: 'mobile_verification' }
 * 
 * AFTER EMAIL VERIFICATION:
 * users table: { ..., email_verified_at: now() }
 * user_otps table: { ..., verified_at: now() }
 * 
 * AFTER MOBILE VERIFICATION:
 * users table: { ..., phone_verified_at: now() }
 * user_otps table: { ..., verified_at: now() }
 * 
 * ADDING SECONDARY EMAIL:
 * vendor_emails table: { user_id, email: 'another@example.com', verified_at: null }
 * user_otps table: { user_id, identifier: 'another@example.com', otp_hash, purpose: 'vendor_email_verification' }
 * 
 * AFTER SECONDARY EMAIL VERIFICATION:
 * vendor_emails table: { ..., verified_at: now() }
 * user_otps table: { ..., verified_at: now() }
 * 
 * ============================================================================
 */
