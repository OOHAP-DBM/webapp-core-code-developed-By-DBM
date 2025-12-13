<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\Booking;
use App\Services\OfferExpiryService;
use Modules\Threads\Services\ThreadService;
use Modules\Threads\Models\ThreadMessage;
use App\Notifications\QuotationExpiredNotification;
use App\Notifications\QuotationBookingCancelledNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * PROMPT 106: Quotation Deadline + Auto-Cancel Service
 * 
 * Handles quotation expiry consequences:
 * - Auto-cancel related booking flow when quotation expires
 * - Notify customer and vendor of expiry
 * - Update communication thread with expiry status
 */
class QuotationExpiryService
{
    const DEFAULT_QUOTATION_EXPIRY_DAYS = 7;
    const SETTING_QUOTATION_EXPIRY_DAYS = 'quotation_default_expiry_days';
    const SETTING_AUTO_CANCEL_ENABLED = 'quotation_auto_cancel_enabled';
    const SETTING_NOTIFY_ON_EXPIRY = 'quotation_notify_on_expiry';

    protected OfferExpiryService $offerExpiryService;
    protected ThreadService $threadService;

    public function __construct(
        OfferExpiryService $offerExpiryService,
        ThreadService $threadService
    ) {
        $this->offerExpiryService = $offerExpiryService;
        $this->threadService = $threadService;
    }

    /**
     * Get default quotation expiry days from settings
     */
    public function getDefaultExpiryDays(): int
    {
        return (int) \App\Models\Setting::getValue(
            self::SETTING_QUOTATION_EXPIRY_DAYS,
            self::DEFAULT_QUOTATION_EXPIRY_DAYS
        );
    }

    /**
     * Check if quotation has expired based on offer expiry
     */
    public function isQuotationExpired(Quotation $quotation): bool
    {
        // If quotation is already rejected or superseded, it's not "expired" in the auto-cancel sense
        if (in_array($quotation->status, [Quotation::STATUS_REJECTED, Quotation::STATUS_REVISED])) {
            return false;
        }

        // If quotation is already approved, it cannot expire
        if ($quotation->status === Quotation::STATUS_APPROVED) {
            return false;
        }

        // Check if the related offer has expired
        if ($quotation->offer) {
            return $this->offerExpiryService->isOfferExpired($quotation->offer);
        }

        return false;
    }

    /**
     * Mark quotation as expired and trigger consequences
     */
    public function markQuotationExpired(Quotation $quotation): Quotation
    {
        if (!$this->isQuotationExpired($quotation)) {
            Log::warning('Attempted to expire non-expired quotation', ['quotation_id' => $quotation->id]);
            return $quotation;
        }

        return DB::transaction(function () use ($quotation) {
            // Update quotation status
            $quotation->update([
                'status' => Quotation::STATUS_REJECTED, // Mark as rejected due to expiry
                'notes' => ($quotation->notes ?? '') . "\n[AUTO-EXPIRED: " . now()->toDateTimeString() . "]",
            ]);

            Log::info('Quotation marked as expired', [
                'quotation_id' => $quotation->id,
                'offer_id' => $quotation->offer_id,
                'customer_id' => $quotation->customer_id,
                'vendor_id' => $quotation->vendor_id,
            ]);

            return $quotation;
        });
    }

