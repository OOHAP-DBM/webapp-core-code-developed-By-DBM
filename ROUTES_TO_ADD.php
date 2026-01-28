<?php

/**
 * Routes for Vendor Email Management and Mobile Verification
 * Add these routes to your routes/web.php or vendor routes file
 */

use App\Http\Controllers\Vendor\EmailVerificationController;
use App\Http\Controllers\Vendor\MobileOTPController;
use Illuminate\Support\Facades\Route;

// Vendor Email Management Routes
Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    // Email Management
    Route::get('/emails', [EmailVerificationController::class, 'index'])->name('emails.index');
    Route::post('/emails/add', [EmailVerificationController::class, 'store'])->name('emails.store');
    Route::get('/emails/{id}/verify', [EmailVerificationController::class, 'showVerifyForm'])->name('emails.verify.show');
    Route::post('/emails/{id}/verify', [EmailVerificationController::class, 'verify'])->name('emails.verify');
    Route::post('/emails/{id}/resend-otp', [EmailVerificationController::class, 'resendOTP'])->name('emails.resend-otp');
    Route::post('/emails/{id}/make-primary', [EmailVerificationController::class, 'makePrimary'])->name('emails.make-primary');
    Route::delete('/emails/{id}', [EmailVerificationController::class, 'destroy'])->name('emails.destroy');

    // Mobile Verification
    Route::get('/verify-mobile', [MobileOTPController::class, 'show'])->name('mobile.verify.show');
    Route::post('/mobile/send-otp', [MobileOTPController::class, 'sendOTP'])->name('mobile.send-otp');
    Route::post('/mobile/verify', [MobileOTPController::class, 'verify'])->name('mobile.verify');
    Route::post('/mobile/resend-otp', [MobileOTPController::class, 'resendOTP'])->name('mobile.resend-otp');
    Route::get('/mobile/status', [MobileOTPController::class, 'getStatus'])->name('mobile.status');

    // Profile routes (for redirection)
    Route::get('/profile/verify-email', fn() => redirect()->route('vendor.emails.index'))->name('profile.verify-email');
    Route::get('/profile/verify-mobile', fn() => redirect()->route('vendor.mobile.verify.show'))->name('profile.verify-mobile');
});

// Hoarding Routes - Add to existing hoarding routes
Route::middleware(['auth', 'vendor'])->prefix('vendor/hoardings')->name('vendor.hoardings.')->group(function () {
    Route::post('/{id}/preview', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@preview')->name('preview');
    Route::post('/{id}/publish', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@publish')->name('publish');
    Route::get('/{id}/preview', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@showPreview')->name('show-preview');
    Route::get('/{id}/edit', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@edit')->name('edit');
    Route::put('/{id}', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@update')->name('update');
});

// Public Preview Route
Route::get('/hoarding/preview/{token}', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@showPreview')->name('hoarding.preview.show');
