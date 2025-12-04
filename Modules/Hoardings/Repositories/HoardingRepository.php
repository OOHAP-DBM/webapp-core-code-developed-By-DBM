<?php

namespace Modules\Hoardings\Repositories;

use App\Models\Hoarding;
use Modules\Hoardings\Repositories\Contracts\HoardingRepositoryInterface;
use Modules\Shared\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
        $query = $this->model->with('vendor');

        // Apply filters
        if (!empty($filters['vendor_id'])) {
            $query->byVendor($filters['vendor_id']);
        }

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
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
        $query->orderBy($sortBy, $sortOrder);

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
    public function create(array $data)
    {
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
}
