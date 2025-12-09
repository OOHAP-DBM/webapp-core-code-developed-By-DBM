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
    // Home/Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Web\Customer\HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [\App\Http\Controllers\Web\Customer\HomeController::class, 'index'])->name('home');
    
    // Search
    Route::get('/search', [\App\Http\Controllers\Web\Customer\SearchController::class, 'index'])->name('search');
    
    // Shortlist/Wishlist
    Route::get('/shortlist', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'index'])->name('shortlist');
    Route::post('/shortlist/{hoarding}', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'store'])->name('shortlist.store');
    Route::delete('/shortlist/{hoarding}', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'destroy'])->name('shortlist.destroy');
    Route::post('/shortlist/clear', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'clear'])->name('shortlist.clear');
    
    // Enquiries
    Route::get('/enquiries', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/create', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'create'])->name('enquiries.create');
    Route::post('/enquiries', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'store'])->name('enquiries.store');
    Route::get('/enquiries/{id}', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'show'])->name('enquiries.show');
    Route::post('/enquiries/{id}/cancel', [\App\Http\Controllers\Web\Customer\EnquiryController::class, 'cancel'])->name('enquiries.cancel');
    
    // Quotations
    Route::get('/quotations', [\App\Http\Controllers\Web\Customer\QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/{id}', [\App\Http\Controllers\Web\Customer\QuotationController::class, 'show'])->name('quotations.show');
    Route::post('/quotations/{id}/accept', [\App\Http\Controllers\Web\Customer\QuotationController::class, 'accept'])->name('quotations.accept');
    
    // Orders/Bookings
    Route::get('/orders', [\App\Http\Controllers\Web\Customer\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [\App\Http\Controllers\Web\Customer\OrderController::class, 'show'])->name('orders.show');
    Route::get('/bookings', [\App\Http\Controllers\Web\Customer\OrderController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [\App\Http\Controllers\Web\Customer\OrderController::class, 'show'])->name('bookings.show');
    
    // Payments
    Route::get('/payments', [\App\Http\Controllers\Web\Customer\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}', [\App\Http\Controllers\Web\Customer\PaymentController::class, 'show'])->name('payments.show');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Web\Customer\ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [\App\Http\Controllers\Web\Customer\ProfileController::class, 'index'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Web\Customer\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [\App\Http\Controllers\Web\Customer\ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::get('/profile/kyc', function() { return view('customer.profile.kyc'); })->name('profile.kyc');
    Route::post('/kyc/submit', [\App\Http\Controllers\Web\Customer\ProfileController::class, 'submitKyc'])->name('kyc.submit');
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Web\Customer\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Web\Customer\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\Web\Customer\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    
    // Threads
    Route::get('/threads', [\App\Http\Controllers\Customer\ThreadController::class, 'index'])->name('threads.index');
    Route::get('/threads/{id}', [\App\Http\Controllers\Customer\ThreadController::class, 'show'])->name('threads.show');
    Route::post('/threads/{id}/send-message', [\App\Http\Controllers\Customer\ThreadController::class, 'sendMessage'])->name('threads.send-message');
    Route::post('/threads/{id}/mark-read', [\App\Http\Controllers\Customer\ThreadController::class, 'markAsRead'])->name('threads.mark-read');
    Route::post('/threads/{id}/archive', [\App\Http\Controllers\Customer\ThreadController::class, 'archive'])->name('threads.archive');
    Route::get('/threads/unread-count', [\App\Http\Controllers\Customer\ThreadController::class, 'unreadCount'])->name('threads.unread-count');
    
    // Bookings Create
    Route::get('/bookings/create', function() { 
        $hoarding = \App\Models\Hoarding::first();
        return view('customer.bookings.create', ['hoarding' => $hoarding, 'quotation' => null]); 
    })->name('bookings.create');
    Route::post('/bookings', function() { return redirect()->route('customer.orders.index'); })->name('bookings.store');
});

// ============================================
// VENDOR PANEL (Authenticated)
// ============================================
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    // Dashboard (PROMPT 26)
    Route::get('/dashboard', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('dashboard');
    
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
    
    // Threads
    Route::get('/threads', [\App\Http\Controllers\Vendor\ThreadController::class, 'index'])->name('threads.index');
    Route::get('/threads/{id}', [\App\Http\Controllers\Vendor\ThreadController::class, 'show'])->name('threads.show');
    Route::post('/threads/{id}/send-message', [\App\Http\Controllers\Vendor\ThreadController::class, 'sendMessage'])->name('threads.send-message');
    Route::post('/threads/{id}/mark-read', [\App\Http\Controllers\Vendor\ThreadController::class, 'markAsRead'])->name('threads.mark-read');
    Route::post('/threads/{id}/archive', [\App\Http\Controllers\Vendor\ThreadController::class, 'archive'])->name('threads.archive');
    Route::get('/threads/unread-count', [\App\Http\Controllers\Vendor\ThreadController::class, 'unreadCount'])->name('threads.unread-count');
    
    // Listings Management (PROMPT 26)
    Route::get('/listings', [\App\Http\Controllers\Vendor\ListingController::class, 'index'])->name('listings.index');
    Route::get('/listings/create', [\App\Http\Controllers\Vendor\ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [\App\Http\Controllers\Vendor\ListingController::class, 'store'])->name('listings.store');
    Route::get('/listings/{id}/edit', [\App\Http\Controllers\Vendor\ListingController::class, 'edit'])->name('listings.edit');
    Route::put('/listings/{id}', [\App\Http\Controllers\Vendor\ListingController::class, 'update'])->name('listings.update');
    Route::delete('/listings/{id}', [\App\Http\Controllers\Vendor\ListingController::class, 'destroy'])->name('listings.destroy');
    Route::get('/listings/bulk-update', [\App\Http\Controllers\Vendor\ListingController::class, 'bulkUpdate'])->name('listings.bulk-update');
    Route::post('/listings/bulk-update-submit', [\App\Http\Controllers\Vendor\ListingController::class, 'bulkUpdateSubmit'])->name('listings.bulk-update-submit');
    
    // Bookings Management (PROMPT 26)
    Route::get('/bookings', [\App\Http\Controllers\Vendor\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [\App\Http\Controllers\Vendor\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/confirm', [\App\Http\Controllers\Vendor\BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{id}/cancel', [\App\Http\Controllers\Vendor\BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{id}/update-status', [\App\Http\Controllers\Vendor\BookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::post('/bookings/{id}/approve-pod', [\App\Http\Controllers\Web\Vendor\BookingController::class, 'approvePOD'])->name('bookings.approve-pod');
    
    // Task Management (PROMPT 26)
    Route::get('/tasks', [\App\Http\Controllers\Vendor\TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [\App\Http\Controllers\Vendor\TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{id}', [\App\Http\Controllers\Vendor\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{id}/start', [\App\Http\Controllers\Vendor\TaskController::class, 'start'])->name('tasks.start');
    Route::post('/tasks/{id}/complete', [\App\Http\Controllers\Vendor\TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('/tasks/{id}/update-progress', [\App\Http\Controllers\Vendor\TaskController::class, 'updateProgress'])->name('tasks.update-progress');
    Route::delete('/tasks/{id}', [\App\Http\Controllers\Vendor\TaskController::class, 'destroy'])->name('tasks.destroy');
    
    // Payouts (PROMPT 26)
    Route::get('/payouts', [\App\Http\Controllers\Vendor\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/request', [\App\Http\Controllers\Vendor\PayoutController::class, 'request'])->name('payouts.request');
    Route::get('/payouts/{id}', [\App\Http\Controllers\Vendor\PayoutController::class, 'show'])->name('payouts.show');
    Route::post('/payouts/update-bank', [\App\Http\Controllers\Vendor\PayoutController::class, 'updateBank'])->name('payouts.update-bank');
    
    // Staff Management
    Route::resource('staff', \App\Http\Controllers\Web\Vendor\StaffController::class);
    
    // KYC
    Route::get('/kyc', [\App\Http\Controllers\Web\Vendor\VendorKYCWebController::class, 'showSubmitForm'])->name('kyc.index');
    Route::get('/kyc/submit', [\App\Http\Controllers\Web\Vendor\VendorKYCWebController::class, 'showSubmitForm'])->name('kyc.submit');
    
    // POS/Billing (PROMPT 26)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Vendor\POSController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\Vendor\POSController::class, 'store'])->name('store');
        Route::get('/history', [\App\Http\Controllers\Vendor\POSController::class, 'history'])->name('history');
        Route::get('/{id}', [\App\Http\Controllers\Vendor\POSController::class, 'show'])->name('show');
        Route::get('/{id}/preview', [\App\Http\Controllers\Vendor\POSController::class, 'preview'])->name('preview');
        Route::get('/{id}/download', [\App\Http\Controllers\Vendor\POSController::class, 'download'])->name('download');
        Route::post('/{id}/update-status', [\App\Http\Controllers\Vendor\POSController::class, 'updateStatus'])->name('update-status');
        
        // Legacy routes
        Route::get('/dashboard', function () {
            return view('vendor.pos.dashboard');
        })->name('dashboard');
        Route::get('/create', function () {
            return view('vendor.pos.create');
        })->name('create');
        Route::get('/list', function () {
            return view('vendor.pos.list');
        })->name('list');
        Route::get('/bookings/{id}', function ($id) {
            return view('vendor.pos.show', compact('id'));
        })->name('bookings.show');
    });
    
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
    
    // Settings (New Enhanced Settings System - PROMPT 29)
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    
    // Price Update Engine (PROMPT 30)
    Route::prefix('price-updates')->name('price-updates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PriceUpdateController::class, 'index'])->name('index');
        Route::get('/single/{hoarding_id?}', [\App\Http\Controllers\Admin\PriceUpdateController::class, 'singleUpdate'])->name('single');
        Route::post('/single', [\App\Http\Controllers\Admin\PriceUpdateController::class, 'storeSingleUpdate'])->name('single.store');
        Route::get('/bulk', [\App\Http\Controllers\Admin\PriceUpdateController::class, 'bulkUpdate'])->name('bulk');
        Route::post('/bulk/preview', [\App\Http\Controllers\Admin\PriceUpdateController::class, 'previewBulkUpdate'])->name('bulk.preview');
        Route::post('/bulk', [\App\Http\Controllers\Admin\PriceUpdateController::class, 'storeBulkUpdate'])->name('bulk.store');
        Route::get('/logs', [\App\Http\Controllers\Admin\PriceUpdateController::class, 'logs'])->name('logs');
        Route::get('/logs/{id}', [\App\Http\Controllers\Admin\PriceUpdateController::class, 'showLog'])->name('logs.show');
    });
    
    // Commission Rules (PROMPT 31)
    Route::prefix('commission-rules')->name('commission-rules.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'store'])->name('store');
        Route::get('/{commissionRule}', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'show'])->name('show');
        Route::get('/{commissionRule}/edit', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'edit'])->name('edit');
        Route::put('/{commissionRule}', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'update'])->name('update');
        Route::delete('/{commissionRule}', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'destroy'])->name('destroy');
        Route::post('/{commissionRule}/toggle', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{commissionRule}/duplicate', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'duplicate'])->name('duplicate');
        Route::post('/preview', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'preview'])->name('preview');
    });
    
    // Refunds & Cancellation Policies (PROMPT 32)
    Route::prefix('refunds')->name('refunds.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RefundController::class, 'index'])->name('index');
        Route::get('/{refund}', [\App\Http\Controllers\Admin\RefundController::class, 'show'])->name('show');
        Route::post('/{refund}/approve', [\App\Http\Controllers\Admin\RefundController::class, 'approve'])->name('approve');
        Route::post('/{refund}/process-manual', [\App\Http\Controllers\Admin\RefundController::class, 'processManual'])->name('process-manual');
        Route::get('/export', [\App\Http\Controllers\Admin\RefundController::class, 'export'])->name('export');
    });
    
    Route::prefix('cancellation-policies')->name('cancellation-policies.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RefundController::class, 'policies'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\RefundController::class, 'createPolicy'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\RefundController::class, 'storePolicy'])->name('store');
    });
    
    // Payment Settlement Engine (PROMPT 33)
    Route::prefix('settlements')->name('settlements.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettlementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\SettlementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\SettlementController::class, 'store'])->name('store');
        Route::get('/{batch}', [\App\Http\Controllers\Admin\SettlementController::class, 'show'])->name('show');
        Route::post('/{batch}/submit', [\App\Http\Controllers\Admin\SettlementController::class, 'submitForApproval'])->name('submit');
        Route::post('/{batch}/approve', [\App\Http\Controllers\Admin\SettlementController::class, 'approve'])->name('approve');
        Route::post('/{batch}/process', [\App\Http\Controllers\Admin\SettlementController::class, 'process'])->name('process');
        
        // Vendor Ledgers
        Route::get('/ledgers/all', [\App\Http\Controllers\Admin\SettlementController::class, 'ledgers'])->name('ledgers');
        Route::get('/ledgers/vendor/{vendor}', [\App\Http\Controllers\Admin\SettlementController::class, 'vendorLedger'])->name('vendor-ledger');
        Route::post('/ledgers/vendor/{vendor}/release-hold', [\App\Http\Controllers\Admin\SettlementController::class, 'releaseHeldAmounts'])->name('release-hold');
        Route::post('/ledgers/vendor/{vendor}/adjustment', [\App\Http\Controllers\Admin\SettlementController::class, 'createAdjustment'])->name('adjustment');
    });
    
    // Booking Rules
    Route::get('/booking-rules', [\App\Http\Controllers\Web\Admin\BookingRuleController::class, 'index'])->name('booking-rules.index');
    Route::put('/booking-rules', [\App\Http\Controllers\Web\Admin\BookingRuleController::class, 'update'])->name('booking-rules.update');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\Web\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue', [\App\Http\Controllers\Web\Admin\ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/vendors', [\App\Http\Controllers\Web\Admin\ReportController::class, 'vendors'])->name('reports.vendors');
    
    // Activity Log
    Route::get('/activity-log', [\App\Http\Controllers\Web\Admin\ActivityLogController::class, 'index'])->name('activity-log.index');
});

// ============================================
// STAFF PANEL (Graphics, Printer, Mounter, Surveyor) - PROMPT 27
// ============================================
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('dashboard');
    
    // Assignments Management
    Route::get('/assignments', [\App\Http\Controllers\Staff\AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{id}', [\App\Http\Controllers\Staff\AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{id}/accept', [\App\Http\Controllers\Staff\AssignmentController::class, 'accept'])->name('assignments.accept');
    Route::post('/assignments/{id}/complete', [\App\Http\Controllers\Staff\AssignmentController::class, 'complete'])->name('assignments.complete');
    Route::post('/assignments/{id}/upload-proof', [\App\Http\Controllers\Staff\AssignmentController::class, 'uploadProof'])->name('assignments.upload-proof');
    Route::post('/assignments/{id}/send-update', [\App\Http\Controllers\Staff\AssignmentController::class, 'sendUpdate'])->name('assignments.send-update');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Web\Staff\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Web\Staff\ProfileController::class, 'update'])->name('profile.update');
});

