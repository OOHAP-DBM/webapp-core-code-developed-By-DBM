<?php

namespace Modules\Quotations\Services;

use Modules\Quotations\Models\Quotation;
use App\Models\Offer;
use Modules\Threads\Models\ThreadMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuotationWorkflowService
{
    /**
     * Create a new quotation version
     */
    public function createQuotationVersion(array $data): Quotation
    {
        DB::beginTransaction();
        try {
            $offer = Offer::with(['enquiry.thread', 'enquiry.customer'])->findOrFail($data['offer_id']);
            
            // Get next version number
            $version = $this->getNextVersionNumber($data['offer_id']);
            
            // Calculate totals
            $calculations = $this->calculateTotals($data);
            
            // Create quotation
            $quotation = Quotation::create([
                'offer_id' => $data['offer_id'],
                'customer_id' => $offer->enquiry->customer_id,
                'vendor_id' => $data['vendor_id'],
                'version' => $version,
                'items' => $data['items'] ?? [],
                'total_amount' => $calculations['total_amount'],
                'tax' => $calculations['tax'],
                'discount' => $calculations['discount'],
                'grand_total' => $calculations['grand_total'],
                'status' => $data['status'] ?? Quotation::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            Log::info('Quotation version created', [
                'quotation_id' => $quotation->id,
                'offer_id' => $data['offer_id'],
                'version' => $version,
                'grand_total' => $calculations['grand_total'],
            ]);

            return $quotation->load(['offer', 'customer', 'vendor']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create quotation version', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Send quotation to customer via thread
     */
    public function sendQuotationViaThread(int $quotationId, ?string $message = null): Quotation
    {
        DB::beginTransaction();
        try {
            $quotation = Quotation::with(['offer.enquiry.thread'])->findOrFail($quotationId);
            
            if (!$quotation->isDraft()) {
                throw new \Exception('Only draft quotations can be sent');
            }

            // Update quotation status
            $quotation->update(['status' => Quotation::STATUS_SENT]);

            // Post quotation in thread
            $thread = $quotation->offer->enquiry->thread;
            if ($thread) {
                ThreadMessage::create([
                    'thread_id' => $thread->id,
                    'sender_id' => $quotation->vendor_id,
                    'sender_type' => ThreadMessage::SENDER_VENDOR,
                    'message_type' => ThreadMessage::TYPE_QUOTATION,
                    'message' => $message ?? "New quotation (Version {$quotation->version}) submitted: ₹" . number_format($quotation->grand_total, 2),
                    'quotation_id' => $quotation->id,
                    'is_read_customer' => false,
                    'is_read_vendor' => true,
                ]);

                $thread->update(['last_message_at' => now()]);
                $thread->incrementUnread('customer');
            }

            DB::commit();

            Log::info('Quotation sent via thread', [
                'quotation_id' => $quotation->id,
                'thread_id' => $thread->id ?? null,
            ]);

            return $quotation->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send quotation', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Approve quotation - freeze with immutable snapshot, proceed to booking
     */
    public function approveQuotationAndFreeze(int $quotationId, int $customerId): Quotation
    {
        DB::beginTransaction();
        try {
            $quotation = Quotation::with(['offer.enquiry.thread'])->findOrFail($quotationId);
            
            // Authorization check
            if ($quotation->customer_id !== $customerId) {
                throw new \Exception('Unauthorized: You do not own this quotation');
            }

            if ($quotation->status !== Quotation::STATUS_SENT) {
                throw new \Exception('Only sent quotations can be approved. Current status: ' . $quotation->status);
            }

            // Create frozen immutable snapshot
            $frozenSnapshot = $this->createFrozenSnapshot($quotation);
            
            $quotation->update([
                'status' => Quotation::STATUS_APPROVED,
                'approved_snapshot' => $frozenSnapshot,
                'approved_at' => now(),
            ]);

            // Post approval message in thread
            $thread = $quotation->offer->enquiry->thread;
            if ($thread) {
                ThreadMessage::create([
                    'thread_id' => $thread->id,
                    'sender_id' => $customerId,
                    'sender_type' => ThreadMessage::SENDER_CUSTOMER,
                    'message_type' => ThreadMessage::TYPE_SYSTEM,
                    'message' => "✅ Quotation approved! Version {$quotation->version} - Grand Total: ₹" . number_format($quotation->grand_total, 2) . "\nReady to proceed with booking.",
                    'quotation_id' => $quotation->id,
                    'is_read_customer' => true,
                    'is_read_vendor' => false,
                ]);

                $thread->update(['last_message_at' => now()]);
                $thread->incrementUnread('vendor');
            }

            DB::commit();

            Log::info('Quotation approved and frozen', [
                'quotation_id' => $quotation->id,
                'customer_id' => $customerId,
                'grand_total' => $quotation->grand_total,
            ]);

            return $quotation->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve quotation', [
                'quotation_id' => $quotationId,
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject quotation with reason
     */
    public function rejectQuotationViaThread(int $quotationId, int $customerId, ?string $reason = null): Quotation
    {
        DB::beginTransaction();
        try {
            $quotation = Quotation::with(['offer.enquiry.thread'])->findOrFail($quotationId);
            
            if ($quotation->customer_id !== $customerId) {
                throw new \Exception('Unauthorized: You do not own this quotation');
            }

            if ($quotation->status !== Quotation::STATUS_SENT) {
                throw new \Exception('Only sent quotations can be rejected');
            }

            $quotation->update(['status' => Quotation::STATUS_REJECTED]);

            // Post rejection in thread
            $thread = $quotation->offer->enquiry->thread;
            if ($thread) {
                $message = "❌ Quotation rejected - Version {$quotation->version}";
                if ($reason) {
                    $message .= "\nReason: {$reason}";
                }

                ThreadMessage::create([
                    'thread_id' => $thread->id,
                    'sender_id' => $customerId,
                    'sender_type' => ThreadMessage::SENDER_CUSTOMER,
                    'message_type' => ThreadMessage::TYPE_SYSTEM,
                    'message' => $message,
                    'quotation_id' => $quotation->id,
                    'is_read_customer' => true,
                    'is_read_vendor' => false,
                ]);

                $thread->update(['last_message_at' => now()]);
                $thread->incrementUnread('vendor');
            }

            DB::commit();

            Log::info('Quotation rejected', [
                'quotation_id' => $quotation->id,
                'customer_id' => $customerId,
                'reason' => $reason,
            ]);

            return $quotation->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject quotation', [
                'quotation_id' => $quotationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Revise quotation (create new version)
     */
    public function reviseQuotation(int $quotationId, array $data): Quotation
    {
        $oldQuotation = Quotation::findOrFail($quotationId);
        
        // Mark old as revised
        $oldQuotation->update(['status' => Quotation::STATUS_REVISED]);

        // Create new version
        $newData = array_merge([
            'offer_id' => $oldQuotation->offer_id,
            'vendor_id' => $oldQuotation->vendor_id,
            'items' => $oldQuotation->items,
            'status' => Quotation::STATUS_DRAFT,
        ], $data);

        return $this->createQuotationVersion($newData);
    }

    /**
     * Update draft quotation
     */
    public function updateDraftQuotation(int $quotationId, array $data): Quotation
    {
        $quotation = Quotation::findOrFail($quotationId);
        
        if (!$quotation->isDraft()) {
            throw new \Exception('Only draft quotations can be updated');
        }

        // Recalculate if items changed
        if (isset($data['items']) || isset($data['tax']) || isset($data['discount'])) {
            $calculations = $this->calculateTotals($data, $quotation);
            
            $quotation->update([
                'items' => $data['items'] ?? $quotation->items,
                'total_amount' => $calculations['total_amount'],
                'tax' => $calculations['tax'],
                'discount' => $calculations['discount'],
                'grand_total' => $calculations['grand_total'],
                'notes' => $data['notes'] ?? $quotation->notes,
            ]);
        } else {
            $quotation->update(array_filter([
                'notes' => $data['notes'] ?? null,
            ]));
        }

        Log::info('Draft quotation updated', ['quotation_id' => $quotationId]);

        return $quotation->fresh();
    }

    /**
     * Get quotation versions for offer
     */
    public function getQuotationVersions(int $offerId)
    {
        return Quotation::with(['vendor'])
            ->where('offer_id', $offerId)
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Calculate totals from items
     */
    protected function calculateTotals(array $data, ?Quotation $existingQuotation = null): array
    {
        $items = $data['items'] ?? $existingQuotation->items ?? [];
        
        // Calculate line items total
        $totalAmount = 0;
        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $rate = $item['rate'] ?? 0;
            $totalAmount += $quantity * $rate;
        }

        // Get tax and discount
        $taxRate = $data['tax_rate'] ?? 0;
        $discount = $data['discount'] ?? $existingQuotation->discount ?? 0;

        // Calculate tax
        $tax = ($totalAmount * $taxRate) / 100;

        // Calculate grand total
        $grandTotal = $totalAmount + $tax - $discount;

        return [
            'total_amount' => round($totalAmount, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Create frozen snapshot on approval
     */
    protected function createFrozenSnapshot(Quotation $quotation): array
    {
        return [
            'quotation_details' => [
                'quotation_id' => $quotation->id,
                'version' => $quotation->version,
                'items' => $quotation->items,
                'total_amount' => $quotation->total_amount,
                'tax' => $quotation->tax,
                'discount' => $quotation->discount,
                'grand_total' => $quotation->grand_total,
                'notes' => $quotation->notes,
            ],
            'offer_snapshot' => $quotation->offer->price_snapshot ?? [],
            'approval' => [
                'approved_at' => now()->toDateTimeString(),
                'approved_by' => $quotation->customer_id,
                'frozen' => true,
                'status' => 'immutable',
            ],
        ];
    }

    /**
     * Get next version number
     */
    protected function getNextVersionNumber(int $offerId): int
    {
        $maxVersion = Quotation::where('offer_id', $offerId)->max('version');
        return ($maxVersion ?? 0) + 1;
    }

    /**
     * Delete draft quotation
     */
    public function deleteDraftQuotation(int $quotationId, int $vendorId): bool
    {
        $quotation = Quotation::where('id', $quotationId)
            ->where('vendor_id', $vendorId)
            ->firstOrFail();

        if (!$quotation->isDraft()) {
            throw new \Exception('Only draft quotations can be deleted');
        }

        $quotation->delete();

        Log::info('Draft quotation deleted', [
            'quotation_id' => $quotationId,
            'vendor_id' => $vendorId,
        ]);

        return true;
    }
}
