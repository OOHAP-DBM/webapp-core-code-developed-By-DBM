<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use App\Models\SearchRankingSetting;
use App\Services\GeoSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapSearchController extends Controller
{
    protected GeoSearchService $geoSearch;

    public function __construct(GeoSearchService $geoSearch)
    {
        $this->geoSearch = $geoSearch;
    }

    /**
     * Show map search interface
     */
    public function index()
    {
        $settings = SearchRankingSetting::current();

        return view('search.map', [
            'settings' => $settings,
            'savedSearches' => Auth::check() 
                ? Auth::user()->savedSearches()->latest()->take(5)->get()
                : [],
        ]);
    }

    /**
     * Search hoardings by location
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_km' => 'nullable|integer|min:1|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'property_type' => 'nullable|string',
            'min_size' => 'nullable|numeric|min:0',
            'max_size' => 'nullable|numeric|min:0',
            'availability' => 'nullable|string',
            'vendor_id' => 'nullable|integer|exists:users,id',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'city' => 'nullable|string',
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
        ]);

        $results = $this->geoSearch->searchByLocation(
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
            $validated['radius_km'] ?? null,
            [
                'min_price' => $validated['min_price'] ?? null,
                'max_price' => $validated['max_price'] ?? null,
                'property_type' => $validated['property_type'] ?? null,
                'min_size' => $validated['min_size'] ?? null,
                'max_size' => $validated['max_size'] ?? null,
                'availability' => $validated['availability'] ?? null,
                'vendor_id' => $validated['vendor_id'] ?? null,
                'min_rating' => $validated['min_rating'] ?? null,
                'city' => $validated['city'] ?? null,
                'search' => $validated['search'] ?? null,
            ],
            $validated['page'] ?? 1
        );

        // Get map bounds
        $bounds = $this->geoSearch->getMapBounds($results['results']);

        return response()->json([
            'success' => true,
            'data' => $results['results'],
            'meta' => [
                'total' => $results['total'],
                'page' => $results['page'],
                'per_page' => $results['per_page'],
                'total_pages' => $results['total_pages'],
                'search_params' => $results['search_params'],
                'bounds' => $bounds,
            ],
        ]);
    }

    /**
     * Get nearby hoardings
     */
    public function nearby(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $hoardings = $this->geoSearch->getNearby(
            $validated['latitude'],
            $validated['longitude'],
            $validated['limit'] ?? 10
        );

        return response()->json([
            'success' => true,
            'data' => $hoardings,
        ]);
    }

    /**
     * Autocomplete location search
     */
    public function autocomplete(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $results = $this->geoSearch->autocomplete(
            $validated['query'],
            $validated['limit'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Save search
     */
    public function saveSearch(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            'radius_km' => 'nullable|integer|min:1|max:100',
            'filters' => 'nullable|array',
            'notify_new_results' => 'nullable|boolean',
        ]);

        $search = Auth::user()->savedSearches()->create([
            'name' => $validated['name'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'location_name' => $validated['location_name'] ?? null,
            'radius_km' => $validated['radius_km'] ?? 10,
            'filters' => $validated['filters'] ?? [],
            'notify_new_results' => $validated['notify_new_results'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Search saved successfully',
            'data' => $search,
        ]);
    }

    /**
     * Get saved searches
     */
    public function getSavedSearches()
    {
        $searches = Auth::user()
            ->savedSearches()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $searches,
        ]);
    }

    /**
     * Execute saved search
     */
    public function executeSavedSearch(SavedSearch $savedSearch)
    {
        // Check ownership
        if ($savedSearch->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Execute search
        $results = $this->geoSearch->searchByLocation(
            $savedSearch->latitude,
            $savedSearch->longitude,
            $savedSearch->radius_km,
            $savedSearch->filters,
            1
        );

        // Update execution stats
        $savedSearch->markExecuted($results['total']);

        return response()->json([
            'success' => true,
            'data' => $results['results'],
            'meta' => [
                'total' => $results['total'],
                'page' => $results['page'],
                'per_page' => $results['per_page'],
                'total_pages' => $results['total_pages'],
                'search_params' => $results['search_params'],
            ],
        ]);
    }

    /**
     * Delete saved search
     */
    public function deleteSavedSearch(SavedSearch $savedSearch)
    {
        // Check ownership
        if ($savedSearch->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $savedSearch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Search deleted successfully',
        ]);
    }

    /**
     * Convert search results to GeoJSON format
     */
    protected function toGeoJSON(array $hoardings): array
    {
        $features = [];

        foreach ($hoardings as $hoarding) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        $hoarding['longitude'],
                        $hoarding['latitude'],
                    ],
                ],
                'properties' => [
                    'id' => $hoarding['id'],
                    'name' => $hoarding['name'],
                    'address' => $hoarding['address'],
                    'city' => $hoarding['city'],
                    'price' => $hoarding['price'],
                    'size' => $hoarding['size'],
                    'type' => $hoarding['type'],
                    'availability' => $hoarding['availability'],
                    'rating' => $hoarding['rating'],
                    'distance_km' => $hoarding['distance_km'] ?? null,
                    'ranking_score' => $hoarding['ranking_score'] ?? null,
                    'is_featured' => $hoarding['is_featured'] ?? false,
                    'is_premium' => $hoarding['is_premium'] ?? false,
                ],
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    /**
     * Get search results as GeoJSON
     */
    public function searchGeoJSON(Request $request)
    {
        $searchResponse = $this->search($request);
        $data = $searchResponse->getData(true);

        if ($data['success']) {
            return response()->json([
                'success' => true,
                'data' => $this->toGeoJSON($data['data']),
                'meta' => $data['meta'],
            ]);
        }

        return $searchResponse;
    }
}
