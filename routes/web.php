<?php

use Illuminate\Support\Facades\Route;

/**
 * OOHAPP Web Routes (Blade Server-Rendered Pages)
 * 
 * Multi-panel application:
 * - Customer Web Panel (/)
 * - Vendor Web Panel (/vendor/*)
 * - Admin Web Panel (/admin/*)
 * - Staff Web Panel (/staff/*)
 */

// ============================================
// PUBLIC ROUTES (Customer-facing)
// ============================================
Route::get('/', [\App\Http\Controllers\Web\HomeController::class, 'index'])->name('home');
Route::get('/search', [\App\Http\Controllers\Web\SearchController::class, 'index'])->name('search');
Route::get('/hoardings', [\App\Http\Controllers\Web\HoardingController::class, 'index'])->name('hoardings.index');
Route::get('/hoardings/map', [\App\Http\Controllers\Web\HoardingController::class, 'map'])->name('hoardings.map');
Route::get('/hoardings/{id}', [\App\Http\Controllers\Web\HoardingController::class, 'show'])->name('hoardings.show');
Route::get('/dooh', [\App\Http\Controllers\Web\DOOHController::class, 'index'])->name('dooh.index');
Route::get('/dooh/{id}', [\App\Http\Controllers\Web\DOOHController::class, 'show'])->name('dooh.show');

// ============================================
// AUTH ROUTES (Guest users)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Web\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Web\Auth\LoginController::class, 'login']);
    
    Route::get('/register', [\App\Http\Controllers\Web\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Web\Auth\RegisterController::class, 'register']);
    
    // OTP Login
    Route::get('/login/otp', [\App\Http\Controllers\Web\Auth\OTPController::class, 'showOTPForm'])->name('login.otp');
    Route::post('/login/otp/send', [\App\Http\Controllers\Web\Auth\OTPController::class, 'sendOTP'])->name('otp.send');
    Route::post('/login/otp/verify', [\App\Http\Controllers\Web\Auth\OTPController::class, 'verifyOTP'])->name('otp.verify');
    Route::post('/login/otp/resend', [\App\Http\Controllers\Web\Auth\OTPController::class, 'resendOTP'])->name('otp.resend');
    
    // Password Reset (to be implemented)
    // Route::get('/forgot-password', [\App\Http\Controllers\Web\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    // Route::post('/forgot-password', [\App\Http\Controllers\Web\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    // Route::get('/reset-password/{token}', [\App\Http\Controllers\Web\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    // Route::post('/reset-password', [\App\Http\Controllers\Web\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [\App\Http\Controllers\Web\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ============================================
