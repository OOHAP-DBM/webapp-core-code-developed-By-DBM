<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Controllers\Api\CartController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/add', [CartController::class, 'add']);
});
