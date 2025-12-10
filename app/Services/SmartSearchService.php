<?php

namespace App\Services;

use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Smart Search Service (PROMPT 54)
 * 
 * Implements intelligent search with:
 * - Coordinates + radius filter
 * - Hoarding type filter
 * - Price range filter
 * - Vendor rating filter
 * - Availability filter
 * - Multi-factor relevance scoring
 * - Sorted results: Relevance → Price → Visibility Score
 */
class SmartSearchService
{
    /**
     * Execute smart search with multi-factor ranking
     *
     * @param array $params Search parameters
     * @return array Search results with metadata
     */
    public function search(array $params): array
    {
        // Extract and validate parameters
        $latitude = $params['latitude'] ?? null;
        $longitude = $params['longitude'] ?? null;
        $radius = min($params['radius'] ?? 10, 100); // Max 100km
        $types = $this->normalizeArray($params['types'] ?? $params['type'] ?? []);
        $minPrice = $params['min_price'] ?? null;
        $maxPrice = $params['max_price'] ?? null;
        $minRating = $params['min_rating'] ?? null;
        $availability = $params['availability'] ?? null;
        $search = $params['search'] ?? null;
        $perPage = min($params['per_page'] ?? 20, 100);
        $page = max($params['page'] ?? 1, 1);

        // Build base query
        $query = Hoarding::query()
            ->where('status', Hoarding::STATUS_ACTIVE)
            ->with(['vendor', 'bookings' => function($q) {
                $q->where('status', 'confirmed')
                  ->select('hoarding_id', DB::raw('COUNT(*) as booking_count'));
            }]);

        // Apply coordinate + radius filter
        if ($latitude && $longitude) {
            $query = $this->applyRadiusFilter($query, $latitude, $longitude, $radius);
        }

        // Apply type filter
        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        // Apply price range filter
        if ($minPrice !== null) {
            $query->where(function($q) use ($minPrice) {
                $q->where('weekly_price', '>=', $minPrice)
                  ->orWhere('monthly_price', '>=', $minPrice * 4);
            });
        }
        
        if ($maxPrice !== null) {
            $query->where(function($q) use ($maxPrice) {
                $q->where('weekly_price', '<=', $maxPrice)
                  ->orWhere('monthly_price', '<=', $maxPrice * 4);
            });
        }

        // Apply availability filter
        if ($availability) {
            $query = $this->applyAvailabilityFilter($query, $availability);
        }

        // Apply text search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($vq) use ($search) {
                      $vq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Get results
        $results = $query->get();

        // Calculate relevance scores for each result
        $results = $this->calculateRelevanceScores($results, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius' => $radius,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'search' => $search,
        ]);

        // Apply vendor rating filter (after fetching to aggregate ratings)
        if ($minRating !== null) {
            $results = $results->filter(function($hoarding) use ($minRating) {
                return ($hoarding->vendor_avg_rating ?? 0) >= $minRating;
            });
        }

        // Sort by relevance → price → visibility
        $results = $this->sortResults($results);

        // Paginate
        $total = $results->count();
        $offset = ($page - 1) * $perPage;
        $paginatedResults = $results->slice($offset, $perPage)->values();

