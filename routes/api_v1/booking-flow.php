<?php

use App\Http\Controllers\Customer\BookingFlowController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Hoarding-First Booking Flow API Routes
|--------------------------------------------------------------------------
|
| These routes handle the complete customer booking flow from hoarding
| selection through payment completion
|
*/

Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    
    // Step 1: Get hoarding details with availability
    Route::get('/booking/hoarding/{id}', [BookingFlowController::class, 'getHoardingDetails']);
    
    // Step 2: Get available packages
    Route::get('/booking/hoarding/{id}/packages', [BookingFlowController::class, 'getPackages']);
    
    // Step 3: Validate date selection
    Route::post('/booking/validate-dates', [BookingFlowController::class, 'validateDates']);
    
    // Step 4: Create/Update draft booking
    Route::post('/booking/draft', [BookingFlowController::class, 'createOrUpdateDraft']);
    Route::get('/booking/draft/{id}', [BookingFlowController::class, 'getDraft']);
    Route::delete('/booking/draft/{id}', [BookingFlowController::class, 'deleteDraft']);
    
    // Get customer's active drafts
    Route::get('/booking/my-drafts', [BookingFlowController::class, 'getMyDrafts']);
    
    // Step 5: Review summary
    Route::get('/booking/draft/{id}/review', [BookingFlowController::class, 'getReviewSummary']);
    
    // Step 6: Confirm booking & lock inventory
    Route::post('/booking/draft/{id}/confirm', [BookingFlowController::class, 'confirmBooking']);
    
    // Step 7: Create payment session
    Route::post('/booking/{id}/create-payment', [BookingFlowController::class, 'createPaymentSession']);
    
    // Step 8: Payment callbacks
    Route::post('/booking/payment/callback', [BookingFlowController::class, 'handlePaymentCallback']);
    Route::post('/booking/payment/failed', [BookingFlowController::class, 'handlePaymentFailure']);
});
