<?php

use Illuminate\Support\Facades\Route;
use Modules\Threads\Controllers\Api\ThreadController;
use Modules\Enquiries\Controllers\Api\EnquiryWorkflowController;

/*
|--------------------------------------------------------------------------
| Enquiry → Offer → Quotation Workflow API Routes
|--------------------------------------------------------------------------
*/

// Customer Routes
Route::middleware(['auth:sanctum'])->prefix('customer')->group(function () {
    
    // Enquiries
    Route::get('/enquiries', [EnquiryWorkflowController::class, 'getCustomerEnquiries']);
    Route::post('/enquiries', [EnquiryWorkflowController::class, 'store']);
    Route::get('/enquiries/{id}', [EnquiryWorkflowController::class, 'show']);
    Route::post('/enquiries/{id}/cancel', [EnquiryWorkflowController::class, 'cancel']);
    
    // Threads
    Route::get('/threads', [ThreadController::class, 'getCustomerThreads']);
    Route::get('/threads/{id}', [ThreadController::class, 'show']);
    Route::post('/threads/{id}/messages', [ThreadController::class, 'sendMessage']);
    
    // Inline Offer Actions (in thread)
    Route::post('/threads/{threadId}/offers/{offerId}/accept', [ThreadController::class, 'acceptOfferInline']);
    Route::post('/threads/{threadId}/offers/{offerId}/reject', [ThreadController::class, 'rejectOfferInline']);
    
    // Inline Quotation Actions (in thread)
    Route::post('/threads/{threadId}/quotations/{quotationId}/approve', [ThreadController::class, 'approveQuotationInline']);
    Route::post('/threads/{threadId}/quotations/{quotationId}/reject', [ThreadController::class, 'rejectQuotationInline']);
});

// Vendor Routes
Route::middleware(['auth:sanctum'])->prefix('vendor')->group(function () {
    
    // Enquiries
    Route::get('/enquiries', [EnquiryWorkflowController::class, 'getVendorEnquiries']);
    Route::get('/enquiries/{id}', [EnquiryWorkflowController::class, 'show']);
    
    // Threads
    Route::get('/threads', [ThreadController::class, 'getVendorThreads']);
    Route::get('/threads/{id}', [ThreadController::class, 'show']);
    Route::post('/threads/{id}/messages', [ThreadController::class, 'sendMessage']);
    
    // Inline Offer Creation (in thread)
    Route::post('/threads/{threadId}/offers/create', [ThreadController::class, 'createOfferInline']);
    
    // Inline Quotation Creation (in thread)
    Route::post('/threads/{threadId}/quotations/create', [ThreadController::class, 'createQuotationInline']);
});
