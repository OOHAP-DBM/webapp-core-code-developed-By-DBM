<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RazorpayWebhookController;
use App\Http\Controllers\Api\Customer\ShortlistController;
use App\Http\Controllers\Api\Vendor\DashboardController;  
use App\Http\Controllers\Api\Customer\CustomerHomeController;
    use Modules\Enquiries\Controllers\Api\DirectEnquiryApiController;
/**
 * OOHAPP API v1 Routes
 * 
 * All API v1 endpoints are prefixed with /api/v1
 * Module-specific routes are loaded from routes/api_v1/ directory
 * Rate limiting applied based on endpoint sensitivity
 */

// Razorpay Webhook (No auth middleware - verified via signature, rate limited)
Route::middleware(['throttle:webhooks'])->group(function () {
    Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle']);
});



Route::middleware(['auth:sanctum'])
    ->prefix('v1/wishlist')
    ->group(function () {

        Route::get('/', [ShortlistController::class, 'index']);          // Get wishlist
        Route::post('/{hoardingId}', [ShortlistController::class, 'store']); // Add
        Route::delete('/{hoardingId}', [ShortlistController::class, 'destroy']); // Remove
        Route::delete('/', [ShortlistController::class, 'clear']);       // Clear all

        Route::post('/toggle/{hoardingId}', [ShortlistController::class, 'toggle']);
        Route::get('/check/{hoardingId}', [ShortlistController::class, 'check']);
        Route::get('/count', [ShortlistController::class, 'count']);
});


Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    
    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0'
        ]);
    });

    Route::middleware(['auth:sanctum', 'role:customer'])->prefix('customer')->group(function () {
        Route::get('/home', [CustomerHomeController::class, 'index']);
    });
    Route::middleware(['auth:sanctum', 'role:vendor'])->prefix('vendor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });
    // Load module-specific API routes
    Route::prefix('auth')->group(base_path('routes/api_v1/auth.php'));
    Route::prefix('profile')->group(base_path('routes/api_v1/profile.php'));
    Route::prefix('hoardings')->group(base_path('routes/api_v1/hoardings.php'));
    Route::prefix('cart')->group(base_path('routes/api_v1/cart.php'));
    Route::prefix('dooh')->group(base_path('routes/api_v1/dooh.php'));
    Route::prefix('enquiries')->group(base_path('routes/api_v1/enquiries.php'));
    Route::prefix('offers')->group(base_path('routes/api_v1/offers.php'));
    Route::prefix('quotations')->group(base_path('routes/api_v1/quotations.php'));
    Route::prefix('bookings')->group(base_path('routes/api_v1/bookings.php'));
    Route::prefix('bookings-v2')->group(base_path('routes/api_v1/bookings_v2.php')); // New Bookings Module
    Route::prefix('payments')->group(base_path('routes/api_v1/payments.php'));
    Route::prefix('vendors')->group(base_path('routes/api_v1/vendors.php'));
    Route::prefix('kyc')->group(base_path('routes/api_v1/kyc.php'));
    Route::prefix('staff')->group(base_path('routes/api_v1/staff.php'));
    Route::prefix('admin')->group(base_path('routes/api_v1/admin.php'));
    Route::prefix('settings')->group(base_path('routes/api_v1/settings.php'));
    Route::prefix('notifications')->group(base_path('routes/api_v1/notifications.php'));
    Route::prefix('vendor/notifications')->group(base_path('routes/api_v1/vendor_notifications.php'));
    Route::prefix('agency-notifications')->group(base_path('routes/api_v1/agency_notifications.php'));
    Route::prefix('brand-manager-notifications')->group(base_path('routes/api_v1/brand_manager_notifications.php'));
    Route::prefix('reports')->group(base_path('routes/api_v1/reports.php'));
    Route::prefix('media')->group(base_path('routes/api_v1/media.php'));
    Route::prefix('search')->group(base_path('routes/api_v1/search.php'));
    Route::prefix('vendor/pos')->group(base_path('routes/api_v1/pos.php')); // POS Module
    Route::prefix('pages')->group(base_path('routes/api_v1/cms.php')); // CMS Module



    
    // Thread Communication System (PROMPT 28)
    require base_path('routes/api_v1/threads.php');
    
    // Direct Booking Module (Customer direct bookings without quotation)
    require base_path('routes/api_v1/direct-bookings.php');
    
    // Enquiry Workflow Module (Enquiry → Offer → Quotation with Thread Communication)
    require base_path('routes/api_v1/enquiry-workflow.php');
    
    // Hoarding-First Booking Flow (PROMPT 43 - Customer direct booking from hoarding)
    require base_path('routes/api_v1/booking-flow.php');
    
    // Vendor Quote & RFP System (PROMPT 44 & 45 - Quote generation and RFP workflow)
    require base_path('routes/api_v1/vendor-quotes.php');
    
    // Milestone Payment System (PROMPT 70 - Vendor-controlled milestone payments)
    require base_path('routes/api_v1/milestone_payments.php');
    
    // Booking Overlap Validation Engine (PROMPT 101 - Check date conflicts & availability)
    Route::prefix('booking-overlap')->group(base_path('routes/api_v1/booking_overlap.php'));
    
    // Maintenance Blocks (PROMPT 102 - Admin/Vendor blocking periods for maintenance/repairs)
    Route::prefix('maintenance-blocks')->group(base_path('routes/api_v1/maintenance_blocks.php'));
    
    // Hoarding Availability Calendar API (PROMPT 104 - Frontend calendar with availability status)
    require base_path('routes/api_v1/hoarding_availability.php');

    Route::prefix('enquiries')->group(function () {
        // Generate captcha (stateless, mobile-friendly)
        Route::get('captcha', [DirectEnquiryApiController::class, 'generateCaptcha']);

        // List all direct enquiries (admin)
        Route::get('direct', [DirectEnquiryApiController::class, 'index']);

        // Send OTP for direct enquiry
        Route::post('direct/send-otp', [DirectEnquiryApiController::class, 'sendOtp']);

        // Verify OTP for direct enquiry
        Route::post('direct/verify-otp', [DirectEnquiryApiController::class, 'verifyOtp']);

        // Store new direct enquiry
        Route::post('direct', [DirectEnquiryApiController::class, 'store']);
    });
});
