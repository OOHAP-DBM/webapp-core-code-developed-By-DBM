<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchRankingSetting;
use Illuminate\Http\Request;

class SearchSettingsController extends Controller
{
    /**
     * Show search settings form
     */
    public function index()
    {
        $settings = SearchRankingSetting::current();

        return view('admin.search.settings', [
            'settings' => $settings,
            'defaults' => SearchRankingSetting::getDefaults(),
        ]);
    }

    /**
     * Update search settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Ranking weights
            'distance_weight' => 'required|integer|min:0|max:100',
            'price_weight' => 'required|integer|min:0|max:100',
            'availability_weight' => 'required|integer|min:0|max:100',
            'rating_weight' => 'required|integer|min:0|max:100',
            'popularity_weight' => 'required|integer|min:0|max:100',
            'recency_weight' => 'required|integer|min:0|max:100',

            // Boost factors
            'featured_boost' => 'required|integer|min:0|max:100',
            'verified_vendor_boost' => 'required|integer|min:0|max:100',
            'premium_boost' => 'required|integer|min:0|max:100',

            // Search behavior
            'default_radius_km' => 'required|integer|min:1|max:100',
            'max_radius_km' => 'required|integer|min:1|max:500',
            'min_radius_km' => 'required|integer|min:1|max:50',
            'results_per_page' => 'required|integer|min:10|max:100',
            'max_results' => 'required|integer|min:100|max:10000',

            // Map settings
            'default_center' => 'required|array',
            'default_center.lat' => 'required|numeric|between:-90,90',
            'default_center.lng' => 'required|numeric|between:-180,180',
            'default_zoom_level' => 'required|integer|min:1|max:20',
            'cluster_markers' => 'required|boolean',
            'cluster_radius' => 'required|integer|min:10|max:200',

            // Filter settings
            'enabled_filters' => 'required|array',
            'enabled_filters.*' => 'string',
            'filter_defaults' => 'nullable|array',

            // Autocomplete
            'enable_autocomplete' => 'required|boolean',
            'autocomplete_min_chars' => 'required|integer|min:1|max:10',
            'autocomplete_max_results' => 'required|integer|min:1|max:50',
        ]);

        // Validate total weight equals 100
        $totalWeight = $validated['distance_weight'] +
                      $validated['price_weight'] +
                      $validated['availability_weight'] +
                      $validated['rating_weight'] +
                      $validated['popularity_weight'] +
                      $validated['recency_weight'];

        if ($totalWeight !== 100) {
            return back()->withErrors([
                'weights' => 'Total weight must equal 100. Current total: ' . $totalWeight,
            ])->withInput();
        }

        // Validate min <= default <= max for radius
        if ($validated['min_radius_km'] > $validated['default_radius_km'] ||
            $validated['default_radius_km'] > $validated['max_radius_km']) {
            return back()->withErrors([
                'radius' => 'Radius values must satisfy: min ≤ default ≤ max',
            ])->withInput();
        }

        $settings = SearchRankingSetting::current();
        $settings->update($validated);

        return redirect()
            ->route('admin.search-settings.index')
            ->with('success', 'Search settings updated successfully');
    }

    /**
     * Reset to default settings
     */
    public function reset()
    {
        $settings = SearchRankingSetting::current();
        $settings->update(SearchRankingSetting::getDefaults());

        return redirect()
            ->route('admin.search-settings.index')
            ->with('success', 'Search settings reset to defaults');
    }

