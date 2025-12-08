<?php

namespace Modules\Hoardings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoardingGeo extends Model
{
    use HasFactory;

    protected $fillable = [
        'hoarding_id',
        'geojson',
        'bounding_box',
    ];

    protected $casts = [
        'geojson' => 'array',
        'bounding_box' => 'array',
    ];

    /**
     * Get the hoarding that owns the geo data
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /**
     * Check if a point (lat, lng) is within the polygon using ray-casting algorithm
     */
    public function isPointInPolygon(float $lat, float $lng): bool
    {
        if (!$this->geojson || !isset($this->geojson['coordinates'])) {
            return false;
        }

        // GeoJSON polygon format: [[[lng, lat], [lng, lat], ...]]
        $coordinates = $this->geojson['coordinates'][0] ?? [];
        
        if (count($coordinates) < 3) {
            return false;
        }

        $inside = false;
        $count = count($coordinates);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $coordinates[$i][0]; // longitude
            $yi = $coordinates[$i][1]; // latitude
            $xj = $coordinates[$j][0];
            $yj = $coordinates[$j][1];

            $intersect = (($yi > $lat) != ($yj > $lat))
                && ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Calculate bounding box from GeoJSON polygon
     */
    public function calculateBoundingBox(): ?array
    {
        if (!$this->geojson || !isset($this->geojson['coordinates'])) {
            return null;
        }

        $coordinates = $this->geojson['coordinates'][0] ?? [];
        
        if (empty($coordinates)) {
            return null;
        }

        $lngs = array_column($coordinates, 0);
        $lats = array_column($coordinates, 1);

        return [
            'min_lat' => min($lats),
            'max_lat' => max($lats),
            'min_lng' => min($lngs),
            'max_lng' => max($lngs),
        ];
    }

    /**
     * Update bounding box from geojson
     */
    public function updateBoundingBox(): void
    {
        $bbox = $this->calculateBoundingBox();
        
        if ($bbox) {
            $this->bounding_box = $bbox;
            $this->save();
        }
    }

    /**
     * Check if this geo fence intersects with a bounding box
     */
    public function intersectsBoundingBox(float $minLat, float $maxLat, float $minLng, float $maxLng): bool
    {
        if (!$this->bounding_box) {
            return false;
        }

        $bbox = $this->bounding_box;

        // Check if bounding boxes overlap
        return !(
            $bbox['max_lat'] < $minLat ||
            $bbox['min_lat'] > $maxLat ||
            $bbox['max_lng'] < $minLng ||
            $bbox['min_lng'] > $maxLng
        );
    }

    /**
     * Get all geo fences that intersect with a bounding box
     */
    public static function inBoundingBox(float $minLat, float $maxLat, float $minLng, float $maxLng)
    {
        return static::whereNotNull('bounding_box')
            ->get()
            ->filter(function ($geo) use ($minLat, $maxLat, $minLng, $maxLng) {
                return $geo->intersectsBoundingBox($minLat, $maxLat, $minLng, $maxLng);
            });
    }

    /**
     * Get all geo fences containing a specific point
     */
    public static function containingPoint(float $lat, float $lng)
    {
        return static::whereNotNull('geojson')
            ->get()
            ->filter(function ($geo) use ($lat, $lng) {
                return $geo->isPointInPolygon($lat, $lng);
            });
    }
}

