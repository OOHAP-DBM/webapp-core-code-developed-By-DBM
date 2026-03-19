<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Controllers\Api\CartController;

// Guest + Logged in dono ke liye
Route::get('count', [CartController::class, 'count']);
Route::get('list', [CartController::class, 'list']);

// Sirf logged in ke liye
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('add', [CartController::class, 'add']);
    Route::delete('remove/{hoardingId}', [CartController::class, 'remove']);
});
