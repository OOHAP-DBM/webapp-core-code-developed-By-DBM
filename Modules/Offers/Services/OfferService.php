<?php

namespace Modules\Offers\Services;

use Modules\Offers\Models\Offer;
use Modules\Enquiries\Models\Enquiry;
use Modules\Offers\Repositories\Contracts\OfferRepositoryInterface;
use Modules\Offers\Events\OfferSent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfferService
{
    protected OfferRepositoryInterface $repository;

    public function __construct(OfferRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new offer with automatic versioning and snapshot
     */
    public function createOffer(array $data): Offer
    {
        // Get the enquiry
        $enquiry = Enquiry::with(['hoarding', 'customer'])->findOrFail($data['enquiry_id']);

        // Get next version number
        $nextVersion = $this->repository->getLatestVersion($data['enquiry_id']) + 1;

        // Create immutable price snapshot
        $priceSnapshot = [
            // Enquiry details
            'enquiry_id' => $enquiry->id,
            'customer_name' => $enquiry->customer->name,
            'customer_email' => $enquiry->customer->email,
            'preferred_start_date' => $enquiry->preferred_start_date->format('Y-m-d'),
            'preferred_end_date' => $enquiry->preferred_end_date->format('Y-m-d'),
            'duration_days' => $enquiry->getDurationInDays(),
            'duration_type' => $enquiry->duration_type,
            
            // Hoarding snapshot from enquiry
            'hoarding_title' => $enquiry->getSnapshotValue('hoarding_title'),
            'hoarding_type' => $enquiry->getSnapshotValue('hoarding_type'),
            'hoarding_location' => $enquiry->getSnapshotValue('location'),
            'hoarding_dimensions' => [
                'width' => $enquiry->getSnapshotValue('width'),
                'height' => $enquiry->getSnapshotValue('height'),
            ],
            
            // Original hoarding prices (for reference)
            'original_price' => $enquiry->getSnapshotValue('price'),
            'original_weekly_price' => $enquiry->getSnapshotValue('weekly_price'),
            
            // This offer's pricing
            'offered_price' => $data['price'],
            'offered_price_type' => $data['price_type'],
            
            // Timestamp
            'snapshot_created_at' => now()->toDateTimeString(),
        ];

        $data['price_snapshot'] = $priceSnapshot;
        $data['vendor_id'] = $data['vendor_id'] ?? Auth::id();
        $data['version'] = $nextVersion;
        $data['status'] = $data['status'] ?? Offer::STATUS_DRAFT;

        return $this->repository->create($data);
    }

    /**
     * Send an offer (draft -> sent) and dispatch event
     */
    public function sendOffer(int $offerId): Offer
    {
        $offer = $this->repository->find($offerId);

        if (!$offer) {
            throw new \Exception('Offer not found');
        }

        if (!$offer->canSend()) {
            throw new \Exception('Only draft offers can be sent');
        }

        DB::beginTransaction();
        try {
            $this->repository->updateStatus($offerId, Offer::STATUS_SENT);
            $offer->refresh();

            // Dispatch event
            event(new OfferSent($offer));

            DB::commit();
            return $offer;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Accept an offer (customer)
     */
    public function acceptOffer(int $offerId): Offer
    {
        $offer = $this->repository->find($offerId);

        if (!$offer) {
            throw new \Exception('Offer not found');
        }

        if (!$offer->canAccept()) {
            throw new \Exception('This offer cannot be accepted (expired or not sent)');
        }

        DB::beginTransaction();
        try {
            $this->repository->updateStatus($offerId, Offer::STATUS_ACCEPTED);
            
            // Reject all other offers for this enquiry
            Offer::where('enquiry_id', $offer->enquiry_id)
                ->where('id', '!=', $offerId)
                ->where('status', Offer::STATUS_SENT)
                ->update(['status' => Offer::STATUS_REJECTED]);

            $offer->refresh();

            DB::commit();
            return $offer;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject an offer
     */
    public function rejectOffer(int $offerId): bool
    {
        $offer = $this->repository->find($offerId);

        if (!$offer || !$offer->isSent()) {
            throw new \Exception('Offer cannot be rejected');
        }

        return $this->repository->updateStatus($offerId, Offer::STATUS_REJECTED);
    }

    /**
     * Get offer by ID
     */
    public function find(int $id): ?Offer
    {
        return $this->repository->find($id);
    }

    /**
     * Get offers by enquiry
     */
    public function getByEnquiry(int $enquiryId): Collection
    {
        return $this->repository->getByEnquiry($enquiryId);
    }

    /**
     * Get offers by vendor
     */
    public function getVendorOffers(): Collection
    {
        return $this->repository->getByVendor(Auth::id());
    }

    /**
     * Get offers received by customer
     */
    public function getCustomerOffers(): Collection
    {
        return $this->repository->getAll([
            'status' => Offer::STATUS_SENT,
        ])->filter(function($offer) {
            return $offer->enquiry->customer_id === Auth::id();
        });
    }

    /**
     * Check and mark expired offers
     */
    public function checkExpiredOffers(): int
    {
        return $this->repository->markExpired();
    }

    /**
     * Get all offers with filters
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Check if user can view offer
     */
    public function canView(Offer $offer, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Vendor can view their own offers
        if ($offer->vendor_id === $user->id) {
            return true;
        }

        // Customer can view offers for their enquiries (if sent)
        if ($offer->enquiry->customer_id === $user->id && $offer->isSent()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can edit offer
     */
    public function canEdit(Offer $offer, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Only vendor who created it can edit, and only if draft
        return $offer->vendor_id === $user->id && $offer->isDraft();
    }
}

