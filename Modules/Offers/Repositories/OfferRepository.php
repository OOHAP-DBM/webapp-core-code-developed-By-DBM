<?php

namespace Modules\Offers\Repositories;

use Modules\Offers\Models\Offer;
use Modules\Offers\Models\OfferItem;
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

    public function findWithItems(int $id): ?Offer
    {
        return Offer::with([
            'enquiry.customer',
            'vendor',
            'items.hoarding',
            'items.enquiryItem',
        ])->find($id);
    }

    /**
     * Get offers by enquiry
     */
    public function getByEnquiry(int $enquiryId): Collection
    {
        return Offer::with(['vendor', 'items.hoarding'])
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
   public function getLatestVersion(int $enquiryId, int $vendorId): int
    {
        return (int) Offer::where('enquiry_id', $enquiryId)
            ->where('vendor_id', $vendorId)
            ->max('version');
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
        $query = Offer::with(['enquiry.customer', 'vendor', 'items.hoarding']);
 
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['enquiry_id'])) {
            $query->where('enquiry_id', $filters['enquiry_id']);
        }
        if (isset($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
        if (!empty($filters['active'])) {
            $query->active();
        }
 
        return $query->orderByDesc('created_at')->get();
    }
 
    public function markExpired(): int
    {
        return Offer::where('status', Offer::STATUS_SENT)
            ->where(function ($q) {
                $q->whereNotNull('expires_at')->where('expires_at', '<', now())
                  ->orWhereNotNull('valid_until')->where('valid_until', '<', now());
            })
            ->update(['status' => Offer::STATUS_EXPIRED]);
    }

     /* ════════════════════════════════════════
       OFFER ITEMS
    ════════════════════════════════════════ */
 
    public function createItem(array $data): \Modules\Offers\Models\OfferItem
    {
        return OfferItem::create($data);
    }
 
    public function deleteItems(int $offerId): void
    {
        OfferItem::where('offer_id', $offerId)->delete();
    }
}
