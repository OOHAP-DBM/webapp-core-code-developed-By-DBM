<?php

use App\Http\Controllers\Api\V1\VendorQuoteController;
use App\Http\Controllers\Api\V1\QuoteRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor Quote Routes
|--------------------------------------------------------------------------
*/

Route::prefix('vendor-quotes')->middleware('auth:sanctum')->group(function () {
    // Vendor routes
    Route::get('/', [VendorQuoteController::class, 'index'])->name('vendor-quotes.index');
    Route::post('/from-request', [VendorQuoteController::class, 'createFromRequest'])->name('vendor-quotes.from-request');
    Route::post('/from-enquiry', [VendorQuoteController::class, 'createFromEnquiry'])->name('vendor-quotes.from-enquiry');
    Route::put('/{id}', [VendorQuoteController::class, 'update'])->name('vendor-quotes.update');
    Route::post('/{id}/send', [VendorQuoteController::class, 'send'])->name('vendor-quotes.send');
    Route::post('/{id}/revise', [VendorQuoteController::class, 'revise'])->name('vendor-quotes.revise');
    
    // Customer routes
    Route::get('/customer', [VendorQuoteController::class, 'customerQuotes'])->name('vendor-quotes.customer');
    Route::post('/{id}/accept', [VendorQuoteController::class, 'accept'])->name('vendor-quotes.accept');
    Route::post('/{id}/reject', [VendorQuoteController::class, 'reject'])->name('vendor-quotes.reject');
    
    // Common routes
    Route::get('/{id}', [VendorQuoteController::class, 'show'])->name('vendor-quotes.show');
    Route::get('/{id}/pdf', [VendorQuoteController::class, 'downloadPdf'])->name('vendor-quotes.pdf');
    
    // Utility routes
    Route::post('/calculate-pricing', [VendorQuoteController::class, 'calculatePricing'])->name('vendor-quotes.calculate');
});

/*
|--------------------------------------------------------------------------
| Quote Request Routes
|--------------------------------------------------------------------------
*/

Route::prefix('quote-requests')->middleware('auth:sanctum')->group(function () {
    // Customer routes
    Route::get('/', [QuoteRequestController::class, 'index'])->name('quote-requests.index');
    Route::post('/', [QuoteRequestController::class, 'store'])->name('quote-requests.store');
    Route::get('/{id}', [QuoteRequestController::class, 'show'])->name('quote-requests.show');
    Route::put('/{id}', [QuoteRequestController::class, 'update'])->name('quote-requests.update');
    Route::post('/{id}/publish', [QuoteRequestController::class, 'publish'])->name('quote-requests.publish');
    Route::get('/{id}/comparison', [QuoteRequestController::class, 'comparison'])->name('quote-requests.comparison');
    Route::post('/{id}/accept-quote', [QuoteRequestController::class, 'acceptQuote'])->name('quote-requests.accept-quote');
    Route::post('/{id}/close', [QuoteRequestController::class, 'close'])->name('quote-requests.close');
    Route::post('/{id}/cancel', [QuoteRequestController::class, 'cancel'])->name('quote-requests.cancel');
    
    // Vendor routes
    Route::get('/vendor/pending', [QuoteRequestController::class, 'pendingForVendor'])->name('quote-requests.vendor-pending');
});