        return [
            'results' => $paginatedResults,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ],
            'filters_applied' => [
                'location' => $latitude && $longitude ? "($latitude, $longitude)" : null,
                'radius_km' => $latitude && $longitude ? $radius : null,
                'types' => $types,
                'price_range' => [$minPrice, $maxPrice],
                'min_rating' => $minRating,
                'availability' => $availability,
                'search' => $search,
            ],
        ];
    }

    /**
     * Apply radius filter using Haversine formula
     */
    protected function applyRadiusFilter(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        return $query->selectRaw("
                hoardings.*,
                (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(lat)) *
                        cos(radians(lng) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(lat))
                    )
                ) AS distance_km
            ", [$lat, $lng, $lat])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->havingRaw('distance_km <= ?', [$radiusKm]);
    }

    /**
     * Apply availability filter
     */
    protected function applyAvailabilityFilter(Builder $query, string $availability): Builder
    {
        $now = now();
        
        switch ($availability) {
            case 'available':
                // No active bookings covering current date
                $query->whereDoesntHave('bookings', function($q) use ($now) {
                    $q->where('status', 'confirmed')
                      ->where('start_date', '<=', $now)
                      ->where('end_date', '>=', $now);
                });
                break;
                
            case 'available_soon':
                // Bookings ending within 30 days
                $query->whereHas('bookings', function($q) use ($now) {
                    $q->where('status', 'confirmed')
                      ->whereBetween('end_date', [$now, $now->copy()->addDays(30)]);
                });
                break;
                
            case 'booked':
                // Has active bookings
                $query->whereHas('bookings', function($q) use ($now) {
                    $q->where('status', 'confirmed')
                      ->where('start_date', '<=', $now)
                      ->where('end_date', '>=', $now);
                });
                break;
        }
        
        return $query;
    }

    /**
     * Calculate multi-factor relevance scores
     */
    protected function calculateRelevanceScores(Collection $results, array $context): Collection
    {
        return $results->map(function($hoarding) use ($context) {
            // Distance score (0-100): Closer is better
            $distanceScore = $this->calculateDistanceScore(
                $hoarding->distance_km ?? null,
                $context['radius']
            );

            // Price relevance score (0-100): Match to budget
            $priceScore = $this->calculatePriceScore(
                $hoarding,
                $context['min_price'],
                $context['max_price']
            );

            // Vendor rating score (0-100)
            $vendorRatingScore = $this->calculateVendorRatingScore($hoarding->vendor);

            // Visibility score (0-100): Bookings, views, recency
            $visibilityScore = $this->calculateVisibilityScore($hoarding);

            // Availability score (0-100)
            $availabilityScore = $this->calculateAvailabilityScore($hoarding);

            // Text relevance score (0-100)
            $textRelevanceScore = $this->calculateTextRelevance(
                $hoarding,
                $context['search'] ?? null
            );

            // Composite relevance score with weighted factors
            $relevanceScore = (
                ($distanceScore * 0.25) +       // 25% - Distance
                ($priceScore * 0.20) +          // 20% - Price match
                ($vendorRatingScore * 0.20) +   // 20% - Vendor quality
                ($visibilityScore * 0.15) +     // 15% - Popularity
                ($availabilityScore * 0.10) +   // 10% - Availability
                ($textRelevanceScore * 0.10)    // 10% - Text match
            );

            // Attach scores to hoarding object
            $hoarding->relevance_score = round($relevanceScore, 2);
            $hoarding->distance_score = round($distanceScore, 2);
            $hoarding->price_score = round($priceScore, 2);
            $hoarding->vendor_rating_score = round($vendorRatingScore, 2);
            $hoarding->visibility_score = round($visibilityScore, 2);
            $hoarding->availability_score = round($availabilityScore, 2);
            $hoarding->vendor_avg_rating = $this->getVendorAverageRating($hoarding->vendor);

            return $hoarding;
        });
    }

    /**
     * Calculate distance score (0-100)
     */
    protected function calculateDistanceScore(?float $distance, float $maxRadius): float
    {
        if ($distance === null) {
            return 50.0; // Neutral score if no distance
        }

        // Linear decay: 100 at 0km, 0 at max radius
        return max(0, 100 - (($distance / $maxRadius) * 100));
    }

    /**
     * Calculate price relevance score (0-100)
     */
    protected function calculatePriceScore(Hoarding $hoarding, ?float $minPrice, ?float $maxPrice): float
    {
        if ($minPrice === null && $maxPrice === null) {
            return 75.0; // Good default score
        }

        $price = $hoarding->weekly_price ?? ($hoarding->monthly_price / 4);
        
        $minPrice = $minPrice ?? 0;
        $maxPrice = $maxPrice ?? PHP_FLOAT_MAX;

        // Perfect match if within range
        if ($price >= $minPrice && $price <= $maxPrice) {
            // Prefer mid-range prices
            $midPrice = ($minPrice + $maxPrice) / 2;
            $deviation = abs($price - $midPrice) / (($maxPrice - $minPrice) / 2);
            return max(70, 100 - ($deviation * 30));
        }

        // Penalty for out-of-range
        if ($price < $minPrice) {
            return 60.0; // Cheaper than budget (still good)
        }

        // More expensive than budget
        $excess = (($price - $maxPrice) / $maxPrice) * 100;
        return max(0, 50 - $excess);
    }

    /**
     * Calculate vendor rating score (0-100)
     */
    protected function calculateVendorRatingScore(?User $vendor): float
    {
        if (!$vendor) {
            return 50.0;
        }

        $avgRating = $this->getVendorAverageRating($vendor);
        
        // Convert 5-star rating to 0-100 score
        return ($avgRating / 5) * 100;
    }

    /**
     * Get vendor average rating (cached)
     */
    protected function getVendorAverageRating(?User $vendor): float
    {
        if (!$vendor) {
            return 3.0; // Default neutral rating
        }

        return Cache::remember("vendor_rating_{$vendor->id}", 3600, function() use ($vendor) {
            // Calculate from completed bookings
            $rating = DB::table('bookings')
                ->where('vendor_id', $vendor->id)
                ->where('status', 'confirmed')
                ->whereNotNull('customer_rating')
                ->avg('customer_rating');

            return $rating ? round($rating, 2) : 3.5;
        });
    }

    /**
     * Calculate visibility score (0-100)
     * Based on: booking count, views, recency
     */
    protected function calculateVisibilityScore(Hoarding $hoarding): float
    {
        // Booking count score (0-40 points)
        $bookingCount = $hoarding->bookings->count();
        $bookingScore = min(40, $bookingCount * 2);

        // Recency score (0-30 points)
        $daysOld = now()->diffInDays($hoarding->created_at);
        $recencyScore = max(0, 30 - ($daysOld / 30)); // Newer is better

        // Featured bonus (0-30 points)
        $featuredScore = ($hoarding->is_featured ?? false) ? 30 : 0;

        return $bookingScore + $recencyScore + $featuredScore;
    }

    /**
     * Calculate availability score (0-100)
     */
    protected function calculateAvailabilityScore(Hoarding $hoarding): float
    {
        $now = now();
        
        // Check if currently booked
        $isBooked = $hoarding->bookings()
            ->where('status', 'confirmed')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->exists();

        if (!$isBooked) {
            return 100.0; // Fully available
        }

        // Check when next available
        $nextBookingEnd = $hoarding->bookings()
            ->where('status', 'confirmed')
            ->where('end_date', '>=', $now)
            ->orderBy('end_date', 'asc')
            ->value('end_date');

        if (!$nextBookingEnd) {
            return 100.0;
        }

        $daysUntilAvailable = now()->diffInDays($nextBookingEnd);
        
        // Score based on how soon it's available
        if ($daysUntilAvailable <= 7) return 70.0;
        if ($daysUntilAvailable <= 14) return 50.0;
        if ($daysUntilAvailable <= 30) return 30.0;
        
        return 10.0; // Booked for long term
    }

    /**
     * Calculate text relevance score (0-100)
     */
    protected function calculateTextRelevance(Hoarding $hoarding, ?string $search): float
    {
        if (!$search) {
            return 50.0; // Neutral if no search
        }

        $search = strtolower($search);
        $score = 0;

        // Exact match in title (40 points)
        if (stripos($hoarding->title, $search) !== false) {
            $score += 40;
        }

        // Match in address (30 points)
        if (stripos($hoarding->address, $search) !== false) {
            $score += 30;
        }

        // Match in description (20 points)
        if (stripos($hoarding->description, $search) !== false) {
            $score += 20;
        }

        // Match in vendor name (10 points)
        if ($hoarding->vendor && stripos($hoarding->vendor->name, $search) !== false) {
            $score += 10;
        }

        return min(100, $score);
    }

    /**
     * Sort results by: Relevance → Price → Visibility
     */
    protected function sortResults(Collection $results): Collection
    {
        return $results->sortBy([
            ['relevance_score', 'desc'],    // Primary: Relevance
            ['price_score', 'desc'],        // Secondary: Price match
            ['visibility_score', 'desc'],   // Tertiary: Visibility
        ])->values();
    }

    /**
     * Normalize array parameter
     */
    protected function normalizeArray($value): array
    {
        if (is_string($value)) {
            return explode(',', $value);
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        return [];
    }

    /**
     * Get available hoarding types for filters
     */
    public function getAvailableTypes(): array
    {
        return Cache::remember('hoarding_types', 86400, function() {
            return Hoarding::select('type')
                ->where('status', Hoarding::STATUS_ACTIVE)
                ->distinct()
                ->pluck('type')
                ->toArray();
        });
    }

    /**
     * Get price range statistics
     */
    public function getPriceRange(): array
    {
        return Cache::remember('hoarding_price_range', 3600, function() {
            $stats = Hoarding::where('status', Hoarding::STATUS_ACTIVE)
                ->selectRaw('
                    MIN(COALESCE(weekly_price, monthly_price/4)) as min_price,
                    MAX(COALESCE(weekly_price, monthly_price/4)) as max_price,
                    AVG(COALESCE(weekly_price, monthly_price/4)) as avg_price
                ')
                ->first();

            return [
                'min' => (int) ($stats->min_price ?? 5000),
                'max' => (int) ($stats->max_price ?? 100000),
                'avg' => (int) ($stats->avg_price ?? 25000),
            ];
        });
    }
}
