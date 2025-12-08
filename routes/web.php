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
Route::get('/hoardings', [\Modules\Hoardings\Controllers\Web\HoardingController::class, 'index'])->name('hoardings.index');
Route::get('/hoardings/map', [\Modules\Hoardings\Controllers\Web\HoardingController::class, 'map'])->name('hoardings.map');
Route::get('/hoardings/{id}', [\Modules\Hoardings\Controllers\Web\HoardingController::class, 'show'])->name('hoardings.show');
// TODO: DOOH feature coming soon
// Route::get('/dooh', [\App\Http\Controllers\Web\DOOHController::class, 'index'])->name('dooh.index');
// Route::get('/dooh/{id}', [\App\Http\Controllers\Web\DOOHController::class, 'show'])->name('dooh.show');

// ============================================
// AUTH ROUTES (Guest users)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [\Modules\Auth\Controllers\Web\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\Modules\Auth\Controllers\Web\LoginController::class, 'login']);
    
    Route::get('/register', [\Modules\Auth\Controllers\Web\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\Modules\Auth\Controllers\Web\RegisterController::class, 'register']);
    
    // OTP Login
    Route::get('/login/otp', [\Modules\Auth\Controllers\Web\OTPController::class, 'showOTPForm'])->name('login.otp');
    Route::post('/login/otp/send', [\Modules\Auth\Controllers\Web\OTPController::class, 'sendOTP'])->name('otp.send');
    Route::post('/login/otp/verify', [\Modules\Auth\Controllers\Web\OTPController::class, 'verifyOTP'])->name('otp.verify');
    Route::post('/login/otp/resend', [\Modules\Auth\Controllers\Web\OTPController::class, 'resendOTP'])->name('otp.resend');
    
    // Password Reset (to be implemented)
    // Route::get('/forgot-password', [\Modules\Auth\Controllers\Web\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    // Route::post('/forgot-password', [\Modules\Auth\Controllers\Web\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    // Route::get('/reset-password/{token}', [\Modules\Auth\Controllers\Web\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    // Route::post('/reset-password', [\Modules\Auth\Controllers\Web\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [\Modules\Auth\Controllers\Web\LoginController::class, 'logout'])->name('logout')->middleware('auth');

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
    Route::resource('hoardings', \Modules\Vendor\Controllers\Web\HoardingController::class);
    
    // DOOH Management (Coming soon)
    // Route::resource('dooh', \App\Http\Controllers\Web\Vendor\DOOHController::class);
    
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
    Route::get('/kyc', [\App\Http\Controllers\Web\Vendor\VendorKYCWebController::class, 'showSubmitForm'])->name('kyc.index');
    Route::get('/kyc/submit', [\App\Http\Controllers\Web\Vendor\VendorKYCWebController::class, 'showSubmitForm'])->name('kyc.submit');
    
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
    Route::get('/kyc', [\App\Http\Controllers\Web\Admin\AdminKYCWebController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/{id}', [\App\Http\Controllers\Web\Admin\AdminKYCWebController::class, 'show'])->name('kyc.show');
    
    // KYC Reviews & Manual Override
    Route::get('/vendor/kyc-reviews', [\App\Http\Controllers\Web\Admin\AdminKYCReviewController::class, 'index'])->name('kyc-reviews.index');
    Route::get('/vendor/kyc-reviews/{id}', [\App\Http\Controllers\Web\Admin\AdminKYCReviewController::class, 'show'])->name('kyc-reviews.show');
    
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
    
    // Finance & Commission Management
    Route::get('/finance/bookings-payments', [\App\Http\Controllers\Admin\FinanceController::class, 'bookingsPaymentsLedger'])->name('finance.bookings-payments');
    Route::get('/finance/pending-manual-payouts', [\App\Http\Controllers\Admin\FinanceController::class, 'pendingManualPayouts'])->name('finance.pending-manual-payouts');
    
    // Settings
    Route::get('/settings', [\Modules\Admin\Controllers\Web\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\Modules\Admin\Controllers\Web\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [\Modules\Admin\Controllers\Web\SettingController::class, 'reset'])->name('settings.reset');
    Route::post('/settings/clear-cache', [\Modules\Admin\Controllers\Web\SettingController::class, 'clearCache'])->name('settings.clear-cache');
    
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


