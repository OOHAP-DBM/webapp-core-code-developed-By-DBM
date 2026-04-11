<?php

namespace Modules\Offers\Services;

use Modules\Offers\Models\Offer;
use Modules\Offers\Models\OfferItem;
use App\Models\Hoarding;
use Modules\Enquiries\Models\Enquiry;
use Modules\Enquiries\Models\EnquiryItem;
use Modules\Offers\Repositories\Contracts\OfferRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferService
{
    public function __construct(
        protected OfferRepositoryInterface $repository
    ) {}

    /* ════════════════════════════════════════════════════════════
       CREATE OFFER
       No price snapshot — snapshot is taken at quotation stage.
    ════════════════════════════════════════════════════════════ */

    /**
     * Create a new offer with its line items.
     *
     * $data shape:
     * [
     *   'enquiry_id'  => int,
     *   'vendor_id'   => int,          // defaults to Auth::id()
     *   'description' => string|null,
     *   'valid_days'  => int|null,     // sets valid_until
     *   'status'      => string,       // default: draft
     *   'items' => [
     *     [
     *       'enquiry_item_id'      => int|null,
     *       'hoarding_id'          => int,
     *       'hoarding_type'        => 'ooh'|'dooh',
     *       'package_id'           => int|null,
     *       'package_type'         => string|null,
     *       'package_label'        => string|null,
     *       'preferred_start_date' => 'Y-m-d',
     *       'preferred_end_date'   => 'Y-m-d',
     *       'duration_months'      => int|null,
     *       'price_per_month'      => float|null,
     *       'offered_price'        => float|null,
     *       'discount_percent'     => float|null,
     *       'services'             => array|null,
     *     ],
     *     ...
     *   ]
     * ]
     */
    public function createOffer(array $data): Offer
    {
        return DB::transaction(function () use ($data) {

            $vendorId  = $data['vendor_id'] ?? Auth::id();
            $enquiryId = $data['enquiry_id'];

            // Next version for this vendor on this enquiry
            $version = $this->repository->getLatestVersion($enquiryId, $vendorId) + 1;

            // Build offer header — no price_snapshot at this stage
            $offer = $this->repository->create([
                'enquiry_id'  => $enquiryId,
                'vendor_id'   => $vendorId,
                'description' => $data['description'] ?? null,
                'valid_until' => isset($data['valid_days'])
                    ? now()->addDays((int) $data['valid_days'])
                    : ($data['valid_until'] ?? null),
                'status'      => $data['status'] ?? Offer::STATUS_DRAFT,
                'version'     => $version,
                // price / price_type / price_snapshot intentionally omitted here
                // They get set during quotation stage
            ]);

            // Create line items
            $this->syncItems($offer, $data['items'] ?? []);

            Log::info('Offer created', [
                'offer_id'   => $offer->id,
                'enquiry_id' => $enquiryId,
                'vendor_id'  => $vendorId,
                'version'    => $version,
                'items'      => count($data['items'] ?? []),
            ]);

            return $offer->load(['items.hoarding', 'vendor', 'enquiry.customer']);
        });
    }

    /* ════════════════════════════════════════════════════════════
       UPDATE DRAFT OFFER
    ════════════════════════════════════════════════════════════ */

    public function updateOffer(int $offerId, array $data): Offer
    {
        return DB::transaction(function () use ($offerId, $data) {

            $offer = $this->repository->find($offerId);

            if (!$offer) {
                throw new \RuntimeException('Offer not found.');
            }

            if (!$offer->isDraft()) {
                throw new \RuntimeException('Only draft offers can be updated.');
            }

            $updateFields = array_filter([
                'description' => $data['description'] ?? $offer->description,
                'valid_until' => isset($data['valid_days'])
                    ? now()->addDays((int) $data['valid_days'])
                    : ($data['valid_until'] ?? $offer->valid_until),
            ], fn($v) => $v !== null);

            $offer->update($updateFields);

            // Re-sync items if provided
            if (!empty($data['items'])) {
                $this->syncItems($offer, $data['items']);
            }

            Log::info('Offer updated', ['offer_id' => $offerId]);

            return $offer->load(['items.hoarding', 'vendor', 'enquiry.customer']);
        });
    }

    /* ════════════════════════════════════════════════════════════
       SEND OFFER
    ════════════════════════════════════════════════════════════ */

    public function sendOffer(int $offerId): Offer
    {
        return DB::transaction(function () use ($offerId) {

            $offer = $this->repository->findWithItems($offerId);

            if (!$offer) {
                throw new \RuntimeException('Offer not found.');
            }

            if (!$offer->isDraft()) {
                throw new \RuntimeException('Only draft offers can be sent.');
            }

            if ($offer->items->isEmpty()) {
                throw new \RuntimeException('Cannot send an offer with no items.');
            }

            // Validate all items have dates
            foreach ($offer->items as $item) {
                if (!$item->preferred_start_date || !$item->preferred_end_date) {
                    throw new \RuntimeException(
                        "Item for hoarding #{$item->hoarding_id} is missing campaign dates."
                    );
                }
            }

            $this->repository->updateStatus($offerId, Offer::STATUS_SENT);
            $offer->refresh();

            Log::info('Offer sent', ['offer_id' => $offerId]);

            return $offer;
        });
    }

    /* ════════════════════════════════════════════════════════════
       ACCEPT OFFER
    ════════════════════════════════════════════════════════════ */

    public function acceptOffer(int $offerId, int $customerId): Offer
    {
        return DB::transaction(function () use ($offerId, $customerId) {

            $offer = $this->repository->findWithItems($offerId);

            if (!$offer) {
                throw new \RuntimeException('Offer not found.');
            }

            if ($offer->enquiry->customer_id !== $customerId) {
                throw new \RuntimeException('Unauthorized: you do not own this enquiry.');
            }

            if (!$offer->isSent()) {
                throw new \RuntimeException('Only sent offers can be accepted.');
            }

            if ($offer->isExpired()) {
                throw new \RuntimeException('This offer has expired and can no longer be accepted.');
            }

            $this->repository->updateStatus($offerId, Offer::STATUS_ACCEPTED);

            // Update enquiry status
            $offer->enquiry->update(['status' => Enquiry::STATUS_ACCEPTED]);

            Log::info('Offer accepted', ['offer_id' => $offerId, 'customer_id' => $customerId]);

            return $offer->fresh();
        });
    }

    /* ════════════════════════════════════════════════════════════
       REJECT OFFER
    ════════════════════════════════════════════════════════════ */

    public function rejectOffer(int $offerId, int $customerId): Offer
    {
        return DB::transaction(function () use ($offerId, $customerId) {

            $offer = $this->repository->find($offerId);

            if (!$offer) {
                throw new \RuntimeException('Offer not found.');
            }

            if ($offer->enquiry->customer_id !== $customerId) {
                throw new \RuntimeException('Unauthorized: you do not own this enquiry.');
            }

            if (!$offer->isSent()) {
                throw new \RuntimeException('Only sent offers can be rejected.');
            }

            $this->repository->updateStatus($offerId, Offer::STATUS_REJECTED);

            Log::info('Offer rejected', ['offer_id' => $offerId, 'customer_id' => $customerId]);

            return $offer->fresh();
        });
    }

    /* ════════════════════════════════════════════════════════════
       DELETE DRAFT
    ════════════════════════════════════════════════════════════ */

    public function deleteDraft(int $offerId, int $vendorId): void
    {
        $offer = $this->repository->find($offerId);

        if (!$offer || $offer->vendor_id !== $vendorId) {
            throw new \RuntimeException('Offer not found.');
        }

        if (!$offer->isDraft()) {
            throw new \RuntimeException('Only draft offers can be deleted.');
        }

        DB::transaction(function () use ($offerId) {
            $this->repository->deleteItems($offerId);
            Offer::destroy($offerId);
        });

        Log::info('Draft offer deleted', ['offer_id' => $offerId, 'vendor_id' => $vendorId]);
    }

    /* ════════════════════════════════════════════════════════════
       QUERY HELPERS
    ════════════════════════════════════════════════════════════ */

    public function find(int $id): ?Offer
    {
        return $this->repository->findWithItems($id);
    }

    public function getByEnquiry(int $enquiryId): Collection
    {
        return $this->repository->getByEnquiry($enquiryId);
    }

    public function getVendorOffers(int $vendorId = null): Collection
    {
        return $this->repository->getByVendor($vendorId ?? Auth::id());
    }

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }

    /* ════════════════════════════════════════════════════════════
       PRIVATE HELPERS
    ════════════════════════════════════════════════════════════ */

    /**
     * Delete existing items and insert fresh ones.
     */
    private function syncItems(Offer $offer, array $items): void
    {
        $this->repository->deleteItems($offer->id);

        foreach ($items as $itemData) {
            $hoarding = Hoarding::find($itemData['hoarding_id']);

            // Derive duration_months if not provided
            $durationMonths = $itemData['duration_months'] ?? null;
            if (!$durationMonths && !empty($itemData['preferred_start_date']) && !empty($itemData['preferred_end_date'])) {
                $days           = \Carbon\Carbon::parse($itemData['preferred_start_date'])
                    ->diffInDays(\Carbon\Carbon::parse($itemData['preferred_end_date'])) + 1;
                $durationMonths = (int) max(1, ceil($days / 30));
            }

            // price_per_month: use provided value, else fall back to hoarding's current price
            $pricePerMonth = $itemData['price_per_month']
                ?? $hoarding?->price_per_month
                ?? $hoarding?->monthly_rental
                ?? null;

            $this->repository->createItem([
                'offer_id'             => $offer->id,
                'enquiry_item_id'      => $itemData['enquiry_item_id'] ?? null,
                'hoarding_id'          => $itemData['hoarding_id'],
                'hoarding_type'        => $itemData['hoarding_type'] ?? 'ooh',
                'package_id'           => $itemData['package_id'] ?? null,
                'package_type'         => $itemData['package_type'] ?? null,
                'package_label'        => $itemData['package_label'] ?? null,
                'preferred_start_date' => $itemData['preferred_start_date'],
                'preferred_end_date'   => $itemData['preferred_end_date'],
                'duration_months'      => $durationMonths,
                'price_per_month'      => $pricePerMonth,
                'offered_price'        => $itemData['offered_price'] ?? null,
                'discount_percent'     => $itemData['discount_percent'] ?? null,
                'services'             => $itemData['services'] ?? null,
                'meta'                 => [
                    // Lightweight reference data — NOT a full snapshot
                    'hoarding_title'    => $hoarding?->title ?? $hoarding?->name,
                    'hoarding_type'     => $itemData['hoarding_type'] ?? 'ooh',
                    'display_location'  => $hoarding?->display_location ?? $hoarding?->city,
                ],
            ]);
        }
    }
}