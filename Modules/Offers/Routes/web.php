<?php

use Illuminate\Support\Facades\Route;
use Modules\Offers\Http\Controllers\Web\OfferController;


Route::middleware(['auth'])->prefix('vendor/offers')->name('vendor.offers.')->group(function () {
 
    // Show create form  (?enquiry_id= optional)
    Route::get('/create',   [OfferController::class, 'create'])->name('create');
 
    // Store new draft offer (AJAX POST)
    Route::post('/', [OfferController::class, 'store'])->name('store');
 
    // Show single offer
    Route::get('/{offer}', [OfferController::class, 'show'])->name('show');
 
    // Send draft → sent
    Route::post('/{offer}/send',[OfferController::class, 'send'])->name('send');
 
    // Delete draft
    Route::delete('/{offer}',[OfferController::class, 'destroy'])->name('destroy');
 
    // Customer search autocomplete
    Route::get('/customer-suggestions', [OfferController::class, 'customerSuggestions'])
         ->name('customer-suggestions');
});


// Customer "myOffers" route (keep outside vendor prefix)
Route::middleware(['web'])
    ->get('/offers', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'myOffers'])->name('offers');
