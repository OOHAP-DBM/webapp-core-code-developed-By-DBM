<?php

namespace Modules\Hoardings\Repositories;

use App\Models\Hoarding;
use App\Models\HoardingGeo;
use Modules\Hoardings\Repositories\Contracts\HoardingRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HoardingRepository extends BaseRepository implements HoardingRepositoryInterface
{
    /**
     * HoardingRepository constructor.
     *
     * @param Hoarding $model
     */
    public function __construct(Hoarding $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all hoardings with optional filters and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with([
            'vendor',
            'hoardingMedia',
            'ooh.packages',
            'doohScreen.media',
            'doohScreen.packages',
        ]);

        // Apply filters
        if (!empty($filters['vendor_id'])) {
            $query->byVendor($filters['vendor_id']);
        }

        if (!empty($filters['hoarding_type'])) {
            $query->where('hoarding_type', $filters['hoarding_type']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->byStatus($filters['status']);
            }
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'LIKE', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['state'])) {
            $query->where('state', 'LIKE', '%' . $filters['state'] . '%');
        }

        if (isset($filters['min_price'])) {
            $query->where(function($q) use ($filters) {
                $q->where('monthly_price', '>=', $filters['min_price'])
                  ->orWhere(function($sq) use ($filters) {
                      $sq->whereNull('monthly_price')
                         ->where('base_monthly_price', '>=', $filters['min_price']);
                  });
            });
        }

        if (isset($filters['max_price'])) {
            $query->where(function($q) use ($filters) {
                $q->where('monthly_price', '<=', $filters['max_price'])
                  ->orWhere(function($sq) use ($filters) {
                      $sq->whereNull('monthly_price')
                         ->where('base_monthly_price', '<=', $filters['max_price']);
                  });
            });
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Letter filter: only hoardings where title starts with the selected letter
        if (!empty($filters['letter'])) {
            $query->whereRaw('UPPER(LEFT(title, 1)) = ?', [mb_strtoupper(mb_substr($filters["letter"], 0, 1))]);
        }

        if (!empty($filters['lat']) && !empty($filters['lng'])) {
            $radiusKm = $filters['radius'] ?? 10;
            $query->nearLocation($filters['lat'], $filters['lng'], $radiusKm);
        }

        // Default to active hoardings for public listing
        if (!isset($filters['status']) && !isset($filters['include_all'])) {
            $query->active();
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        // Map 'price' to monthly_price with fallback to base_monthly_price
        if ($sortBy === 'price') {
            $sortBy = 'base_monthly_price';
        }
        
        // Don't add additional orderBy if location-based sorting is already applied
        if (empty($filters['lat']) || empty($filters['lng'])) {
            // For price sorting, use COALESCE to handle null monthly_price
            if ($sortBy === 'base_monthly_price') {
                $query->orderByRaw("COALESCE(monthly_price, base_monthly_price) {$sortOrder}");
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Find a hoarding by ID.
     *
     * @param int $id
     * @return Hoarding|null
     */
    public function findById(int $id)
    {
        return $this->model->with('vendor')->find($id);
    }

    /**
     * Create a new hoarding.
     *
     * @param array $data
     * @return Hoarding
     */
    // public function create(array $data)
    // {
    //     return $this->model->create($data);
    // }
    public function createForVendor(array $data, int $vendorId): Hoarding
    {
        $data['vendor_id'] = $vendorId;

        return $this->model->create($data);
    }

    /**
     * Update a hoarding.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $hoarding = $this->findById($id);
        
        if (!$hoarding) {
            return false;
        }

        return $hoarding->update($data);
    }

    /**
     * Delete a hoarding (soft delete).
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $hoarding = $this->findById($id);
        if (!$hoarding) {
            return false;
        }

        // Delete related data based on hoarding_type
        if ($hoarding->hoarding_type === \App\Models\Hoarding::TYPE_OOH) {
            // Delete OOH child, packages, brand logos
            $ooh = $hoarding->ooh;
            if ($ooh) {
                \Modules\Hoardings\Models\OOHHoarding::where('hoarding_id', $ooh->id)->delete();
                // Delete OOH packages
                \Modules\Hoardings\Models\HoardingPackage::where('hoarding_id', $ooh->id)->delete();
                // Delete OOH brand logos
                \Modules\Hoardings\Models\HoardingBrandLogo::where('hoarding_id', $ooh->id)->delete();
                $ooh->delete();
            }
        } elseif ($hoarding->hoarding_type === \App\Models\Hoarding::TYPE_DOOH) {
            // Delete DOOH screens, slots, packages, brand logos
            $doohScreens = $hoarding->doohScreens;
            foreach ($doohScreens as $screen) {
                // Delete DOOH packages
                if (method_exists($screen, 'packages')) {
                    $screen->packages()->delete();
                }
                // Delete DOOH slots
                if (method_exists($screen, 'slots')) {
                    $screen->slots()->delete();
                }
                // Delete DOOH brand logos
                if (method_exists($screen, 'brandLogos')) {
                    $screen->brandLogos()->delete();
                }
                // Delete DOOH media
                if (method_exists($screen, 'media')) {
                    $screen->media()->delete();
                }
                $screen->delete();
            }
            // Delete DOOH packages directly linked to hoarding (if any)
            \Modules\Hoardings\Models\HoardingPackage::where('hoarding_id', $hoarding->id)->delete();
            // Delete DOOH brand logos directly linked to hoarding (if any)
            \Modules\Hoardings\Models\HoardingBrandLogo::where('hoarding_id', $hoarding->id)->delete();
        }

        // Delete packages and brand logos linked to parent hoarding (if any)
        \Modules\Hoardings\Models\HoardingPackage::where('hoarding_id', $hoarding->id)->delete();
        \Modules\Hoardings\Models\HoardingBrandLogo::where('hoarding_id', $hoarding->id)->delete();

        return $hoarding->delete();
    }
   

    /**
     * Get hoardings by vendor.
     *
     * @param int $vendorId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->byVendor($vendorId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search hoardings by keyword.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->search($keyword)
            ->active()
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get hoardings near a location.
     *
     * @param float $lat
     * @param float $lng
     * @param float $radiusKm
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getNearLocation(float $lat, float $lng, float $radiusKm = 10, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->nearLocation($lat, $lng, $radiusKm)
            ->active()
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active hoardings.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->active()
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update hoarding status.
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $hoarding = $this->findById($id);
        
        if (!$hoarding) {
            return false;
        }

        return $hoarding->update(['status' => $status]);
    }

    /**
     * Get hoardings within a bounding box.
     *
     * @param float $minLat
     * @param float $maxLat
     * @param float $minLng
     * @param float $maxLng
     * @return Collection
     */
    public function getByBoundingBox(float $minLat, float $maxLat, float $minLng, float $maxLng): Collection
    {
        return $this->model->whereBetween('lat', [$minLat, $maxLat])
            ->whereBetween('lng', [$minLng, $maxLng])
            ->active()
            ->with(['vendor', 'geo'])
            ->get();
    }

    /**
     * Get hoardings within radius with precise Haversine calculation.
     *
     * @param float $lat
     * @param float $lng
     * @param float $radiusKm
     * @return Collection
     */
    public function getNearbyWithRadius(float $lat, float $lng, float $radiusKm = 10): Collection
    {
        // First, get candidates using bounding box (fast)
        $candidates = $this->model->nearLocation($lat, $lng, $radiusKm)
            ->active()
            ->with(['vendor', 'geo'])
            ->get();

        // Then filter with precise Haversine distance
        return $candidates->filter(function ($hoarding) use ($lat, $lng, $radiusKm) {
            return $hoarding->haversineDistance($lat, $lng) <= $radiusKm;
        });
    }

    /**
     * Get compact map pins (minimal data for map markers).
     *
     * @param array $filters
     * @return Collection
     */
    public function getMapPins(array $filters = []): Collection
    {
        $query = $this->model->active();

        // Apply location filters
        if (!empty($filters['bbox'])) {
            $bbox = explode(',', $filters['bbox']);
            if (count($bbox) === 4) {
                $query->whereBetween('lat', [$bbox[0], $bbox[2]])
                    ->whereBetween('lng', [$bbox[1], $bbox[3]]);
            }
        }

        if (!empty($filters['near'])) {
            $near = explode(',', $filters['near']);
            if (count($near) === 2) {
                $radiusKm = $filters['radius'] ?? 10;
                $query->nearLocation($near[0], $near[1], $radiusKm);
            }
        }

        // Apply other filters
        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->byVendor($filters['vendor_id']);
        }

        return $query->select([
                'id',
                'title',
                'lat',
                'lng',
                'type',
                'monthly_price',
                'weekly_price',
                'enable_weekly_booking',
            ])
            ->get()
            ->map(function ($hoarding) {
                return [
                    'id' => $hoarding->id,
                    'title' => $hoarding->title,
                    'lat' => (float) $hoarding->lat,
                    'lng' => (float) $hoarding->lng,
                    'type' => $hoarding->type,
                    'price' => $hoarding->monthly_price,
                    'weekly_price' => $hoarding->weekly_price,
                ];
            });
    }
}
