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

// Newsletter Routes
Route::post('/newsletter/subscribe', [\App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/unsubscribe', [\App\Http\Controllers\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Sitemap Routes (PROMPT 79)
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-static.xml', [\App\Http\Controllers\SitemapController::class, 'static'])->name('sitemap.static');
Route::get('/sitemap-hoardings.xml', [\App\Http\Controllers\SitemapController::class, 'hoardings'])->name('sitemap.hoardings');
Route::get('/sitemap-locations.xml', [\App\Http\Controllers\SitemapController::class, 'locations'])->name('sitemap.locations');

// Language Routes (PROMPT 80)
Route::post('/language/switch', [\App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');
Route::get('/language/selector', [\App\Http\Controllers\LanguageController::class, 'selector'])->name('language.selector');
Route::get('/api/languages', [\App\Http\Controllers\LanguageController::class, 'index'])->name('api.languages.index');
Route::get('/api/languages/{locale}/translations', [\App\Http\Controllers\LanguageController::class, 'getTranslations'])->name('api.languages.translations');
Route::post('/api/languages/suggest', [\App\Http\Controllers\LanguageController::class, 'suggestTranslation'])->name('api.languages.suggest')->middleware('auth');

// Map Search Routes
Route::get('/map-search', [\App\Http\Controllers\MapSearchController::class, 'index'])->name('map-search.index');
Route::post('/api/map/search', [\App\Http\Controllers\MapSearchController::class, 'search'])->name('api.map.search');
Route::post('/api/map/search/geojson', [\App\Http\Controllers\MapSearchController::class, 'searchGeoJSON'])->name('api.map.search.geojson');
Route::get('/api/map/nearby', [\App\Http\Controllers\MapSearchController::class, 'nearby'])->name('api.map.nearby');
Route::get('/api/map/autocomplete', [\App\Http\Controllers\MapSearchController::class, 'autocomplete'])->name('api.map.autocomplete');

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
// ROLE SWITCHING (PROMPT 96)
// ============================================
Route::middleware(['auth'])->prefix('auth')->name('auth.')->group(function () {
    Route::post('/switch-role/{role}', [\App\Http\Controllers\Web\Auth\RoleSwitchController::class, 'switch'])->name('switch-role');
    Route::get('/available-roles', [\App\Http\Controllers\Web\Auth\RoleSwitchController::class, 'getAvailableRoles'])->name('available-roles');
});

// ============================================
// CUSTOMER PANEL (Authenticated)
// ============================================
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    // Home/Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Web\Customer\HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [\App\Http\Controllers\Web\Customer\HomeController::class, 'index'])->name('home');
    
    // Search (PROMPT 54: Smart Search Algorithm)
    Route::get('/search', [\App\Http\Controllers\Web\Customer\SearchController::class, 'index'])->name('search');
    Route::post('/api/search', [\App\Http\Controllers\Web\Customer\SearchController::class, 'apiSearch'])->name('api.search');
    Route::get('/api/search/filters', [\App\Http\Controllers\Web\Customer\SearchController::class, 'getFilterOptions'])->name('api.search.filters');
    
    // Saved Searches
    Route::post('/saved-searches', [\App\Http\Controllers\MapSearchController::class, 'saveSearch'])->name('saved-searches.store');
    Route::get('/saved-searches', [\App\Http\Controllers\MapSearchController::class, 'getSavedSearches'])->name('saved-searches.index');
    Route::post('/saved-searches/{savedSearch}/execute', [\App\Http\Controllers\MapSearchController::class, 'executeSavedSearch'])->name('saved-searches.execute');
    Route::delete('/saved-searches/{savedSearch}', [\App\Http\Controllers\MapSearchController::class, 'deleteSavedSearch'])->name('saved-searches.destroy');
    
    // Shortlist/Wishlist
    Route::get('/shortlist', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'index'])->name('shortlist');
    Route::post('/shortlist/{hoarding}', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'store'])->name('shortlist.store');
    Route::delete('/shortlist/{hoarding}', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'destroy'])->name('shortlist.destroy');
    Route::post('/shortlist/clear', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'clear'])->name('shortlist.clear');
    // PROMPT 50: New routes for toggle, check, and count
    Route::post('/shortlist/toggle/{hoarding}', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'toggle'])->name('shortlist.toggle');
    Route::get('/shortlist/check/{hoarding}', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'check'])->name('shortlist.check');
    Route::get('/shortlist/count', [\App\Http\Controllers\Web\Customer\ShortlistController::class, 'count'])->name('shortlist.count');
    
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
    
    // ============================================
    // CUSTOMER DASHBOARD + REPORTS (PROMPT 40)
    // ============================================
    Route::prefix('my')->name('my.')->group(function () {
        // Main Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'index'])->name('dashboard');
        
        // My Bookings
        Route::get('/bookings', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'myBookings'])->name('bookings');
        Route::get('/bookings/export/{format}', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'exportBookings'])->name('bookings.export');
        
        // My Payments
        Route::get('/payments', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'myPayments'])->name('payments');
        Route::get('/payments/export/{format}', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'exportPayments'])->name('payments.export');
        
        // My Enquiries
        Route::get('/enquiries', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'myEnquiries'])->name('enquiries');
        
        // My Offers
        Route::get('/offers', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'myOffers'])->name('offers');
        
        // My Quotations
        Route::get('/quotations', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'myQuotations'])->name('quotations');
        
        // My Invoices (PROMPT 64)
        Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices');
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');
        Route::get('/invoices/{invoice}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('/invoices/{invoice}/email', [\App\Http\Controllers\InvoiceController::class, 'sendEmail'])->name('invoices.email');
        Route::get('/invoices/export/{format}', [\App\Http\Controllers\InvoiceController::class, 'export'])->name('invoices.export');
        
        // My Threads
        Route::get('/threads', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'myThreads'])->name('threads');
        
        // Refresh Stats
        Route::post('/refresh-stats', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'refreshStats'])->name('refresh-stats');
    });
    
    // DOOH Creatives & Schedules (PROMPT 67)
    Route::prefix('dooh')->name('dooh.')->group(function () {
        // Creatives
        Route::get('/creatives', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'creatives'])->name('creatives.index');
        Route::get('/creatives/create', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'createCreative'])->name('creatives.create');
        Route::post('/creatives', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'storeCreative'])->name('creatives.store');
        Route::get('/creatives/{creative}', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'showCreative'])->name('creatives.show');
        Route::delete('/creatives/{creative}', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'destroyCreative'])->name('creatives.destroy');
        
        // Schedules
        Route::get('/schedules', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'schedules'])->name('schedules.index');
        Route::get('/schedules/create', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'createSchedule'])->name('schedules.create');
        Route::post('/schedules', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'storeSchedule'])->name('schedules.store');
        Route::get('/schedules/{schedule}', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'showSchedule'])->name('schedules.show');
        Route::post('/schedules/{schedule}/cancel', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'cancelSchedule'])->name('schedules.cancel');
        
        // AJAX Routes
        Route::post('/check-availability', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'checkAvailability'])->name('check-availability');
        Route::post('/playback-preview', [\Modules\DOOH\Controllers\Customer\DOOHScheduleController::class, 'playbackPreview'])->name('playback-preview');
    });
});

