<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware(['api'])
    ->namespace('Modules\\Offers\\Http\\Controllers')
    ->group(function () {
        // Vendor routes
        Route::middleware(['auth:sanctum', 'role:vendor'])->group(function () {
            Route::post('/', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'store']);
            Route::get('/', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'index']);
            Route::get('/{id}', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'show']);
            Route::put('/{id}', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'update']);
            Route::post('/{id}/withdraw', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'withdraw']);
        });
        // Customer routes
        Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
            Route::get('/enquiry/{enquiryId}/offers', [\Modules\Offers\Http\Controllers\Api\OfferController::class, 'getOffersByEnquiry']);
        });
    });
