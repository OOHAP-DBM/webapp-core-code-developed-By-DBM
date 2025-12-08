<?php

namespace Modules\Hoardings\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface HoardingRepositoryInterface
{
    /**
     * Get all hoardings with optional filters and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a hoarding by ID.
     *
     * @param int $id
     * @return \App\Models\Hoarding|null
     */
    public function findById(int $id);

    /**
     * Create a new hoarding.
     *
     * @param array $data
     * @return \App\Models\Hoarding
     */
    public function create(array $data);

    /**
     * Update a hoarding.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a hoarding (soft delete).
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get hoardings by vendor.
     *
     * @param int $vendorId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Search hoardings by keyword.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get hoardings near a location.
     *
     * @param float $lat
     * @param float $lng
     * @param float $radiusKm
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getNearLocation(float $lat, float $lng, float $radiusKm = 10, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active hoardings.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Update hoarding status.
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Get hoardings within a bounding box.
     *
     * @param float $minLat
     * @param float $maxLat
     * @param float $minLng
     * @param float $maxLng
     * @return SupportCollection
     */
    public function getByBoundingBox(float $minLat, float $maxLat, float $minLng, float $maxLng): SupportCollection;

    /**
     * Get hoardings within radius with precise Haversine calculation.
     *
     * @param float $lat
     * @param float $lng
     * @param float $radiusKm
     * @return SupportCollection
     */
    public function getNearbyWithRadius(float $lat, float $lng, float $radiusKm = 10): SupportCollection;

    /**
     * Get compact map pins (minimal data for map markers).
     *
     * @param array $filters
     * @return SupportCollection
     */
    public function getMapPins(array $filters = []): SupportCollection;
}

