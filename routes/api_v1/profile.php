<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\ProfileController;

//Customer Profile Crud @Aviral
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::delete('/remove-avatar', [ProfileController::class, 'removeAvatar']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);

    Route::middleware(['throttle:otp'])->group(function () {
        Route::post('/send-otp', [ProfileController::class, 'sendOtp']);
        Route::post('/verify-otp', [ProfileController::class, 'verifyOtp']);
    });
    Route::prefix('customer')->group(function () {   
        Route::get('/show', [ProfileController::class, 'show']);
        Route::post('/update', [ProfileController::class, 'update']);
       
        });
   
});
