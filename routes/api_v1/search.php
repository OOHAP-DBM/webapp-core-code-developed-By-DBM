<?php

use Illuminate\Support\Facades\Route;

/**
 * Search API Routes (v1)
 * Base: /api/v1/search
 * 
 * Global search across hoardings, DOOH, vendors, locations
 */

// Public search
Route::get('/', [\Modules\Search\Controllers\Api\SearchController::class, 'search']);
Route::get('/suggestions', [\Modules\Search\Controllers\Api\SearchController::class, 'suggestions']);
Route::get('/filters', [\Modules\Search\Controllers\Api\SearchController::class, 'availableFilters']);
Route::get('/locations', [\Modules\Search\Controllers\Api\SearchController::class, 'locations']);

// Advanced search (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/advanced', [\Modules\Search\Controllers\Api\SearchController::class, 'advancedSearch']);
    Route::post('/save-search', [\Modules\Search\Controllers\Api\SearchController::class, 'saveSearch']);
    Route::get('/saved-searches', [\Modules\Search\Controllers\Api\SearchController::class, 'savedSearches']);
});
