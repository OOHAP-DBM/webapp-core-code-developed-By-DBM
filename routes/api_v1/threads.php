<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\ThreadController as CustomerThreadController;
use App\Http\Controllers\Vendor\ThreadController as VendorThreadController;

/*
|--------------------------------------------------------------------------
| Thread API Routes (v1)
|--------------------------------------------------------------------------
|
| Universal messaging thread system for enquiries, offers, quotations, bookings
| Supports: Text messages, Attachments, System events, Read receipts
|
*/

// Customer Thread Routes
Route::middleware(['auth:sanctum', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/threads', [CustomerThreadController::class, 'index'])->name('threads.index');
    Route::get('/threads/{id}', [CustomerThreadController::class, 'show'])->name('threads.show');
    Route::post('/threads/{id}/send-message', [CustomerThreadController::class, 'sendMessage'])->name('threads.send-message');
    Route::post('/threads/{id}/mark-read', [CustomerThreadController::class, 'markAsRead'])->name('threads.mark-read');
    Route::post('/threads/{id}/archive', [CustomerThreadController::class, 'archive'])->name('threads.archive');
    Route::get('/threads/unread-count', [CustomerThreadController::class, 'unreadCount'])->name('threads.unread-count');
});

// Vendor Thread Routes
Route::middleware(['auth:sanctum', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/threads', [VendorThreadController::class, 'index'])->name('threads.index');
    Route::get('/threads/{id}', [VendorThreadController::class, 'show'])->name('threads.show');
    Route::post('/threads/{id}/send-message', [VendorThreadController::class, 'sendMessage'])->name('threads.send-message');
    Route::post('/threads/{id}/mark-read', [VendorThreadController::class, 'markAsRead'])->name('threads.mark-read');
    Route::post('/threads/{id}/archive', [VendorThreadController::class, 'archive'])->name('threads.archive');
    Route::get('/threads/unread-count', [VendorThreadController::class, 'unreadCount'])->name('threads.unread-count');
});
