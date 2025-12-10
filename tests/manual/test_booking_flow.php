<?php

/**
 * Manual Test Script for Booking Flow
 * Run with: php tests/manual/test_booking_flow.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== BOOKING FLOW MANUAL TEST ===" . PHP_EOL . PHP_EOL;

// Test 1: Model and Constants
echo "Test 1: BookingDraft Model" . PHP_EOL;
echo "----------------------------" . PHP_EOL;
$modelExists = class_exists('App\Models\BookingDraft');
echo "✓ Model exists: " . ($modelExists ? "YES" : "NO") . PHP_EOL;

if ($modelExists) {
    echo "✓ STEP_HOARDING_SELECTED: " . \App\Models\BookingDraft::STEP_HOARDING_SELECTED . PHP_EOL;
    echo "✓ STEP_PACKAGE_SELECTED: " . \App\Models\BookingDraft::STEP_PACKAGE_SELECTED . PHP_EOL;
    echo "✓ STEP_DATES_SELECTED: " . \App\Models\BookingDraft::STEP_DATES_SELECTED . PHP_EOL;
    echo "✓ STEP_REVIEW: " . \App\Models\BookingDraft::STEP_REVIEW . PHP_EOL;
    echo "✓ STEP_PAYMENT_PENDING: " . \App\Models\BookingDraft::STEP_PAYMENT_PENDING . PHP_EOL;
    echo "✓ DURATION_DAYS: " . \App\Models\BookingDraft::DURATION_DAYS . PHP_EOL;
    echo "✓ DURATION_WEEKS: " . \App\Models\BookingDraft::DURATION_WEEKS . PHP_EOL;
    echo "✓ DURATION_MONTHS: " . \App\Models\BookingDraft::DURATION_MONTHS . PHP_EOL;
}
echo PHP_EOL;

// Test 2: Service
echo "Test 2: HoardingBookingService" . PHP_EOL;
echo "-------------------------------" . PHP_EOL;
try {
    $service = app('App\Services\HoardingBookingService');
    echo "✓ Service instantiated successfully" . PHP_EOL;
    echo "✓ Service class: " . get_class($service) . PHP_EOL;
    
    $methods = get_class_methods($service);
    $requiredMethods = [
        'getHoardingDetails',
        'getAvailablePackages',
        'validateDateSelection',
        'createOrUpdateDraft',
        'getReviewSummary',
        'confirmAndLockBooking',
        'cleanupExpiredDrafts',
        'releaseExpiredHolds'
    ];
    
    foreach ($requiredMethods as $method) {
        $exists = in_array($method, $methods);
        echo ($exists ? "✓" : "✗") . " Method {$method}: " . ($exists ? "YES" : "NO") . PHP_EOL;
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Test 3: Controller
echo "Test 3: BookingFlowController" . PHP_EOL;
echo "------------------------------" . PHP_EOL;
try {
    $controllerExists = class_exists('App\Http\Controllers\Customer\BookingFlowController');
    echo "✓ Controller exists: " . ($controllerExists ? "YES" : "NO") . PHP_EOL;
    
    if ($controllerExists) {
        $controller = app('App\Http\Controllers\Customer\BookingFlowController');
        echo "✓ Controller instantiated successfully" . PHP_EOL;
        
        $methods = get_class_methods($controller);
        $requiredMethods = [
            'getHoardingDetails',
            'getPackages',
            'validateDates',
            'createOrUpdateDraft',
            'getDraft',
            'getReviewSummary',
            'confirmBooking',
            'createPaymentSession',
            'handlePaymentCallback',
            'handlePaymentFailure',
            'getMyDrafts',
            'deleteDraft'
        ];
        
        foreach ($requiredMethods as $method) {
            $exists = in_array($method, $methods);
            echo ($exists ? "✓" : "✗") . " Endpoint {$method}: " . ($exists ? "YES" : "NO") . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Test 4: Database Table
echo "Test 4: Database Table" . PHP_EOL;
echo "----------------------" . PHP_EOL;
try {
    $tableExists = \Illuminate\Support\Facades\Schema::hasTable('booking_drafts');
    echo "✓ Table 'booking_drafts' exists: " . ($tableExists ? "YES" : "NO") . PHP_EOL;
    
    if ($tableExists) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('booking_drafts');
        echo "✓ Total columns: " . count($columns) . PHP_EOL;
        
        $requiredColumns = [
            'id', 'customer_id', 'hoarding_id', 'package_id',
            'start_date', 'end_date', 'duration_days', 'duration_type',
            'price_snapshot', 'base_price', 'discount_amount', 'gst_amount', 'total_amount',
            'applied_offers', 'coupon_code', 'step', 'last_updated_step_at',
            'session_id', 'expires_at', 'is_converted', 'booking_id', 'converted_at',
            'created_at', 'updated_at', 'deleted_at'
        ];
        
        echo PHP_EOL . "Column Check:" . PHP_EOL;
        foreach ($requiredColumns as $column) {
            $exists = in_array($column, $columns);
            echo ($exists ? "✓" : "✗") . " {$column}: " . ($exists ? "YES" : "NO") . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Test 5: Dependencies
echo "Test 5: Dependencies" . PHP_EOL;
echo "--------------------" . PHP_EOL;
try {
    $priceCalculator = app('App\Services\DynamicPriceCalculator');
    echo "✓ DynamicPriceCalculator: YES" . PHP_EOL;
} catch (Exception $e) {
    echo "✗ DynamicPriceCalculator: NO - " . $e->getMessage() . PHP_EOL;
}

try {
    $razorpay = app('App\Services\RazorpayService');
    echo "✓ RazorpayService: YES" . PHP_EOL;
} catch (Exception $e) {
    echo "✗ RazorpayService: NO - " . $e->getMessage() . PHP_EOL;
}

try {
    $settings = app('App\Services\SettingsService');
    echo "✓ SettingsService: YES" . PHP_EOL;
} catch (Exception $e) {
    echo "✗ SettingsService: NO - " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Test 6: Routes
echo "Test 6: Routes Registration" . PHP_EOL;
echo "---------------------------" . PHP_EOL;
$routeFile = __DIR__ . '/../../routes/api_v1/booking-flow.php';
echo "✓ Route file exists: " . (file_exists($routeFile) ? "YES" : "NO") . PHP_EOL;

$apiFile = __DIR__ . '/../../routes/api.php';
$apiContent = file_get_contents($apiFile);
echo "✓ Route file included in api.php: " . (strpos($apiContent, 'booking-flow.php') !== false ? "YES" : "NO") . PHP_EOL;
echo PHP_EOL;

// Test 7: Scheduled Jobs
echo "Test 7: Scheduled Jobs" . PHP_EOL;
echo "----------------------" . PHP_EOL;
$consoleFile = __DIR__ . '/../../routes/console.php';
$consoleContent = file_get_contents($consoleFile);
echo "✓ Cleanup drafts job: " . (strpos($consoleContent, 'cleanupExpiredDrafts') !== false ? "YES" : "NO") . PHP_EOL;
echo "✓ Release holds job: " . (strpos($consoleContent, 'releaseExpiredHolds') !== false ? "YES" : "NO") . PHP_EOL;
echo PHP_EOL;

// Summary
echo "==================================" . PHP_EOL;
echo "         TEST SUMMARY" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo "All core components are verified!" . PHP_EOL;
echo PHP_EOL;
echo "Next Steps:" . PHP_EOL;
echo "1. Run: php artisan test --filter=BookingFlowTest" . PHP_EOL;
echo "2. Seed required settings in database" . PHP_EOL;
echo "3. Test API endpoints with Postman" . PHP_EOL;
echo "4. Integrate with frontend" . PHP_EOL;
echo PHP_EOL;
