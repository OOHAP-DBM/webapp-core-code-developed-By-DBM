<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchRankingSetting extends Model
{
    protected $fillable = [
        'distance_weight',
        'price_weight',
        'availability_weight',
        'rating_weight',
        'popularity_weight',
        'recency_weight',
        'featured_boost',
        'verified_vendor_boost',
        'premium_boost',
        'default_radius_km',
        'max_radius_km',
        'min_radius_km',
        'results_per_page',
        'max_results',
        'default_center',
        'default_zoom_level',
        'cluster_markers',
        'cluster_radius',
        'enabled_filters',
        'filter_defaults',
        'enable_autocomplete',
        'autocomplete_min_chars',
        'autocomplete_max_results',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'default_center' => 'array',
        'enabled_filters' => 'array',
        'filter_defaults' => 'array',
        'cluster_markers' => 'boolean',
        'enable_autocomplete' => 'boolean',
    ];

    /**
     * Get updater
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get current settings (singleton pattern)
     */
    public static function current(): self
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create(self::getDefaults());
        }

        return $settings;
    }

    /**
     * Get default settings
     */
    public static function getDefaults(): array
    {
        return [
            'distance_weight' => 40,
            'price_weight' => 20,
            'availability_weight' => 15,
            'rating_weight' => 10,
            'popularity_weight' => 10,
            'recency_weight' => 5,
            'featured_boost' => 50,
            'verified_vendor_boost' => 20,
            'premium_boost' => 30,
            'default_radius_km' => 10,
            'max_radius_km' => 100,
            'min_radius_km' => 1,
            'results_per_page' => 20,
            'max_results' => 1000,
            'default_center' => [
                'lat' => 28.6139, // Delhi, India
                'lng' => 77.2090
            ],
            'default_zoom_level' => 12,
            'cluster_markers' => true,
            'cluster_radius' => 80,
            'enabled_filters' => [
                'price_range',
                'property_type',
                'size',
                'availability',
                'rating',
                'features'
            ],
            'filter_defaults' => [],
            'enable_autocomplete' => true,
            'autocomplete_min_chars' => 3,
            'autocomplete_max_results' => 10,
        ];
    }

    /**
     * Get total weight
     */
    public function getTotalWeightAttribute(): int
    {
        return $this->distance_weight +
            $this->price_weight +
            $this->availability_weight +
            $this->rating_weight +
            $this->popularity_weight +
            $this->recency_weight;
    }

    /**
     * Calculate ranking score for a hoarding
     */
    public function calculateScore(array $factors): float
    {
        $score = 0;

        // Base factors (0-100 each)
        $score += ($factors['distance_score'] ?? 0) * ($this->distance_weight / 100);
        $score += ($factors['price_score'] ?? 0) * ($this->price_weight / 100);
        $score += ($factors['availability_score'] ?? 0) * ($this->availability_weight / 100);
        $score += ($factors['rating_score'] ?? 0) * ($this->rating_weight / 100);
        $score += ($factors['popularity_score'] ?? 0) * ($this->popularity_weight / 100);
        $score += ($factors['recency_score'] ?? 0) * ($this->recency_weight / 100);

        // Apply boosts
        if ($factors['is_featured'] ?? false) {
            $score *= (1 + $this->featured_boost / 100);
        }

        if ($factors['is_verified_vendor'] ?? false) {
            $score *= (1 + $this->verified_vendor_boost / 100);
        }

        if ($factors['is_premium'] ?? false) {
            $score *= (1 + $this->premium_boost / 100);
        }

        return round($score, 2);
    }

    /**
     * Validate radius value
     */
    public function isValidRadius(int $radius): bool
    {
        return $radius >= $this->min_radius_km && $radius <= $this->max_radius_km;
    }

    /**
     * Get validated radius
     */
    public function getValidatedRadius(?int $radius): int
    {
        if ($radius === null) {
            return $this->default_radius_km;
        }

        return max($this->min_radius_km, min($radius, $this->max_radius_km));
    }
}
