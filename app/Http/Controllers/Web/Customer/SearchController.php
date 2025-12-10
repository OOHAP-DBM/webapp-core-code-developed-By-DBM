<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Services\SmartSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Smart Search Controller (PROMPT 54)
 * 
 * Implements intelligent search with multi-factor ranking:
 * - Coordinates + radius filter
 * - Hoarding type filter  
 * - Price range filter
 * - Vendor rating filter
 * - Availability filter
 * - Sorted by: Relevance → Price → Visibility
 */
class SearchController extends Controller
{
    protected SmartSearchService $smartSearch;

    public function __construct(SmartSearchService $smartSearch)
    {
        $this->smartSearch = $smartSearch;
    }

    /**
     * Display smart search results (PROMPT 54).
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Validate input
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:100',
            'types' => 'nullable|array',
            'types.*' => 'string',
            'type' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_rating' => 'nullable|numeric|between:0,5',
            'availability' => 'nullable|in:available,available_soon,booked',
            'sort' => 'nullable|in:relevance,price_low,price_high,rating,distance',
            'per_page' => 'nullable|integer|min:12|max:60',
            'page' => 'nullable|integer|min:1',
        ]);

        // Execute smart search
        $searchResults = $this->smartSearch->search([
            'search' => $validated['search'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'radius' => $validated['radius'] ?? 10,
            'types' => $validated['types'] ?? ($validated['type'] ?? []),
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'min_rating' => $validated['min_rating'] ?? null,
            'availability' => $validated['availability'] ?? null,
            'per_page' => $validated['per_page'] ?? 20,
            'page' => $validated['page'] ?? 1,
        ]);

        // Convert to paginator for view compatibility
        $hoardings = new LengthAwarePaginator(
            $searchResults['results'],
            $searchResults['meta']['total'],
            $searchResults['meta']['per_page'],
            $searchResults['meta']['current_page'],
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Get filter options
        $availableTypes = $this->smartSearch->getAvailableTypes();
        $priceRange = $this->smartSearch->getPriceRange();
        
        // Get unique cities for legacy support
        $cities = \App\Models\Hoarding::active()
            ->distinct()
            ->orderBy('address')
            ->pluck('address')
            ->map(fn($addr) => explode(',', $addr)[0] ?? '')
            ->unique()
            ->filter()
            ->values();

        return view('customer.search', compact(
            'hoardings',
            'availableTypes',
            'priceRange',
            'cities',
            'searchResults'
        ));
    }

    /**
     * API endpoint for smart search (PROMPT 54).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function apiSearch(Request $request): JsonResponse
    {
        // Validate input
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:100',
            'types' => 'nullable|array',
            'types.*' => 'string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_rating' => 'nullable|numeric|between:0,5',
            'availability' => 'nullable|in:available,available_soon,booked',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        // Execute smart search
        $results = $this->smartSearch->search($validated);

        return response()->json([
            'success' => true,
            'data' => $results['results'],
            'meta' => $results['meta'],
            'filters_applied' => $results['filters_applied'],
        ]);
    }

    /**
     * Get available filter options.
     *
     * @return JsonResponse
     */
    public function getFilterOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'types' => $this->smartSearch->getAvailableTypes(),
                'price_range' => $this->smartSearch->getPriceRange(),
                'availability_options' => [
                    'available' => 'Available Now',
                    'available_soon' => 'Available Soon',
                    'booked' => 'Currently Booked',
                ],
            ],
        ]);
    }
}