    /**
     * Preview scoring algorithm
     */
    public function previewScore(Request $request)
    {
        $validated = $request->validate([
            'distance_score' => 'required|numeric|min:0|max:100',
            'price_score' => 'required|numeric|min:0|max:100',
            'availability_score' => 'required|numeric|min:0|max:100',
            'rating_score' => 'required|numeric|min:0|max:100',
            'popularity_score' => 'required|numeric|min:0|max:100',
            'recency_score' => 'required|numeric|min:0|max:100',
            'is_featured' => 'required|boolean',
            'is_verified_vendor' => 'required|boolean',
            'is_premium' => 'required|boolean',

            // Current weights
            'distance_weight' => 'required|integer|min:0|max:100',
            'price_weight' => 'required|integer|min:0|max:100',
            'availability_weight' => 'required|integer|min:0|max:100',
            'rating_weight' => 'required|integer|min:0|max:100',
            'popularity_weight' => 'required|integer|min:0|max:100',
            'recency_weight' => 'required|integer|min:0|max:100',
            'featured_boost' => 'required|integer|min:0|max:100',
            'verified_vendor_boost' => 'required|integer|min:0|max:100',
            'premium_boost' => 'required|integer|min:0|max:100',
        ]);

        // Create temporary settings object
        $tempSettings = new SearchRankingSetting([
            'distance_weight' => $validated['distance_weight'],
            'price_weight' => $validated['price_weight'],
            'availability_weight' => $validated['availability_weight'],
            'rating_weight' => $validated['rating_weight'],
            'popularity_weight' => $validated['popularity_weight'],
            'recency_weight' => $validated['recency_weight'],
            'featured_boost' => $validated['featured_boost'],
            'verified_vendor_boost' => $validated['verified_vendor_boost'],
            'premium_boost' => $validated['premium_boost'],
        ]);

        $factors = [
            'distance_score' => $validated['distance_score'],
            'price_score' => $validated['price_score'],
            'availability_score' => $validated['availability_score'],
            'rating_score' => $validated['rating_score'],
            'popularity_score' => $validated['popularity_score'],
            'recency_score' => $validated['recency_score'],
            'is_featured' => $validated['is_featured'],
            'is_verified_vendor' => $validated['is_verified_vendor'],
            'is_premium' => $validated['is_premium'],
        ];

        $score = $tempSettings->calculateScore($factors);

        // Calculate breakdown
        $baseScore = ($validated['distance_score'] * $validated['distance_weight'] / 100) +
                    ($validated['price_score'] * $validated['price_weight'] / 100) +
                    ($validated['availability_score'] * $validated['availability_weight'] / 100) +
                    ($validated['rating_score'] * $validated['rating_weight'] / 100) +
                    ($validated['popularity_score'] * $validated['popularity_weight'] / 100) +
                    ($validated['recency_score'] * $validated['recency_weight'] / 100);

        $boostMultiplier = 1.0;
        $boosts = [];

        if ($validated['is_featured']) {
            $boost = 1 + ($validated['featured_boost'] / 100);
            $boostMultiplier *= $boost;
            $boosts[] = "Featured: ×{$boost}";
        }
        if ($validated['is_verified_vendor']) {
            $boost = 1 + ($validated['verified_vendor_boost'] / 100);
            $boostMultiplier *= $boost;
            $boosts[] = "Verified: ×{$boost}";
        }
        if ($validated['is_premium']) {
            $boost = 1 + ($validated['premium_boost'] / 100);
            $boostMultiplier *= $boost;
            $boosts[] = "Premium: ×{$boost}";
        }

        return response()->json([
            'success' => true,
            'data' => [
                'final_score' => round($score, 2),
                'base_score' => round($baseScore, 2),
                'boost_multiplier' => round($boostMultiplier, 2),
                'boosts_applied' => $boosts,
                'breakdown' => [
                    'distance' => round($validated['distance_score'] * $validated['distance_weight'] / 100, 2),
                    'price' => round($validated['price_score'] * $validated['price_weight'] / 100, 2),
                    'availability' => round($validated['availability_score'] * $validated['availability_weight'] / 100, 2),
                    'rating' => round($validated['rating_score'] * $validated['rating_weight'] / 100, 2),
                    'popularity' => round($validated['popularity_score'] * $validated['popularity_weight'] / 100, 2),
                    'recency' => round($validated['recency_score'] * $validated['recency_weight'] / 100, 2),
                ],
            ],
        ]);
    }

    /**
     * Get current settings as JSON
     */
    public function show()
    {
        $settings = SearchRankingSetting::current();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