    /**
     * Process expired quotations: cancel bookings, notify parties, update threads
     */
    public function processExpiredQuotations(): int
    {
        $count = 0;

        // Get all sent or draft quotations with expired offers
        $expiredQuotations = Quotation::with(['offer', 'customer', 'vendor', 'offer.enquiry'])
            ->whereIn('status', [Quotation::STATUS_SENT, Quotation::STATUS_DRAFT])
            ->whereHas('offer', function ($query) {
                // Offers that are due to expire (past expires_at but not yet marked as expired)
                $query->where('status', \Modules\Offers\Models\Offer::STATUS_SENT)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', now());
            })
            ->get();

        foreach ($expiredQuotations as $quotation) {
            try {
                // Mark quotation as expired
                $this->markQuotationExpired($quotation);

                // Auto-cancel related booking flow
                $this->autoCancelBookingFlow($quotation);

                // Notify customer and vendor
                $this->notifyExpiry($quotation);

                // Update thread
                $this->updateThreadForExpiry($quotation);

                $count++;

            } catch (\Exception $e) {
                Log::error('Failed to process expired quotation', [
                    'quotation_id' => $quotation->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        if ($count > 0) {
            Log::info('Processed expired quotations', ['count' => $count]);
        }

        return $count;
    }

    /**
     * Auto-cancel booking flow when quotation expires
     */
    protected function autoCancelBookingFlow(Quotation $quotation): void
    {
        // Check if auto-cancel is enabled
        $autoCancelEnabled = (bool) \App\Models\Setting::getValue(
            self::SETTING_AUTO_CANCEL_ENABLED,
            true
        );

        if (!$autoCancelEnabled) {
            Log::info('Auto-cancel disabled, skipping booking cancellation', [
                'quotation_id' => $quotation->id,
            ]);
            return;
        }

        // Find related bookings in pending payment or draft states
        $bookings = Booking::where('quotation_id', $quotation->id)
            ->whereIn('status', ['pending_payment', 'draft', 'payment_hold'])
            ->get();

        foreach ($bookings as $booking) {
            try {
                // Cancel the booking
                $booking->update([
                    'status' => 'cancelled',
                    'payment_status' => 'cancelled',
                    'cancellation_reason' => 'Quotation expired before payment completion',
                    'cancelled_at' => now(),
                    'cancelled_by' => 'system',
                ]);

                Log::info('Booking auto-cancelled due to quotation expiry', [
                    'booking_id' => $booking->id,
                    'quotation_id' => $quotation->id,
                ]);

                // Notify about booking cancellation
                if ($booking->customer) {
                    $booking->customer->notify(new QuotationBookingCancelledNotification($quotation, $booking));
                }

                if ($booking->vendor) {
                    $booking->vendor->notify(new QuotationBookingCancelledNotification($quotation, $booking));
                }

            } catch (\Exception $e) {
                Log::error('Failed to cancel booking', [
                    'booking_id' => $booking->id,
                    'quotation_id' => $quotation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Notify customer and vendor about quotation expiry
     */
    protected function notifyExpiry(Quotation $quotation): void
    {
        $notifyEnabled = (bool) \App\Models\Setting::getValue(
            self::SETTING_NOTIFY_ON_EXPIRY,
            true
        );

        if (!$notifyEnabled) {
            return;
        }

        try {
            // Notify customer
            if ($quotation->customer) {
                $quotation->customer->notify(new QuotationExpiredNotification($quotation));
            }

            // Notify vendor
            if ($quotation->vendor) {
                $quotation->vendor->notify(new QuotationExpiredNotification($quotation));
            }

            Log::info('Expiry notifications sent', [
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'vendor_id' => $quotation->vendor_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send expiry notifications', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update conversation thread with expiry message
     */
    protected function updateThreadForExpiry(Quotation $quotation): void
    {
        try {
            // Get or create thread for the enquiry
            if (!$quotation->offer || !$quotation->offer->enquiry_id) {
                Log::warning('No enquiry found for quotation, skipping thread update', [
                    'quotation_id' => $quotation->id,
                ]);
                return;
            }

            $thread = $this->threadService->getOrCreateThread($quotation->offer->enquiry_id);

            // Create system message in thread
            $message = sprintf(
                "⚠️ Quotation #%d has expired.\n\nThe quotation was based on Offer #%d which expired on %s. Any related booking requests have been automatically cancelled.\n\nPlease contact the vendor for a new quotation if you're still interested.",
                $quotation->id,
                $quotation->offer_id,
                $quotation->offer->expires_at ? $quotation->offer->expires_at->format('M d, Y g:i A') : 'N/A'
            );

            \Modules\Threads\Models\ThreadMessage::create([
                'thread_id' => $thread->id,
                'sender_id' => null, // System message
                'sender_type' => ThreadMessage::SENDER_SYSTEM,
                'message_type' => ThreadMessage::TYPE_SYSTEM,
                'message' => $message,
                'quotation_id' => $quotation->id,
                'is_read_customer' => false,
                'is_read_vendor' => false,
            ]);

            // Update thread timestamp
            $thread->update(['last_message_at' => now()]);

            // Increment unread counts for both parties
            $thread->incrementUnread('customer');
            $thread->incrementUnread('vendor');

            Log::info('Thread updated with expiry message', [
                'quotation_id' => $quotation->id,
                'thread_id' => $thread->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update thread for expiry', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get quotations expiring soon (based on offer expiry)
     */
    public function getQuotationsExpiringSoon(int $days = 3): \Illuminate\Database\Eloquent\Collection
    {
        return Quotation::with(['offer', 'customer', 'vendor'])
            ->whereIn('status', [Quotation::STATUS_SENT, Quotation::STATUS_DRAFT])
            ->whereHas('offer', function ($query) use ($days) {
                $query->where('status', \Modules\Offers\Models\Offer::STATUS_SENT)
                    ->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [now(), now()->addDays($days)]);
            })
            ->get();
    }

    /**
     * Get quotations expiring today
     */
    public function getQuotationsExpiringToday(): \Illuminate\Database\Eloquent\Collection
    {
        return Quotation::with(['offer', 'customer', 'vendor'])
            ->whereIn('status', [Quotation::STATUS_SENT, Quotation::STATUS_DRAFT])
            ->whereHas('offer', function ($query) {
                $query->where('status', \Modules\Offers\Models\Offer::STATUS_SENT)
                    ->whereNotNull('expires_at')
                    ->whereDate('expires_at', today());
            })
            ->get();
    }

    /**
     * Get quotation expiry statistics
     */
    public function getExpiryStatistics(): array
    {
        $totalActive = Quotation::whereIn('status', [Quotation::STATUS_SENT, Quotation::STATUS_DRAFT])
            ->count();

        $totalExpired = Quotation::where('status', Quotation::STATUS_REJECTED)
            ->where('notes', 'LIKE', '%AUTO-EXPIRED%')
            ->count();

        $expiringToday = $this->getQuotationsExpiringToday()->count();

        $expiringSoon = $this->getQuotationsExpiringSoon(
            (int) \App\Models\Setting::getValue('quotation_expiry_warning_days', 2)
        )->count();

        $expiryRate = ($totalActive + $totalExpired) > 0
            ? ($totalExpired / ($totalActive + $totalExpired)) * 100
            : 0;

        return [
            'total_active' => $totalActive,
            'total_expired' => $totalExpired,
            'expiring_today' => $expiringToday,
            'expiring_soon' => $expiringSoon,
            'expiry_rate' => round($expiryRate, 2),
        ];
    }

    /**
     * Send warning notifications for quotations expiring soon
     */
    public function sendExpiryWarnings(): int
    {
        $warningDays = (int) \App\Models\Setting::getValue('quotation_expiry_warning_days', 2);
        $quotations = $this->getQuotationsExpiringSoon($warningDays);

        $count = 0;

        foreach ($quotations as $quotation) {
            try {
                // Send warning to customer
                if ($quotation->customer) {
                    $quotation->customer->notify(
                        new \App\Notifications\QuotationExpiryWarningNotification($quotation)
                    );
                }

                // Send warning to vendor
                if ($quotation->vendor) {
                    $quotation->vendor->notify(
                        new \App\Notifications\QuotationExpiryWarningNotification($quotation)
                    );
                }

                $count++;

                Log::info('Expiry warning sent', [
                    'quotation_id' => $quotation->id,
                    'expires_at' => $quotation->offer->expires_at,
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send expiry warning', [
                    'quotation_id' => $quotation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Get days remaining until quotation expires
     */
    public function getDaysRemaining(Quotation $quotation): ?int
    {
        if (!$quotation->offer || !$quotation->offer->expires_at) {
            return null;
        }

        return $this->offerExpiryService->getDaysRemaining($quotation->offer);
    }

    /**
     * Get expiry label for quotation
     */
    public function getExpiryLabel(Quotation $quotation): string
    {
        if (!$quotation->offer) {
            return 'No expiry set';
        }

        return $quotation->offer->getExpiryLabel();
    }
}
