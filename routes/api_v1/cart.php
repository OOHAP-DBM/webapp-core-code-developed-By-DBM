<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Controllers\Api\CartController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('add', [CartController::class, 'add']);
    Route::delete('remove/{hoardingId}', [CartController::class, 'remove']);
    Route::get('count', [CartController::class, 'count']);
    Route::get('list', [CartController::class, 'list']);
});
