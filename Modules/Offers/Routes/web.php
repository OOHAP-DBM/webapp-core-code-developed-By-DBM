<?php

use Illuminate\Support\Facades\Route;
use Modules\Offers\Http\Controllers\Web\OfferController;

Route::middleware(['web', 'auth'])
    ->prefix('vendor/offers')
    ->name('vendor.offers.')
    ->group(function () {

        Route::get('/create', [OfferController::class, 'create'])->name('create');
        Route::post('/', [OfferController::class, 'store'])->name('store');
        Route::get('/{offer}', [OfferController::class, 'show'])->name('show');
        Route::post('/{offer}/send', [OfferController::class, 'send'])->name('send');
        Route::delete('/{offer}', [OfferController::class, 'destroy'])->name('destroy');
        Route::get('/customer-suggestions', [OfferController::class, 'customerSuggestions'])->name('customer-suggestions');
});

// Customer "myOffers" route (keep outside vendor prefix)
Route::middleware(['web'])
    ->get('/offers', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'myOffers'])->name('offers');
