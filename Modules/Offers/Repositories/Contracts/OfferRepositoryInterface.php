<?php

namespace Modules\Offers\Repositories\Contracts;

use App\Models\Offer;
use Illuminate\Support\Collection;
use Modules\Offers\Models\OfferItem;

interface OfferRepositoryInterface
{
    /**
     * Create a new offer
     */
    public function create(array $data): Offer;

    /**
     * Find offer by ID
     */
    public function find(int $id): ?Offer;

     public function findWithItems(int $id): ?Offer;
    /**
     * Get offers by enquiry
     */
    public function getByEnquiry(int $enquiryId): Collection;

    /**
     * Get offers by vendor
     */
    public function getByVendor(int $vendorId): Collection;

    /**
     * Get latest version for an enquiry
     */
    public function getLatestVersion(int $enquiryId, int $vendorId): int;

    /**
     * Get specific version of offer for enquiry
     */
    public function getVersion(int $enquiryId, int $version): ?Offer;

    /**
     * Update offer status
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Get all offers with filters
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Mark expired offers
     */
    public function markExpired(): int;

       /* ── Items ── */
 
    public function createItem(array $data): \Modules\Offers\Models\OfferItem;
 
    public function deleteItems(int $offerId): void;
}
