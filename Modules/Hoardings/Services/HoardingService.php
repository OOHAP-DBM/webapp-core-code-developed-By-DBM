<?php

namespace Modules\Hoardings\Services;

use App\Models\Hoarding;
use Modules\Hoardings\Repositories\Contracts\HoardingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

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
}