// ============================================
// VENDOR PANEL (Authenticated)
// ============================================
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    // Dashboard (PROMPT 26)
    Route::get('/dashboard', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('dashboard');
    
    // Hoardings Management
    Route::resource('hoardings', \App\Http\Controllers\Web\Vendor\HoardingController::class);
    
    // Hoarding Media Management (PROMPT 59)
    Route::prefix('hoardings/{hoarding}/media')->name('hoardings.media.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'index'])->name('index');
        Route::post('/hero', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'uploadHero'])->name('upload-hero');
        Route::post('/night', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'uploadNight'])->name('upload-night');
        Route::post('/gallery', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'uploadGallery'])->name('upload-gallery');
        Route::post('/size-overlay', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'uploadSizeOverlay'])->name('upload-size-overlay');
        Route::delete('/hero', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'deleteHero'])->name('delete-hero');
        Route::delete('/night', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'deleteNight'])->name('delete-night');
        Route::delete('/gallery/{mediaId}', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'deleteGalleryImage'])->name('delete-gallery');
        Route::delete('/size-overlay', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'deleteSizeOverlay'])->name('delete-size-overlay');
        Route::post('/gallery/reorder', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'reorderGallery'])->name('reorder-gallery');
        Route::get('/stats', [\App\Http\Controllers\Vendor\HoardingMediaController::class, 'stats'])->name('stats');
    });
    
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
    
    // Bookings Management (PROMPT 48 - Enhanced)
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Vendor\BookingController::class, 'index'])->name('index');
        Route::get('/new', [\App\Http\Controllers\Vendor\BookingController::class, 'newBookings'])->name('new');
        Route::get('/ongoing', [\App\Http\Controllers\Vendor\BookingController::class, 'ongoingBookings'])->name('ongoing');
        Route::get('/completed', [\App\Http\Controllers\Vendor\BookingController::class, 'completedBookings'])->name('completed');
        Route::get('/cancelled', [\App\Http\Controllers\Vendor\BookingController::class, 'cancelledBookings'])->name('cancelled');
        Route::get('/{id}', [\App\Http\Controllers\Vendor\BookingController::class, 'show'])->name('show');
        Route::post('/{id}/confirm', [\App\Http\Controllers\Vendor\BookingController::class, 'confirm'])->name('confirm');
        Route::post('/{id}/cancel', [\App\Http\Controllers\Vendor\BookingController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/update-status', [\App\Http\Controllers\Vendor\BookingController::class, 'updateStatus'])->name('update-status');
    });
    Route::post('/bookings/{id}/approve-pod', [\App\Http\Controllers\Web\Vendor\BookingController::class, 'approvePOD'])->name('bookings.approve-pod');
    
    // Hoarding Availability Calendar (PROMPT 49)
    Route::get('/hoarding/{id}/calendar', [\App\Http\Controllers\Vendor\HoardingCalendarController::class, 'show'])->name('hoarding.calendar');
    Route::get('/hoarding/{id}/calendar/data', [\App\Http\Controllers\Vendor\HoardingCalendarController::class, 'getCalendarData'])->name('hoarding.calendar.data');
    Route::get('/hoarding/{id}/calendar/stats', [\App\Http\Controllers\Vendor\HoardingCalendarController::class, 'getStats'])->name('hoarding.calendar.stats');
    
    // Task Management (PROMPT 26)
    Route::get('/tasks', [\App\Http\Controllers\Vendor\TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [\App\Http\Controllers\Vendor\TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{id}', [\App\Http\Controllers\Vendor\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{id}/start', [\App\Http\Controllers\Vendor\TaskController::class, 'start'])->name('tasks.start');
    Route::post('/tasks/{id}/complete', [\App\Http\Controllers\Vendor\TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('/tasks/{id}/update-progress', [\App\Http\Controllers\Vendor\TaskController::class, 'updateProgress'])->name('tasks.update-progress');
    Route::delete('/tasks/{id}', [\App\Http\Controllers\Vendor\TaskController::class, 'destroy'])->name('tasks.destroy');
    
    // Payouts (PROMPT 26 - Basic)
    Route::get('/payouts-old', [\App\Http\Controllers\Vendor\PayoutController::class, 'index'])->name('payouts-old.index');
    Route::post('/payouts/request', [\App\Http\Controllers\Vendor\PayoutController::class, 'request'])->name('payouts-old.request');
    Route::get('/payouts-old/{id}', [\App\Http\Controllers\Vendor\PayoutController::class, 'show'])->name('payouts-old.show');
    Route::post('/payouts/update-bank', [\App\Http\Controllers\Vendor\PayoutController::class, 'updateBank'])->name('payouts.update-bank');
    
    // Payout Request System (PROMPT 58 - Advanced)
    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Vendor\PayoutRequestController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Vendor\PayoutRequestController::class, 'create'])->name('create');
        Route::post('/preview', [\App\Http\Controllers\Vendor\PayoutRequestController::class, 'preview'])->name('preview');
        Route::post('/', [\App\Http\Controllers\Vendor\PayoutRequestController::class, 'store'])->name('store');
        Route::get('/{payoutRequest}', [\App\Http\Controllers\Vendor\PayoutRequestController::class, 'show'])->name('show');
        Route::post('/{payoutRequest}/submit', [\App\Http\Controllers\Vendor\PayoutRequestController::class, 'submit'])->name('submit');
        Route::post('/{payoutRequest}/cancel', [\App\Http\Controllers\Vendor\PayoutRequestController::class, 'cancel'])->name('cancel');
        Route::get('/{payoutRequest}/download-receipt', [\App\Http\Controllers\Vendor\PayoutRequestController::class, 'downloadReceipt'])->name('download-receipt');
    });
    
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
    
    // Cancellation Policies (PROMPT 71)
    Route::prefix('cancellation-policies')->name('cancellation-policies.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Vendor\CancellationPolicyController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Vendor\CancellationPolicyController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Vendor\CancellationPolicyController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Vendor\CancellationPolicyController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Vendor\CancellationPolicyController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Vendor\CancellationPolicyController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [\App\Http\Controllers\Vendor\CancellationPolicyController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/preview-refund', [\App\Http\Controllers\Vendor\CancellationPolicyController::class, 'previewRefund'])->name('preview-refund');
    });
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
    
    // Invoice Management (PROMPT 64)
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InvoiceController::class, 'adminIndex'])->name('index');
        Route::get('/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'adminShow'])->name('show');
        Route::get('/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('download');
        Route::post('/{invoice}/cancel', [\App\Http\Controllers\InvoiceController::class, 'adminCancel'])->name('cancel');
        Route::post('/{invoice}/mark-paid', [\App\Http\Controllers\InvoiceController::class, 'adminMarkPaid'])->name('mark-paid');
        Route::post('/{invoice}/regenerate-pdf', [\App\Http\Controllers\InvoiceController::class, 'adminRegeneratePDF'])->name('regenerate-pdf');
        Route::post('/{invoice}/email', [\App\Http\Controllers\InvoiceController::class, 'sendEmail'])->name('email');
    });
    
    // DOOH Creatives & Schedules Management (PROMPT 67)
    Route::prefix('dooh')->name('dooh.')->group(function () {
        // Creatives Management
        Route::get('/creatives', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'creatives'])->name('creatives.index');
        Route::get('/creatives/{creative}', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'showCreative'])->name('creatives.show');
        Route::post('/creatives/{creative}/approve', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'approveCreative'])->name('creatives.approve');
        Route::post('/creatives/{creative}/reject', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'rejectCreative'])->name('creatives.reject');
        
        // Schedules Management
        Route::get('/schedules', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'schedules'])->name('schedules.index');
        Route::get('/schedules/{schedule}', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'showSchedule'])->name('schedules.show');
        Route::post('/schedules/{schedule}/approve', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'approveSchedule'])->name('schedules.approve');
        Route::post('/schedules/{schedule}/reject', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'rejectSchedule'])->name('schedules.reject');
        Route::post('/schedules/{schedule}/pause', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'pauseSchedule'])->name('schedules.pause');
        Route::post('/schedules/{schedule}/resume', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'resumeSchedule'])->name('schedules.resume');
        Route::post('/schedules/bulk-approve', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'bulkApprove'])->name('schedules.bulk-approve');
        Route::get('/schedules/export', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'exportSchedules'])->name('schedules.export');
        
        // Screen Calendar & Playback
        Route::get('/screens/{screen}/calendar', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'screenCalendar'])->name('screens.calendar');
        Route::get('/screens/{screen}/playback', [\Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController::class, 'dailyPlayback'])->name('screens.playback');
    });
    
    // Settings (New Enhanced Settings System - PROMPT 29)
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    
    // Geo-Fencing Settings (PROMPT 98)
    Route::get('/settings/geofencing', [\App\Http\Controllers\Web\Admin\GeofencingSettingsController::class, 'index'])->name('settings.geofencing');
    Route::put('/settings/geofencing', [\App\Http\Controllers\Web\Admin\GeofencingSettingsController::class, 'update'])->name('settings.geofencing.update');
    Route::get('/settings/geofencing/violations', [\App\Http\Controllers\Web\Admin\GeofencingSettingsController::class, 'violations'])->name('settings.geofencing.violations');
    
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
        Route::get('/{policy}/edit', [\App\Http\Controllers\Admin\RefundController::class, 'editPolicy'])->name('edit');
        Route::put('/{policy}', [\App\Http\Controllers\Admin\RefundController::class, 'updatePolicy'])->name('update');
        Route::delete('/{policy}', [\App\Http\Controllers\Admin\RefundController::class, 'destroyPolicy'])->name('destroy');
        Route::post('/{policy}/toggle-status', [\App\Http\Controllers\Admin\RefundController::class, 'togglePolicyStatus'])->name('toggle-status');
        Route::get('/vendor-policies', [\App\Http\Controllers\Admin\RefundController::class, 'vendorPolicies'])->name('vendor-policies');
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
    
    // Search Ranking Settings (PROMPT 35)
    Route::prefix('search-settings')->name('search-settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SearchSettingsController::class, 'index'])->name('index');
        Route::get('/show', [\App\Http\Controllers\Admin\SearchSettingsController::class, 'show'])->name('show');
        Route::put('/', [\App\Http\Controllers\Admin\SearchSettingsController::class, 'update'])->name('update');
        Route::post('/reset', [\App\Http\Controllers\Admin\SearchSettingsController::class, 'reset'])->name('reset');
        Route::post('/preview-score', [\App\Http\Controllers\Admin\SearchSettingsController::class, 'previewScore'])->name('preview-score');
    });
    
    // Payout Approval System (PROMPT 58)
    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'index'])->name('index');
        Route::get('/all', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'allRequests'])->name('all');
        Route::get('/{payoutRequest}', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'show'])->name('show');
        Route::post('/{payoutRequest}/approve', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'approve'])->name('approve');
        Route::post('/{payoutRequest}/reject', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'reject'])->name('reject');
        Route::post('/{payoutRequest}/process-settlement', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'processSettlement'])->name('process-settlement');
        Route::post('/{payoutRequest}/generate-receipt', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'generateReceipt'])->name('generate-receipt');
        Route::get('/{payoutRequest}/download-receipt', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'downloadReceipt'])->name('download-receipt');
        Route::post('/{payoutRequest}/regenerate-receipt', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'regenerateReceipt'])->name('regenerate-receipt');
        Route::post('/bulk-approve', [\App\Http\Controllers\Admin\PayoutApprovalController::class, 'bulkApprove'])->name('bulk-approve');
    });
    
    // Notification Templates (PROMPT 34)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Templates
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'store'])->name('store');
            Route::get('/{template}', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'show'])->name('show');
            Route::get('/{template}/edit', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/duplicate', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'duplicate'])->name('duplicate');
            Route::post('/{template}/toggle', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'toggleStatus'])->name('toggle');
            Route::post('/{template}/test', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'testSend'])->name('test');
        });
        
        // Logs
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'logs'])->name('index');
            Route::get('/{log}', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'logShow'])->name('show');
            Route::post('/{log}/retry', [\App\Http\Controllers\Admin\NotificationTemplateController::class, 'retryLog'])->name('retry');
        });
    });
    
    // Snapshot Engine (PROMPT 36)
    Route::prefix('snapshots')->name('snapshots.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SnapshotController::class, 'index'])->name('index');
        Route::get('/statistics', [\App\Http\Controllers\Admin\SnapshotController::class, 'statistics'])->name('statistics');
        Route::get('/recent', [\App\Http\Controllers\Admin\SnapshotController::class, 'recent'])->name('recent');
        Route::get('/type/{type}', [\App\Http\Controllers\Admin\SnapshotController::class, 'byType'])->name('by-type');
        Route::get('/event/{event}', [\App\Http\Controllers\Admin\SnapshotController::class, 'byEvent'])->name('by-event');
        Route::get('/for-model', [\App\Http\Controllers\Admin\SnapshotController::class, 'forModel'])->name('for-model');
        Route::post('/compare', [\App\Http\Controllers\Admin\SnapshotController::class, 'compare'])->name('compare');
        Route::get('/{snapshot}', [\App\Http\Controllers\Admin\SnapshotController::class, 'show'])->name('show');
        Route::post('/{snapshot}/restore', [\App\Http\Controllers\Admin\SnapshotController::class, 'restore'])->name('restore');
    });
    
    // Audit Trail + Logs (PROMPT 37)
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('index');
        Route::get('/statistics', [\App\Http\Controllers\Admin\AuditLogController::class, 'statistics'])->name('statistics');
        Route::get('/recent', [\App\Http\Controllers\Admin\AuditLogController::class, 'recent'])->name('recent');
        Route::get('/user/{userId}/activity', [\App\Http\Controllers\Admin\AuditLogController::class, 'userActivity'])->name('user-activity');
        Route::get('/for-model', [\App\Http\Controllers\Admin\AuditLogController::class, 'forModel'])->name('for-model');
        Route::get('/timeline', [\App\Http\Controllers\Admin\AuditLogController::class, 'timeline'])->name('timeline');
        Route::post('/search', [\App\Http\Controllers\Admin\AuditLogController::class, 'search'])->name('search');
        Route::get('/export', [\App\Http\Controllers\Admin\AuditLogController::class, 'export'])->name('export');
        Route::get('/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('show');
    });
    
    // Booking Timeline (PROMPT 38)
    Route::prefix('bookings/{booking}/timeline')->name('bookings.timeline.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'index'])->name('index');
        Route::get('/api', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'getTimeline'])->name('api');
        Route::post('/start-stage', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'startStage'])->name('start-stage');
        Route::post('/complete-stage', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'completeStage'])->name('complete-stage');
        Route::post('/add-event', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'addEvent'])->name('add-event');
        Route::post('/rebuild', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'rebuild'])->name('rebuild');
        Route::get('/progress', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'progress'])->name('progress');
        Route::get('/current-stage', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'currentStage'])->name('current-stage');
        
        // Enhanced Timeline with Notes (PROMPT 47)
        Route::post('/start-stage-with-note', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'startStageWithNote'])->name('start-stage-with-note');
        Route::post('/complete-stage-with-note', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'completeStageWithNote'])->name('complete-stage-with-note');
        Route::post('/events/{event}/add-note', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'addNote'])->name('events.add-note');
    });
    Route::put('/timeline/events/{event}', [\App\Http\Controllers\Admin\BookingTimelineController::class, 'updateEvent'])->name('timeline.events.update');
    
    // DOOH Slot Management (PROMPT 39)
    Route::prefix('hoardings/{hoarding}/dooh-slots')->name('hoarding.dooh-slots.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'store'])->name('store');
        Route::get('/booking', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'bookingView'])->name('booking');
        Route::post('/setup-defaults', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'setupDefaults'])->name('setup-defaults');
        Route::get('/availability', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'checkAvailability'])->name('availability');
    });
    
    Route::prefix('dooh-slots')->name('dooh-slots.')->group(function () {
        Route::get('/{slot}', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'show'])->name('show');
        Route::get('/{slot}/edit', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'edit'])->name('edit');
        Route::put('/{slot}', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'update'])->name('update');
        Route::delete('/{slot}', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'destroy'])->name('destroy');
        Route::post('/{slot}/release', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'release'])->name('release');
        Route::post('/{slot}/block', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'block'])->name('block');
        Route::post('/{slot}/maintenance', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'maintenance'])->name('maintenance');
        Route::get('/{slot}/schedule', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'getDailySchedule'])->name('schedule');
        Route::get('/{slot}/metrics', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'getMetrics'])->name('metrics');
    });
    
    // DOOH Booking & Calculation APIs
    Route::post('/dooh/calculate-cost', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'calculateCost'])->name('dooh.calculate-cost');
    Route::post('/dooh/book-slots', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'book'])->name('dooh.book-slots');
    Route::post('/dooh/calculate-frequency', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'calculateFrequency'])->name('dooh.calculate-frequency');
    Route::post('/dooh/optimize-budget', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'optimizeForBudget'])->name('dooh.optimize-budget');
    Route::get('/dooh/roi-calculator', [\App\Http\Controllers\Admin\DOOHSlotController::class, 'roiCalculator'])->name('dooh.roi-calculator');
    
    // Booking Rules
    Route::get('/booking-rules', [\App\Http\Controllers\Web\Admin\BookingRuleController::class, 'index'])->name('booking-rules.index');
    Route::put('/booking-rules', [\App\Http\Controllers\Web\Admin\BookingRuleController::class, 'update'])->name('booking-rules.update');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\Web\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue', [\App\Http\Controllers\Web\Admin\ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/vendors', [\App\Http\Controllers\Web\Admin\ReportController::class, 'vendors'])->name('reports.vendors');
    
    // Activity Log
    Route::get('/activity-log', [\App\Http\Controllers\Web\Admin\ActivityLogController::class, 'index'])->name('activity-log.index');
    
    // Revenue Dashboard & Analytics (PROMPT 74)
    Route::prefix('revenue')->name('revenue.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\RevenueController::class, 'dashboard'])->name('dashboard');
        Route::get('/vendor-revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'vendorRevenue'])->name('vendor-revenue');
        Route::get('/location-revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'locationRevenue'])->name('location-revenue');
        Route::get('/commission-analytics', [\App\Http\Controllers\Admin\RevenueController::class, 'commissionAnalytics'])->name('commission-analytics');
        Route::get('/payout-management', [\App\Http\Controllers\Admin\RevenueController::class, 'payoutManagement'])->name('payout-management');
        Route::get('/export', [\App\Http\Controllers\Admin\RevenueController::class, 'export'])->name('export');
    });
    
    // Hoarding Approval Workflow (PROMPT 78)
    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'show'])->name('show');
        Route::post('/{id}/start-verification', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'startVerification'])->name('start-verification');
        Route::post('/{id}/checklist', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'updateChecklist'])->name('update-checklist');
        Route::post('/{id}/approve', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'reject'])->name('reject');
        Route::get('/{id}/versions/{version1}/{version2}', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'compareVersions'])->name('compare-versions');
        Route::post('/bulk-approve', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/{id}/assign', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'assign'])->name('assign');
        Route::get('/export', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'export'])->name('export');
        
        // Templates & Settings
        Route::get('/templates/manage', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'templates'])->name('templates');
        Route::post('/templates', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'storeTemplate'])->name('templates.store');
        Route::get('/settings/manage', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\HoardingApprovalController::class, 'saveSettings'])->name('settings.save');
    });
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

