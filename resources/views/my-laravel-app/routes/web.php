<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add other routes here as needed

// Custom 404 error handling
Route::fallback(function () {
    abort(404);
});