// CUSTOMER PANEL (Authenticated)
// ============================================
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Web\Customer\DashboardController::class, 'index'])->name('dashboard');
    
    // Enquiries
    Route::get('/enquiries', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/create', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'create'])->name('enquiries.create');
    Route::post('/enquiries', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'store'])->name('enquiries.store');
    Route::get('/enquiries/{id}', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'show'])->name('enquiries.show');
    
    // Quotations
    Route::get('/quotations', [\App\Http\Controllers\Web\Customer\QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/{id}', [\App\Http\Controllers\Web\Customer\QuotationController::class, 'show'])->name('quotations.show');
    Route::post('/quotations/{id}/accept', [\App\Http\Controllers\Web\Customer\QuotationController::class, 'accept'])->name('quotations.accept');
    
    // Bookings
    Route::get('/bookings', [\App\Http\Controllers\Web\Customer\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [\App\Http\Controllers\Web\Customer\BookingController::class, 'show'])->name('bookings.show');
    
    // Payments
    Route::get('/payments', [\App\Http\Controllers\Web\Customer\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}', [\App\Http\Controllers\Web\Customer\PaymentController::class, 'show'])->name('payments.show');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Web\Customer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Web\Customer\ProfileController::class, 'update'])->name('profile.update');
});

// ============================================
// VENDOR PANEL (Authenticated)
// ============================================
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Web\Vendor\DashboardController::class, 'index'])->name('dashboard');
    
    // Hoardings Management
    Route::resource('hoardings', \App\Http\Controllers\Web\Vendor\HoardingController::class);
    
    // DOOH Management
    Route::resource('dooh', \App\Http\Controllers\Web\Vendor\DOOHController::class);
    
    // Enquiries (received)
    Route::get('/enquiries', [\App\Http\Controllers\Web\Vendor\EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/{id}', [\App\Http\Controllers\Web\Vendor\EnquiryController::class, 'show'])->name('enquiries.show');
    Route::post('/enquiries/{id}/respond', [\App\Http\Controllers\Web\Vendor\EnquiryController::class, 'respond'])->name('enquiries.respond');
    
    // Offers
    Route::get('/offers', [\App\Http\Controllers\Web\Vendor\OfferController::class, 'index'])->name('offers.index');
    Route::get('/offers/create', [\App\Http\Controllers\Web\Vendor\OfferController::class, 'create'])->name('offers.create');
    Route::post('/offers', [\App\Http\Controllers\Web\Vendor\OfferController::class, 'store'])->name('offers.store');
    Route::get('/offers/{id}', [\App\Http\Controllers\Web\Vendor\OfferController::class, 'show'])->name('offers.show');
    
    // Quotations
    Route::get('/quotations', [\App\Http\Controllers\Web\Vendor\QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/create', [\App\Http\Controllers\Web\Vendor\QuotationController::class, 'create'])->name('quotations.create');
    Route::post('/quotations', [\App\Http\Controllers\Web\Vendor\QuotationController::class, 'store'])->name('quotations.store');
    Route::get('/quotations/{id}', [\App\Http\Controllers\Web\Vendor\QuotationController::class, 'show'])->name('quotations.show');
    
    // Bookings
    Route::get('/bookings', [\App\Http\Controllers\Web\Vendor\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [\App\Http\Controllers\Web\Vendor\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/approve-pod', [\App\Http\Controllers\Web\Vendor\BookingController::class, 'approvePOD'])->name('bookings.approve-pod');
    
    // Staff Management
    Route::resource('staff', \App\Http\Controllers\Web\Vendor\StaffController::class);
    
    // KYC
    Route::get('/kyc', [\App\Http\Controllers\Web\Vendor\KYCController::class, 'index'])->name('kyc.index');
    Route::post('/kyc/submit', [\App\Http\Controllers\Web\Vendor\KYCController::class, 'submit'])->name('kyc.submit');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\Web\Vendor\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue', [\App\Http\Controllers\Web\Vendor\ReportController::class, 'revenue'])->name('reports.revenue');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Web\Vendor\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Web\Vendor\ProfileController::class, 'update'])->name('profile.update');
});

// ============================================
// ADMIN PANEL (Authenticated)
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Web\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Users Management
    Route::resource('users', \App\Http\Controllers\Web\Admin\UserController::class);
    
    // Vendors Management
    Route::get('/vendors', [\App\Http\Controllers\Web\Admin\VendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/{id}', [\App\Http\Controllers\Web\Admin\VendorController::class, 'show'])->name('vendors.show');
    Route::post('/vendors/{id}/approve', [\App\Http\Controllers\Web\Admin\VendorController::class, 'approve'])->name('vendors.approve');
    Route::post('/vendors/{id}/suspend', [\App\Http\Controllers\Web\Admin\VendorController::class, 'suspend'])->name('vendors.suspend');
    
    // KYC Verification
    Route::get('/kyc/pending', [\App\Http\Controllers\Web\Admin\KYCController::class, 'pending'])->name('kyc.pending');
    Route::get('/kyc/{id}', [\App\Http\Controllers\Web\Admin\KYCController::class, 'show'])->name('kyc.show');
    Route::post('/kyc/{id}/approve', [\App\Http\Controllers\Web\Admin\KYCController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{id}/reject', [\App\Http\Controllers\Web\Admin\KYCController::class, 'reject'])->name('kyc.reject');
    
    // Hoardings Management
    Route::get('/hoardings', [\App\Http\Controllers\Web\Admin\HoardingController::class, 'index'])->name('hoardings.index');
    Route::get('/hoardings/{id}', [\App\Http\Controllers\Web\Admin\HoardingController::class, 'show'])->name('hoardings.show');
    Route::post('/hoardings/{id}/approve', [\App\Http\Controllers\Web\Admin\HoardingController::class, 'approve'])->name('hoardings.approve');
    Route::post('/hoardings/{id}/reject', [\App\Http\Controllers\Web\Admin\HoardingController::class, 'reject'])->name('hoardings.reject');
    
    // Bookings Management
    Route::get('/bookings', [\App\Http\Controllers\Web\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [\App\Http\Controllers\Web\Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{id}/price-snapshot', [\App\Http\Controllers\Admin\BookingHoldController::class, 'showPriceSnapshot'])->name('bookings.price-snapshot');
    Route::get('/bookings/holds/manage', [\App\Http\Controllers\Admin\BookingHoldController::class, 'index'])->name('bookings.holds');
    
    // Payments Management
    Route::get('/payments', [\App\Http\Controllers\Web\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}', [\App\Http\Controllers\Web\Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/process-payouts', [\App\Http\Controllers\Web\Admin\PaymentController::class, 'processPayouts'])->name('payments.process-payouts');
    
    // Settings
    Route::get('/settings', [\App\Http\Controllers\Web\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Web\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [\App\Http\Controllers\Web\Admin\SettingController::class, 'reset'])->name('settings.reset');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\Web\Admin\SettingController::class, 'clearCache'])->name('settings.clear-cache');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\Web\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue', [\App\Http\Controllers\Web\Admin\ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/vendors', [\App\Http\Controllers\Web\Admin\ReportController::class, 'vendors'])->name('reports.vendors');
    
    // Activity Log
    Route::get('/activity-log', [\App\Http\Controllers\Web\Admin\ActivityLogController::class, 'index'])->name('activity-log.index');
});

// ============================================
// STAFF PANEL (Designer, Printer, Mounter, Surveyor)
// ============================================
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Web\Staff\DashboardController::class, 'index'])->name('dashboard');
    
    // Assignments
    Route::get('/assignments', [\App\Http\Controllers\Web\Staff\AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{id}', [\App\Http\Controllers\Web\Staff\AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{id}/accept', [\App\Http\Controllers\Web\Staff\AssignmentController::class, 'accept'])->name('assignments.accept');
    Route::post('/assignments/{id}/complete', [\App\Http\Controllers\Web\Staff\AssignmentController::class, 'complete'])->name('assignments.complete');
    Route::post('/assignments/{id}/upload-proof', [\App\Http\Controllers\Web\Staff\AssignmentController::class, 'uploadProof'])->name('assignments.upload-proof');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Web\Staff\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Web\Staff\ProfileController::class, 'update'])->name('profile.update');
});

