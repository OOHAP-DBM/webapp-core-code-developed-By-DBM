<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\ProfileController;

//Customer Profile Crud @Aviral
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {

    Route::get('/', [ProfileController::class, 'show']);
    Route::post('/', [ProfileController::class, 'update']);
    Route::delete('/avatar', [ProfileController::class, 'removeAvatar']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);

    Route::middleware(['throttle:otp'])->group(function () {
        Route::post('/otp/send', [ProfileController::class, 'sendOtp']);
        Route::post('/otp/verify', [ProfileController::class, 'verifyOtp']);
    });
});
