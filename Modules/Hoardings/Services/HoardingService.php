<?php

namespace Modules\Hoardings\Services;

use App\Models\Hoarding;
use App\Models\HoardingGeo;
use Modules\Hoardings\Repositories\Contracts\HoardingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Modules\DOOH\Models\DOOHScreen;
use Illuminate\Support\Facades\Request;

class HoardingService
{
    /**
     * @var HoardingRepositoryInterface
     */
    protected $hoardingRepository;

    /**
     * HoardingService constructor.
     *
     * @param HoardingRepositoryInterface $hoardingRepository
     */
    public function __construct(HoardingRepositoryInterface $hoardingRepository)
    {
        $this->hoardingRepository = $hoardingRepository;
    }

    /**
     * Get all hoardings with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->hoardingRepository->getAll($filters, $perPage);
    }

    /**
     * Get a hoarding by ID.
     *
     * @param int $id
     * @return Hoarding|null
     */
    public function getById(int $id): ?Hoarding
    {
        return $this->hoardingRepository->findById($id);
    }

    /**
     * Create a new hoarding.
     *
     * @param array $data
     * @return Hoarding
     */
    public function create(array $data): Hoarding
    {
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = Hoarding::STATUS_DRAFT;
        }

        // Validate weekly booking settings
        if (isset($data['enable_weekly_booking']) && $data['enable_weekly_booking']) {
            if (empty($data['weekly_price'])) {
                throw new \InvalidArgumentException('Weekly price is required when weekly booking is enabled.');
            }
        }

        return $this->hoardingRepository->create($data);
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
        // Validate weekly booking settings
        if (isset($data['enable_weekly_booking']) && $data['enable_weekly_booking']) {
            if (empty($data['weekly_price'])) {
                throw new \InvalidArgumentException('Weekly price is required when weekly booking is enabled.');
            }
        }

