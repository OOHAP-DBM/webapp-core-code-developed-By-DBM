<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PageController;

Route::get('/about', [PageController::class, 'about'])->name('api.pages.about');
Route::get('/faqs', [PageController::class, 'faqs'])->name('api.pages.faqs');
Route::get('/terms', [PageController::class, 'terms'])->name('api.pages.terms');
Route::get('/disclaimer', [PageController::class, 'disclaimer'])->name('api.pages.disclaimer');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('api.pages.privacy');
Route::get('/refund-policy', [PageController::class, 'refund'])->name('api.pages.refund');

