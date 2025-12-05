<?php

namespace Modules\Quotations\Services;

use App\Models\Quotation;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Quotations\Repositories\Contracts\QuotationRepositoryInterface;
use Modules\Quotations\Events\QuotationApproved;

class QuotationService
{
    protected QuotationRepositoryInterface $repository;

    public function __construct(QuotationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new quotation with auto-versioning
     */
    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $offer = Offer::with(['enquiry.hoarding', 'enquiry.customer', 'vendor'])->findOrFail($data['offer_id']);

            // Calculate next version
            $nextVersion = $this->repository->getLatestVersion($offer->id) + 1;

            // Auto-assign IDs from offer
            $data['customer_id'] = $offer->enquiry->customer_id;
            $data['vendor_id'] = $offer->vendor_id;
            $data['version'] = $nextVersion;

            // Calculate grand total if not provided
            if (!isset($data['grand_total'])) {
                $totalAmount = (float) ($data['total_amount'] ?? 0);
                $tax = (float) ($data['tax'] ?? 0);
                $discount = (float) ($data['discount'] ?? 0);
                $data['grand_total'] = $totalAmount + $tax - $discount;
            }

            // Set default status
            if (!isset($data['status'])) {
                $data['status'] = Quotation::STATUS_DRAFT;
            }

            return $this->repository->create($data);
        });
    }

    /**
     * Create quotation from accepted offer
     */
    public function createFromOffer(int $offerId, array $additionalData = []): Quotation
    {
        $offer = Offer::with(['enquiry.hoarding', 'enquiry.customer'])->findOrFail($offerId);

        // Build items from offer
        $items = [
            [
                'description' => $offer->price_snapshot['hoarding_title'] ?? 'Hoarding Advertisement',
                'quantity' => $offer->price_snapshot['duration_days'] ?? 1,
                'unit' => 'days',
                'rate' => (float) $offer->price,
                'amount' => (float) $offer->price * ($offer->price_snapshot['duration_days'] ?? 1),
            ]
        ];

        $totalAmount = $items[0]['amount'];
        $tax = $additionalData['tax'] ?? ($totalAmount * 0.18); // Default 18% tax
        $discount = $additionalData['discount'] ?? 0;

        $data = array_merge([
            'offer_id' => $offerId,
            'items' => $items,
            'total_amount' => $totalAmount,
            'tax' => $tax,
            'discount' => $discount,
            'notes' => $additionalData['notes'] ?? 'Quotation generated from accepted offer.',
        ], $additionalData);

        return $this->createQuotation($data);
    }

    /**
     * Send quotation (draft -> sent)
     */
    public function sendQuotation(int $quotationId): Quotation
    {
        return DB::transaction(function () use ($quotationId) {
            $quotation = $this->repository->find($quotationId);

            if (!$quotation) {
                throw new \Exception('Quotation not found');
            }

            if (!$quotation->canSend()) {
                throw new \Exception('Only draft quotations can be sent');
            }

            $quotation->status = Quotation::STATUS_SENT;
            $quotation->save();

            return $quotation->fresh();
        });
    }

    /**
     * Approve quotation and capture immutable snapshot
     */
    public function approveQuotation(int $quotationId): Quotation
    {
        return DB::transaction(function () use ($quotationId) {
            $quotation = $this->repository->find($quotationId);

            if (!$quotation) {
                throw new \Exception('Quotation not found');
            }

            if (!$quotation->canApprove()) {
                throw new \Exception('Only sent quotations can be approved');
            }

            // Capture immutable snapshot
            $snapshot = [
                'quotation_id' => $quotation->id,
                'version' => $quotation->version,
                'offer_id' => $quotation->offer_id,
                'customer_id' => $quotation->customer_id,
                'customer_name' => $quotation->customer->name,
                'customer_email' => $quotation->customer->email,
                'vendor_id' => $quotation->vendor_id,
                'vendor_name' => $quotation->vendor->name,
                'vendor_email' => $quotation->vendor->email,
                'items' => $quotation->items,
                'total_amount' => (float) $quotation->total_amount,
                'tax' => (float) $quotation->tax,
                'discount' => (float) $quotation->discount,
                'grand_total' => (float) $quotation->grand_total,
                'notes' => $quotation->notes,
                'approved_at' => now()->toIso8601String(),
            ];

            $quotation->status = Quotation::STATUS_APPROVED;
            $quotation->approved_snapshot = $snapshot;
            $quotation->approved_at = now();
            $quotation->save();

            // Dispatch event
            event(new QuotationApproved($quotation));

            return $quotation->fresh();
        });
    }

    /**
     * Reject quotation
     */
    public function rejectQuotation(int $quotationId): Quotation
    {
        $quotation = $this->repository->find($quotationId);

        if (!$quotation) {
            throw new \Exception('Quotation not found');
        }

        if (!$quotation->isSent()) {
            throw new \Exception('Only sent quotations can be rejected');
        }

        $quotation->status = Quotation::STATUS_REJECTED;
        $quotation->save();

        return $quotation->fresh();
    }

    /**
     * Create new version (revision)
     */
    public function createRevision(int $quotationId, array $changes = []): Quotation
    {
        $originalQuotation = $this->repository->find($quotationId);

        if (!$originalQuotation) {
            throw new \Exception('Quotation not found');
        }

        if (!$originalQuotation->canRevise()) {
            throw new \Exception('This quotation cannot be revised');
        }

        // Mark original as revised
        $originalQuotation->status = Quotation::STATUS_REVISED;
        $originalQuotation->save();

        // Create new version with changes
        $data = array_merge([
            'offer_id' => $originalQuotation->offer_id,
            'items' => $originalQuotation->items,
            'total_amount' => $originalQuotation->total_amount,
            'tax' => $originalQuotation->tax,
            'discount' => $originalQuotation->discount,
            'notes' => $originalQuotation->notes,
        ], $changes);

        return $this->createQuotation($data);
    }

    /**
     * Find quotation by ID
     */
    public function find(int $id): ?Quotation
    {
        return $this->repository->find($id);
    }

    /**
     * Get quotations by offer
     */
    public function getByOffer(int $offerId): Collection
    {
        return $this->repository->getByOffer($offerId);
    }

    /**
     * Get vendor's quotations
     */
    public function getVendorQuotations(): Collection
    {
        return $this->repository->getByVendor(Auth::id());
    }

    /**
     * Get customer's quotations
     */
    public function getCustomerQuotations(): Collection
    {
        return $this->repository->getByCustomer(Auth::id());
    }

    /**
     * Get all quotations with filters
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Check if user can view quotation
     */
    public function canView(Quotation $quotation, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Vendor can view own
        if ($user->hasRole('vendor') && $quotation->vendor_id === $user->id) {
            return true;
        }

        // Customer can view own (but not drafts)
        if ($user->hasRole('customer') && $quotation->customer_id === $user->id) {
            return !$quotation->isDraft();
        }

        return false;
    }

    /**
     * Check if user can edit quotation
     */
    public function canEdit(Quotation $quotation, $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Only vendor can edit, and only drafts
        return $user->hasRole('vendor') 
            && $quotation->vendor_id === $user->id 
            && $quotation->isDraft();
    }
}