        return $this->hoardingRepository->update($id, $data);
    }

    /**
     * Delete a hoarding.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->hoardingRepository->delete($id);
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
        return $this->hoardingRepository->getByVendor($vendorId, $perPage);
    }

    /**
     * Search hoardings.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->hoardingRepository->search($keyword, $perPage);
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
        return $this->hoardingRepository->getNearLocation($lat, $lng, $radiusKm, $perPage);
    }

    /**
     * Get active hoardings.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->hoardingRepository->getActive($perPage);
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
        // Validate status
        if (!in_array($status, array_keys(Hoarding::getStatuses()))) {
            throw new \InvalidArgumentException('Invalid status provided.');
        }

        return $this->hoardingRepository->updateStatus($id, $status);
    }

    /**
     * Check if hoarding is available for booking.
     *
     * @param int $id
     * @return bool
     */
    public function isAvailable(int $id): bool
    {
        $hoarding = $this->getById($id);
        
        if (!$hoarding) {
            return false;
        }

        return $hoarding->isActive();
    }

    /**
     * Get hoarding types.
     *
     * @return array
     */
    public function getTypes(): array
    {
        return Hoarding::getTypes();
    }

    /**
     * Get hoarding statuses.
     *
     * @return array
     */
    public function getStatuses(): array
    {
        return Hoarding::getStatuses();
    }

    /**
     * Calculate booking price based on duration.
     *
     * @param Hoarding $hoarding
     * @param int $weeks
     * @param int $months
     * @return float
     */
    public function calculatePrice(Hoarding $hoarding, int $weeks = 0, int $months = 0): float
    {
        $totalPrice = 0;

        // Calculate monthly price
        if ($months > 0) {
            $totalPrice += $hoarding->monthly_price * $months;
        }

        // Calculate weekly price
        if ($weeks > 0) {
            if (!$hoarding->supportsWeeklyBooking()) {
                throw new \InvalidArgumentException('This hoarding does not support weekly booking.');
            }
            $totalPrice += $hoarding->weekly_price * $weeks;
        }

        return $totalPrice;
    }

    /**
     * Get vendor statistics.
     *
     * @param int $vendorId
     * @return array
     */
    public function getVendorStatistics(int $vendorId): array
    {
        $cacheKey = "vendor_hoarding_stats_{$vendorId}";

        return Cache::remember($cacheKey, 3600, function () use ($vendorId) {
            $hoardings = Hoarding::byVendor($vendorId)->get();

            return [
                'total' => $hoardings->count(),
                'active' => $hoardings->where('status', Hoarding::STATUS_ACTIVE)->count(),
                'draft' => $hoardings->where('status', Hoarding::STATUS_DRAFT)->count(),
                'pending' => $hoardings->where('status', Hoarding::STATUS_PENDING_APPROVAL)->count(),
                'inactive' => $hoardings->where('status', Hoarding::STATUS_INACTIVE)->count(),
                'by_type' => $hoardings->groupBy('type')->map->count(),
            ];
        });
    }

    /**
     * Clear vendor statistics cache.
     *
     * @param int $vendorId
     * @return void
     */
    public function clearVendorStatistics(int $vendorId): void
    {
        Cache::forget("vendor_hoarding_stats_{$vendorId}");
    }

    /**
     * Attach or update geo fence for a hoarding.
     *
     * @param int $hoardingId
     * @param array $geojson
     * @return HoardingGeo
     */
    public function attachGeoFence(int $hoardingId, array $geojson): HoardingGeo
    {
        $hoarding = $this->getById($hoardingId);
        
        if (!$hoarding) {
            throw new \InvalidArgumentException('Hoarding not found.');
        }

        // Create or update geo fence
        $geo = HoardingGeo::updateOrCreate(
            ['hoarding_id' => $hoardingId],
            ['geojson' => $geojson]
        );

        // Calculate and save bounding box
        $geo->updateBoundingBox();

        return $geo;
    }

    /**
     * Update geo fence for a hoarding.
     *
     * @param int $hoardingId
     * @param array $geojson
     * @return HoardingGeo|null
     */
    public function updateGeoFence(int $hoardingId, array $geojson): ?HoardingGeo
    {
        return $this->attachGeoFence($hoardingId, $geojson);
    }

    /**
     * Search hoardings with geo filtering.
     * Combines polygon check and Haversine fallback.
     *
     * @param array $filters
     * @return Collection
     */
    public function searchWithGeo(array $filters = []): Collection
    {
        // If bounding box is provided
        if (!empty($filters['bbox'])) {
            $bbox = explode(',', $filters['bbox']);
            if (count($bbox) === 4) {
                return $this->hoardingRepository->getByBoundingBox(
                    (float) $bbox[0],
                    (float) $bbox[2],
                    (float) $bbox[1],
                    (float) $bbox[3]
                );
            }
        }

        // If near point with radius is provided
        if (!empty($filters['near'])) {
            $near = explode(',', $filters['near']);
            if (count($near) === 2) {
                $radiusKm = $filters['radius'] ?? 10;
                
                // Get hoardings within radius
                $hoardings = $this->hoardingRepository->getNearbyWithRadius(
                    (float) $near[0],
                    (float) $near[1],
                    (float) $radiusKm
                );

                // Filter by geo fence if exists
                $lat = (float) $near[0];
                $lng = (float) $near[1];

                return $hoardings->filter(function ($hoarding) use ($lat, $lng, $radiusKm) {
                    // Check if hoarding has geo fence
                    if ($hoarding->geo && $hoarding->geo->geojson) {
                        // Use polygon check
                        return $hoarding->geo->isPointInPolygon($lat, $lng);
                    }
                    
                    // Fallback to Haversine distance
                    return $hoarding->haversineDistance($lat, $lng) <= $radiusKm;
                });
            }
        }

        // Default: return all active hoardings
        return $this->hoardingRepository->getActive()->getCollection();
    }

    /**
     * Get map pins with optional filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getMapPins(array $filters = []): Collection
    {
        return $this->hoardingRepository->getMapPins($filters);
    }

    // Modules/Hoardings/Services/HoardingService.php

    public function getActiveHoardings(array $params = [])
    {
        $perPage = $params['per_page'] ?? 12;

        // 1. Fetch Static Hoardings
        $static = Hoarding::where('status', 'active')
            ->with(['vendor', 'media'])
            ->get()
            ->map(function ($item) {
                $item->is_digital = false; // Flag to identify in Blade
                return $item;
            });

        // 2. Fetch Digital (DOOH) Hoardings
        $digital = DOOHScreen::where('status', 'active')
            ->with(['vendor', 'media'])
            ->get()
            ->map(function ($item) {
                $item->is_digital = true; // Flag to identify in Blade
                return $item;
            });

        // 3. Merge and Sort
        $merged = $static->concat($digital)->sortByDesc('created_at');

        // 4. Manually Paginate the merged collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $merged->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator(
            $currentItems,
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => Request::url(), 'query' => Request::query()]
        );
    }
}
