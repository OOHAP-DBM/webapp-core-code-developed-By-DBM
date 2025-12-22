<?php

namespace Modules\Offers\Services;

use App\Models\Offer;
use Modules\Enquiries\Models\Enquiry;
use Modules\Threads\Models\ThreadMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OfferWorkflowService
{
    /**
     * Create a new offer version with snapshot
     */
    public function createOfferVersion(array $data): Offer
    {
        DB::beginTransaction();
        try {
            $enquiry = Enquiry::with(['hoarding', 'customer'])->findOrFail($data['enquiry_id']);
            
            // Get next version number for this vendor
            $version = $this->getNextVersionNumber($data['enquiry_id'], $data['vendor_id']);
            
            // Create comprehensive price snapshot
            $priceSnapshot = $this->buildPriceSnapshot($enquiry, $data);
            
            // Create offer
            $offer = Offer::create([
                'enquiry_id' => $data['enquiry_id'],
                'vendor_id' => $data['vendor_id'],
                'price' => $data['price'],
                'price_type' => $data['price_type'] ?? Offer::PRICE_TOTAL,
                'price_snapshot' => $priceSnapshot,
                'description' => $data['description'] ?? null,
                'valid_until' => isset($data['valid_days']) 
                    ? now()->addDays($data['valid_days']) 
                    : ($data['valid_until'] ?? null),
                'status' => $data['status'] ?? Offer::STATUS_DRAFT,
                'version' => $version,
            ]);

            DB::commit();

            Log::info('Offer version created', [
                'offer_id' => $offer->id,
                'enquiry_id' => $data['enquiry_id'],
                'vendor_id' => $data['vendor_id'],
                'version' => $version,
                'price' => $data['price'],
            ]);

            return $offer->load(['enquiry', 'vendor']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create offer version', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Send offer to customer via thread
     */
    public function sendOfferViaThread(int $offerId, ?string $message = null): Offer
    {
        DB::beginTransaction();
        try {
            $offer = Offer::with(['enquiry.thread', 'vendor'])->findOrFail($offerId);
            
            if (!$offer->isDraft()) {
                throw new \Exception('Only draft offers can be sent');
            }

            // Update offer status to sent
            $offer->update(['status' => Offer::STATUS_SENT]);

            // Post offer message in thread
            if ($offer->enquiry->thread) {
                ThreadMessage::create([
                    'thread_id' => $offer->enquiry->thread->id,
                    'sender_id' => $offer->vendor_id,
                    'sender_type' => ThreadMessage::SENDER_VENDOR,
                    'message_type' => ThreadMessage::TYPE_OFFER,
                    'message' => $message ?? "New offer (Version {$offer->version}) submitted: ₹" . number_format($offer->price, 2),
                    'offer_id' => $offer->id,
                    'is_read_customer' => false,
                    'is_read_vendor' => true,
                ]);

                // Update thread last message time and unread count
                $offer->enquiry->thread->update(['last_message_at' => now()]);
                $offer->enquiry->thread->incrementUnread('customer');
            }

            DB::commit();

            Log::info('Offer sent via thread', [
                'offer_id' => $offer->id,
                'thread_id' => $offer->enquiry->thread->id ?? null,
            ]);

            return $offer->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send offer via thread', [
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Accept offer - freeze version with immutable snapshot
     */
    public function acceptOfferAndFreeze(int $offerId, int $customerId): Offer
    {
        DB::beginTransaction();
        try {
            $offer = Offer::with(['enquiry.thread'])->findOrFail($offerId);
            
            // Authorization check
            if ($offer->enquiry->customer_id !== $customerId) {
                throw new \Exception('Unauthorized: You do not own this enquiry');
            }

            if ($offer->status !== Offer::STATUS_SENT) {
                throw new \Exception('Only sent offers can be accepted. Current status: ' . $offer->status);
            }

            // Freeze offer by creating immutable snapshot
            $frozenSnapshot = $this->createFrozenSnapshot($offer);
            
            $offer->update([
                'status' => Offer::STATUS_ACCEPTED,
                'price_snapshot' => $frozenSnapshot,
            ]);

            // Update enquiry status to accepted
            $offer->enquiry->update(['status' => Enquiry::STATUS_ACCEPTED]);

            // Post acceptance message in thread
            if ($offer->enquiry->thread) {
                ThreadMessage::create([
                    'thread_id' => $offer->enquiry->thread->id,
                    'sender_id' => $customerId,
                    'sender_type' => ThreadMessage::SENDER_CUSTOMER,
                    'message_type' => ThreadMessage::TYPE_SYSTEM,
                    'message' => "✅ Offer accepted! Version {$offer->version} - ₹" . number_format($offer->price, 2),
                    'offer_id' => $offer->id,
                    'is_read_customer' => true,
                    'is_read_vendor' => false,
                ]);

                $offer->enquiry->thread->update(['last_message_at' => now()]);
                $offer->enquiry->thread->incrementUnread('vendor');
            }

            DB::commit();

            Log::info('Offer accepted and frozen', [
                'offer_id' => $offer->id,
                'customer_id' => $customerId,
                'frozen_snapshot_keys' => array_keys($frozenSnapshot),
            ]);

            return $offer->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept offer', [
                'offer_id' => $offerId,
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject offer with reason via thread
     */
    public function rejectOfferViaThread(int $offerId, int $customerId, ?string $reason = null): Offer
    {
        DB::beginTransaction();
        try {
            $offer = Offer::with(['enquiry.thread'])->findOrFail($offerId);
            
            if ($offer->enquiry->customer_id !== $customerId) {
                throw new \Exception('Unauthorized: You do not own this enquiry');
            }

            if ($offer->status !== Offer::STATUS_SENT) {
                throw new \Exception('Only sent offers can be rejected');
            }

            $offer->update(['status' => Offer::STATUS_REJECTED]);

            // Post rejection message in thread
            if ($offer->enquiry->thread) {
                $message = "❌ Offer rejected - Version {$offer->version}";
                if ($reason) {
                    $message .= "\nReason: {$reason}";
                }

                ThreadMessage::create([
                    'thread_id' => $offer->enquiry->thread->id,
                    'sender_id' => $customerId,
                    'sender_type' => ThreadMessage::SENDER_CUSTOMER,
                    'message_type' => ThreadMessage::TYPE_SYSTEM,
                    'message' => $message,
                    'offer_id' => $offer->id,
                    'is_read_customer' => true,
                    'is_read_vendor' => false,
                ]);

                $offer->enquiry->thread->update(['last_message_at' => now()]);
                $offer->enquiry->thread->incrementUnread('vendor');
            }

            DB::commit();

            Log::info('Offer rejected', [
                'offer_id' => $offer->id,
                'customer_id' => $customerId,
                'reason' => $reason,
            ]);

            return $offer->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject offer', [
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update draft offer
     */
    public function updateDraftOffer(int $offerId, array $data): Offer
    {
        $offer = Offer::findOrFail($offerId);
        
        if (!$offer->isDraft()) {
            throw new \Exception('Only draft offers can be updated');
        }

        $updateData = [];
        
        if (isset($data['price'])) $updateData['price'] = $data['price'];
        if (isset($data['price_type'])) $updateData['price_type'] = $data['price_type'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        
        if (isset($data['valid_days'])) {
            $updateData['valid_until'] = now()->addDays($data['valid_days']);
        } elseif (isset($data['valid_until'])) {
            $updateData['valid_until'] = $data['valid_until'];
        }

        $offer->update($updateData);

        // Rebuild price snapshot if price changed
        if (isset($data['price']) || isset($data['price_type'])) {
            $offer->update([
                'price_snapshot' => $this->buildPriceSnapshot($offer->enquiry, array_merge($offer->toArray(), $data))
            ]);
        }

        Log::info('Draft offer updated', [
            'offer_id' => $offerId,
            'updated_fields' => array_keys($updateData),
        ]);

        return $offer->fresh();
    }

    /**
     * Get all versions for an enquiry
     */
    public function getOfferVersions(int $enquiryId, ?int $vendorId = null)
    {
        $query = Offer::with(['vendor'])
            ->where('enquiry_id', $enquiryId);

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        return $query->orderBy('version', 'desc')->get();
    }

    /**
     * Get next version number for vendor's offers on this enquiry
     */
    protected function getNextVersionNumber(int $enquiryId, int $vendorId): int
    {
        $maxVersion = Offer::where('enquiry_id', $enquiryId)
            ->where('vendor_id', $vendorId)
            ->max('version');

        return ($maxVersion ?? 0) + 1;
    }

    /**
     * Build comprehensive price snapshot
     */
    protected function buildPriceSnapshot(Enquiry $enquiry, array $data): array
    {
        $startDate = Carbon::parse($enquiry->preferred_start_date);
        $endDate = Carbon::parse($enquiry->preferred_end_date);
        $durationDays = $startDate->diffInDays($endDate);

        $priceType = $data['price_type'] ?? Offer::PRICE_TOTAL;
        $price = $data['price'];

        return [
            'offer_details' => [
                'price' => $price,
                'price_type' => $priceType,
                'description' => $data['description'] ?? null,
            ],
            'duration' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $durationDays,
                'weeks' => ceil($durationDays / 7),
                'months' => ceil($durationDays / 30),
                'type' => $enquiry->duration_type,
            ],
            'price_breakdown' => $this->calculateBreakdown($price, $priceType, $durationDays),
            'hoarding_snapshot' => $enquiry->snapshot['hoarding'] ?? [],
            'vendor_snapshot' => $enquiry->snapshot['vendor'] ?? [],
            'snapshot_metadata' => [
                'captured_at' => now()->toDateTimeString(),
                'enquiry_id' => $enquiry->id,
            ],
        ];
    }

    /**
     * Create frozen immutable snapshot on acceptance
     */
    protected function createFrozenSnapshot(Offer $offer): array
    {
        $snapshot = $offer->price_snapshot;
        
        // Add acceptance metadata
        $snapshot['acceptance'] = [
            'accepted_at' => now()->toDateTimeString(),
            'frozen' => true,
            'offer_id' => $offer->id,
            'offer_version' => $offer->version,
            'status' => 'immutable',
        ];
        
        return $snapshot;
    }

    /**
     * Calculate price breakdown based on type
     */
    protected function calculateBreakdown(float $price, string $priceType, int $durationDays): array
    {
        $breakdown = [
            'base_price' => $price,
            'price_type' => $priceType,
            'duration_days' => $durationDays,
        ];

        switch ($priceType) {
            case Offer::PRICE_DAILY:
                $breakdown['total'] = $price * $durationDays;
                $breakdown['per_day'] = $price;
                $breakdown['calculation'] = "{$price} × {$durationDays} days";
                break;
                
            case Offer::PRICE_WEEKLY:
                $weeks = ceil($durationDays / 7);
                $breakdown['weeks'] = $weeks;
                $breakdown['total'] = $price * $weeks;
                $breakdown['per_week'] = $price;
                $breakdown['calculation'] = "{$price} × {$weeks} weeks";
                break;
                
            case Offer::PRICE_MONTHLY:
                $months = ceil($durationDays / 30);
                $breakdown['months'] = $months;
                $breakdown['total'] = $price * $months;
                $breakdown['per_month'] = $price;
                $breakdown['calculation'] = "{$price} × {$months} months";
                break;
                
            case Offer::PRICE_TOTAL:
            default:
                $breakdown['total'] = $price;
                $breakdown['calculation'] = "Fixed total: {$price}";
                break;
        }

        return $breakdown;
    }

    /**
     * Delete draft offer
     */
    public function deleteDraftOffer(int $offerId, int $vendorId): bool
    {
        $offer = Offer::where('id', $offerId)
            ->where('vendor_id', $vendorId)
            ->firstOrFail();

        if (!$offer->isDraft()) {
            throw new \Exception('Only draft offers can be deleted');
        }

        $offer->delete();

        Log::info('Draft offer deleted', [
            'offer_id' => $offerId,
            'vendor_id' => $vendorId,
        ]);

        return true;
    }
}
