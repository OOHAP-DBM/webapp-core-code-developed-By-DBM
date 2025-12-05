<?php

namespace Modules\Offers\Repositories;

use App\Models\Offer;
use Modules\Offers\Repositories\Contracts\OfferRepositoryInterface;
use Illuminate\Support\Collection;

class OfferRepository implements OfferRepositoryInterface
{
    /**
     * Create a new offer
     */
    public function create(array $data): Offer
    {
        return Offer::create($data);
    }

    /**
     * Find offer by ID
     */
    public function find(int $id): ?Offer
    {
        return Offer::with(['enquiry.customer', 'enquiry.hoarding', 'vendor'])
            ->find($id);
    }

    /**
     * Get offers by enquiry
     */
    public function getByEnquiry(int $enquiryId): Collection
    {
        return Offer::with(['vendor', 'enquiry'])
            ->where('enquiry_id', $enquiryId)
            ->orderBy('version', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get offers by vendor
     */
    public function getByVendor(int $vendorId): Collection
    {
        return Offer::with(['enquiry.customer', 'enquiry.hoarding'])
            ->where('vendor_id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get latest version for an enquiry
     */
    public function getLatestVersion(int $enquiryId): int
    {
        return Offer::where('enquiry_id', $enquiryId)
            ->max('version') ?? 0;
    }

    /**
     * Get specific version of offer for enquiry
     */
    public function getVersion(int $enquiryId, int $version): ?Offer
    {
        return Offer::with(['enquiry.customer', 'enquiry.hoarding', 'vendor'])
            ->where('enquiry_id', $enquiryId)
            ->where('version', $version)
            ->first();
    }

    /**
     * Update offer status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $offer = Offer::find($id);
        
        if (!$offer) {
            return false;
        }

        $offer->status = $status;
        return $offer->save();
    }

    /**
     * Get all offers with filters
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Offer::with(['enquiry.customer', 'enquiry.hoarding', 'vendor']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by enquiry ID
        if (isset($filters['enquiry_id'])) {
            $query->where('enquiry_id', $filters['enquiry_id']);
        }

        // Filter by vendor ID
        if (isset($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        // Filter by date range
        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        // Filter active (sent and not expired)
        if (isset($filters['active']) && $filters['active']) {
            $query->active();
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Mark expired offers
     */
    public function markExpired(): int
    {
        return Offer::where('status', Offer::STATUS_SENT)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now())
            ->update(['status' => Offer::STATUS_EXPIRED]);
    }
}
