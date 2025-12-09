<?php

namespace App\Services;

use App\Models\SearchRankingSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GeoSearchService
{
    protected SearchRankingSetting $settings;

    public function __construct()
    {
        $this->settings = SearchRankingSetting::current();
    }

    /**
     * Search hoardings by location and radius
     */
    public function searchByLocation(
        ?float $latitude,
        ?float $longitude,
        ?int $radiusKm = null,
        array $filters = [],
        int $page = 1
    ): array {
        $radiusKm = $this->settings->getValidatedRadius($radiusKm);
        $perPage = $this->settings->results_per_page;

        // Base query
        $query = DB::table('hoardings')
            ->where('status', 'active');

        // Apply geolocation filter if coordinates provided
        if ($latitude && $longitude) {
            $query = $this->applyDistanceFilter($query, $latitude, $longitude, $radiusKm);
        }

        // Apply additional filters
        $query = $this->applyFilters($query, $filters);

        // Get total count
        $total = $query->count();

        // Calculate ranking scores and sort
        $results = $this->calculateRankings($query, $latitude, $longitude, $filters);

        // Paginate
        $offset = ($page - 1) * $perPage;
        $paginatedResults = array_slice($results, $offset, $perPage);

        return [
            'results' => $paginatedResults,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'search_params' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius_km' => $radiusKm,
                'filters' => $filters,
            ],
        ];
    }

    /**
     * Apply distance filter using Haversine formula
     */
    protected function applyDistanceFilter(
        $query,
        float $latitude,
        float $longitude,
        int $radiusKm
    ) {
        // Haversine formula for calculating distance
        // Returns results within radius, adds 'distance_km' column
        return $query->selectRaw("
                *,
                (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(latitude))
                    )
                ) AS distance_km
            ", [$latitude, $longitude, $latitude])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->havingRaw('distance_km <= ?', [$radiusKm]);
    }

    /**
     * Apply search filters
     */
    protected function applyFilters($query, array $filters)
    {
        // Price range
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Property type
        if (!empty($filters['property_type'])) {
            $query->where('type', $filters['property_type']);
        }

        // Size range
        if (!empty($filters['min_size'])) {
            $query->where('size', '>=', $filters['min_size']);
        }
        if (!empty($filters['max_size'])) {
            $query->where('size', '<=', $filters['max_size']);
        }

        // Availability
        if (!empty($filters['availability'])) {
            $query->where('availability', $filters['availability']);
        }

        // Vendor
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        // Minimum rating
        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        // City
        if (!empty($filters['city'])) {
            $query->where('city', 'like', '%' . $filters['city'] . '%');
        }

        // Search query (name, address, description)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    /**
     * Calculate ranking scores for results
     */
    protected function calculateRankings($query, ?float $latitude, ?float $longitude, array $filters): array
    {
        $results = $query->get()->toArray();

        foreach ($results as &$hoarding) {
            $hoarding = (array) $hoarding;
            
            $factors = [
                'distance_score' => $this->calculateDistanceScore($hoarding, $latitude, $longitude),
                'price_score' => $this->calculatePriceScore($hoarding, $filters),
                'availability_score' => $this->calculateAvailabilityScore($hoarding),
                'rating_score' => $this->calculateRatingScore($hoarding),
                'popularity_score' => $this->calculatePopularityScore($hoarding),
                'recency_score' => $this->calculateRecencyScore($hoarding),
                'is_featured' => $hoarding['is_featured'] ?? false,
                'is_verified_vendor' => false, // TODO: Check vendor verification
                'is_premium' => $hoarding['is_premium'] ?? false,
            ];

            $hoarding['ranking_score'] = $this->settings->calculateScore($factors);
            $hoarding['ranking_factors'] = $factors;
        }

        // Sort by ranking score (highest first)
        usort($results, fn($a, $b) => $b['ranking_score'] <=> $a['ranking_score']);

        return $results;
    }

    /**
     * Calculate distance score (0-100)
     * Closer = higher score
     */
    protected function calculateDistanceScore(array $hoarding, ?float $lat, ?float $lng): float
    {
        if (!$lat || !$lng || !isset($hoarding['distance_km'])) {
            return 50; // Neutral score if no location
        }

        $distance = $hoarding['distance_km'];
        $maxDistance = $this->settings->max_radius_km;

        // Linear score: 100 at 0km, 0 at max distance
        return max(0, 100 - ($distance / $maxDistance * 100));
    }

    /**
     * Calculate price score (0-100)
     * Match user's budget preference
     */
    protected function calculatePriceScore(array $hoarding, array $filters): float
    {
        $price = $hoarding['price'] ?? 0;

        if (empty($filters['min_price']) && empty($filters['max_price'])) {
            return 50; // Neutral if no price filter
        }

        $minPrice = $filters['min_price'] ?? 0;
        $maxPrice = $filters['max_price'] ?? PHP_INT_MAX;

        // Perfect match if within range
        if ($price >= $minPrice && $price <= $maxPrice) {
            // Prefer prices closer to the middle of the range
            $midPrice = ($minPrice + $maxPrice) / 2;
            $deviation = abs($price - $midPrice) / ($maxPrice - $minPrice);
            return 100 - ($deviation * 30); // 70-100 score
        }

        // Partial score if outside range
        if ($price < $minPrice) {
            $diff = $minPrice - $price;
            return max(0, 50 - ($diff / $minPrice * 50));
        }

        $diff = $price - $maxPrice;
        return max(0, 50 - ($diff / $maxPrice * 50));
    }

    /**
     * Calculate availability score (0-100)
     */
    protected function calculateAvailabilityScore(array $hoarding): float
    {
        $availability = $hoarding['availability'] ?? 'unavailable';

        return match ($availability) {
            'available' => 100,
            'available_soon' => 70,
            'partially_available' => 50,
            'booked' => 20,
            'unavailable' => 0,
            default => 50,
        };
    }

    /**
     * Calculate rating score (0-100)
     */
    protected function calculateRatingScore(array $hoarding): float
    {
        $rating = $hoarding['rating'] ?? 0;

        // Convert 0-5 rating to 0-100 score
        return ($rating / 5) * 100;
    }

    /**
     * Calculate popularity score (0-100)
     * Based on views and bookings
     */
    protected function calculatePopularityScore(array $hoarding): float
    {
        $views = $hoarding['views_count'] ?? 0;
        $bookings = $hoarding['bookings_count'] ?? 0;

        // Weight bookings more than views
        $popularity = ($views * 1) + ($bookings * 10);

        // Normalize to 0-100 (assuming max 1000 views, 100 bookings)
        $maxPopularity = 1000 + (100 * 10);

        return min(100, ($popularity / $maxPopularity) * 100);
    }

    /**
     * Calculate recency score (0-100)
     * Newer listings score higher
     */
    protected function calculateRecencyScore(array $hoarding): float
    {
        $createdAt = $hoarding['created_at'] ?? null;

        if (!$createdAt) {
            return 50;
        }

        $daysOld = now()->diffInDays($createdAt);

        // 100 for new (0-7 days), decrease over time
        if ($daysOld <= 7) {
            return 100;
        } elseif ($daysOld <= 30) {
            return 80;
        } elseif ($daysOld <= 90) {
            return 60;
        } elseif ($daysOld <= 180) {
            return 40;
        } else {
            return 20;
        }
    }

    /**
     * Get nearby hoardings
     */
    public function getNearby(float $latitude, float $longitude, int $limit = 10): array
    {
        return DB::table('hoardings')
            ->selectRaw("
                *,
                (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(latitude))
                    )
                ) AS distance_km
            ", [$latitude, $longitude, $latitude])
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('distance_km')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Autocomplete location search
     */
    public function autocomplete(string $query, int $limit = null): array
    {
        $limit = $limit ?? $this->settings->autocomplete_max_results;

        if (strlen($query) < $this->settings->autocomplete_min_chars) {
            return [];
        }

        return DB::table('hoardings')
            ->select('city', 'address', 'latitude', 'longitude')
            ->where(function ($q) use ($query) {
                $q->where('city', 'like', '%' . $query . '%')
                    ->orWhere('address', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%');
            })
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->groupBy('city', 'address', 'latitude', 'longitude')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get map bounds for results
     */
    public function getMapBounds(array $hoardings): array
    {
        if (empty($hoardings)) {
            return [
                'minLat' => null,
                'maxLat' => null,
                'minLng' => null,
                'maxLng' => null,
            ];
        }

        $lats = array_column($hoardings, 'latitude');
        $lngs = array_column($hoardings, 'longitude');

        return [
            'minLat' => min($lats),
            'maxLat' => max($lats),
            'minLng' => min($lngs),
            'maxLng' => max($lngs),
        ];
    }
}
