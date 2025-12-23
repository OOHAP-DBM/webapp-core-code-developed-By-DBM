<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PROMPT 105: Offer Auto-Expiry Logic
 * 
 * Service for managing offer expiry automation
 * Handles auto-expiry after X days configured by admin or vendor
 */
class OfferExpiryService
{
    /**
     * Default expiry days if no setting exists
     */
    const DEFAULT_EXPIRY_DAYS = 7;

    /**
     * Setting key for system default expiry days
     */
    const SETTING_DEFAULT_EXPIRY_DAYS = 'offer_default_expiry_days';

    /**
     * Get default expiry days from system settings
     * 
     * @return int
     */
    public function getDefaultExpiryDays(): int
    {
        $setting = Setting::where('key', self::SETTING_DEFAULT_EXPIRY_DAYS)
            ->whereNull('tenant_id')
            ->first();

        if ($setting) {
            return (int) $setting->value;
        }

        return self::DEFAULT_EXPIRY_DAYS;
    }

    /**
     * Calculate expiry timestamp for an offer
     * 
     * @param Offer $offer
     * @return Carbon|null
     */
    public function calculateExpiryTimestamp(Offer $offer): ?Carbon
    {
        // If offer is not sent, no expiry
        if ($offer->status !== Offer::STATUS_SENT) {
            return null;
        }

        // Use offer-specific expiry_days if set, otherwise system default
        $expiryDays = $offer->expiry_days ?? $this->getDefaultExpiryDays();

        // If 0 or negative, no expiry
        if ($expiryDays <= 0) {
            return null;
        }

        // Calculate from sent_at timestamp
        $sentAt = $offer->sent_at ?? $offer->created_at;
        
        return Carbon::parse($sentAt)->addDays($expiryDays);
    }

    /**
     * Set expiry timestamp when offer is sent
     * 
     * @param Offer $offer
     * @param int|null $expiryDays Override expiry days (null = use default)
     * @return Offer
     */
    public function setOfferExpiry(Offer $offer, ?int $expiryDays = null): Offer
    {
        // Set sent_at if not already set
        if (!$offer->sent_at) {
            $offer->sent_at = now();
        }

        // Set expiry_days
        if ($expiryDays !== null) {
            $offer->expiry_days = $expiryDays;
        } elseif ($offer->expiry_days === null) {
            $offer->expiry_days = $this->getDefaultExpiryDays();
        }

        // Calculate and set expires_at
        $offer->expires_at = $this->calculateExpiryTimestamp($offer);

        $offer->save();

        return $offer;
    }

    /**
     * Check if an offer is expired
     * 
     * @param Offer $offer
     * @return bool
     */
    public function isOfferExpired(Offer $offer): bool
    {
        // If already marked as expired
        if ($offer->status === Offer::STATUS_EXPIRED) {
            return true;
        }

        // Only sent offers can expire
        if ($offer->status !== Offer::STATUS_SENT) {
            return false;
        }

        // Check expires_at timestamp
        if ($offer->expires_at && $offer->expires_at->isPast()) {
            return true;
        }

        // Backward compatibility: check valid_until
        if ($offer->valid_until && $offer->valid_until->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Mark a specific offer as expired
     * 
     * @param Offer $offer
     * @return bool
     */
    public function markOfferExpired(Offer $offer): bool
    {
        if (!$this->isOfferExpired($offer)) {
            return false;
        }

        $offer->status = Offer::STATUS_EXPIRED;
        $offer->expired_at = now();
        $offer->save();

        Log::info("Offer #{$offer->id} marked as expired", [
            'offer_id' => $offer->id,
            'enquiry_id' => $offer->enquiry_id,
            'vendor_id' => $offer->vendor_id,
            'expires_at' => $offer->expires_at?->toDateTimeString(),
        ]);

        return true;
    }

    /**
     * Run auto-expiry check on all sent offers
     * Returns count of expired offers
     * 
     * @return int
     */
    public function expireAllDueOffers(): int
    {
        $expiredCount = 0;

        // Get all sent offers that have passed expiry
        $offers = Offer::where('status', Offer::STATUS_SENT)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Check expires_at
                    $q->whereNotNull('expires_at')
                      ->where('expires_at', '<', now());
                })->orWhere(function ($q) {
                    // Backward compatibility: check valid_until
                    $q->whereNotNull('valid_until')
                      ->where('valid_until', '<', now());
                });
            })
            ->get();

        foreach ($offers as $offer) {
            if ($this->markOfferExpired($offer)) {
                $expiredCount++;
            }
        }

        if ($expiredCount > 0) {
            Log::info("Auto-expired {$expiredCount} offers");
        }

        return $expiredCount;
    }

    /**
     * Get offers expiring soon (within X days)
     * 
     * @param int $days Number of days to look ahead
     * @return Collection
     */
    public function getOffersExpiringSoon(int $days = 3): Collection
    {
        $threshold = now()->addDays($days);

        return Offer::where('status', Offer::STATUS_SENT)
            ->where(function ($query) use ($threshold) {
                $query->where(function ($q) use ($threshold) {
                    $q->whereNotNull('expires_at')
                      ->where('expires_at', '>', now())
                      ->where('expires_at', '<=', $threshold);
                })->orWhere(function ($q) use ($threshold) {
                    $q->whereNotNull('valid_until')
                      ->where('valid_until', '>', now())
                      ->where('valid_until', '<=', $threshold);
                });
            })
            ->with(['enquiry.customer', 'vendor'])
            ->get();
    }

    /**
     * Get offers expiring today
     * 
     * @return Collection
     */
    public function getOffersExpiringToday(): Collection
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        return Offer::where('status', Offer::STATUS_SENT)
            ->where(function ($query) use ($todayStart, $todayEnd) {
                $query->where(function ($q) use ($todayStart, $todayEnd) {
                    $q->whereNotNull('expires_at')
                      ->whereBetween('expires_at', [$todayStart, $todayEnd]);
                })->orWhere(function ($q) use ($todayStart, $todayEnd) {
                    $q->whereNotNull('valid_until')
                      ->whereBetween('valid_until', [$todayStart, $todayEnd]);
                });
            })
            ->with(['enquiry.customer', 'vendor'])
            ->get();
    }

    /**
     * Get statistics about offer expiry
     * 
     * @return array
     */
    public function getExpiryStatistics(): array
    {
        $sentOffers = Offer::where('status', Offer::STATUS_SENT)->count();
        $expiredOffers = Offer::where('status', Offer::STATUS_EXPIRED)->count();
        
        $expiringToday = $this->getOffersExpiringToday()->count();
        $expiringSoon = $this->getOffersExpiringSoon(7)->count();

        $acceptedBeforeExpiry = Offer::where('status', Offer::STATUS_ACCEPTED)
            ->whereNotNull('expires_at')
            ->count();

        return [
            'sent_offers' => $sentOffers,
            'expired_offers' => $expiredOffers,
            'expiring_today' => $expiringToday,
            'expiring_within_7_days' => $expiringSoon,
            'accepted_before_expiry' => $acceptedBeforeExpiry,
            'default_expiry_days' => $this->getDefaultExpiryDays(),
        ];
    }

    /**
     * Extend offer expiry by X days
     * 
     * @param Offer $offer
     * @param int $additionalDays
     * @return Offer
     * @throws \Exception
     */
    public function extendOfferExpiry(Offer $offer, int $additionalDays): Offer
    {
        if ($offer->status !== Offer::STATUS_SENT) {
            throw new \Exception('Only sent offers can be extended');
        }

        if ($additionalDays <= 0) {
            throw new \Exception('Additional days must be greater than 0');
        }

        // Add days to current expires_at
        if ($offer->expires_at) {
            $offer->expires_at = $offer->expires_at->addDays($additionalDays);
        } else {
            // If no expires_at, calculate from now
            $offer->expires_at = now()->addDays($additionalDays);
        }

        // Update expiry_days if needed
        if ($offer->sent_at && $offer->expires_at) {
            $offer->expiry_days = $offer->sent_at->diffInDays($offer->expires_at);
        }

        $offer->save();

        Log::info("Offer #{$offer->id} expiry extended by {$additionalDays} days", [
            'offer_id' => $offer->id,
            'new_expires_at' => $offer->expires_at->toDateTimeString(),
        ]);

        return $offer;
    }

    /**
     * Reset expiry for an offer (set new expiry from now)
     * 
     * @param Offer $offer
     * @param int $expiryDays
     * @return Offer
     * @throws \Exception
     */
    public function resetOfferExpiry(Offer $offer, int $expiryDays): Offer
    {
        if ($offer->status !== Offer::STATUS_SENT) {
            throw new \Exception('Only sent offers can have expiry reset');
        }

        if ($expiryDays <= 0) {
            throw new \Exception('Expiry days must be greater than 0');
        }

        $offer->sent_at = now();
        $offer->expiry_days = $expiryDays;
        $offer->expires_at = now()->addDays($expiryDays);
        $offer->save();

        Log::info("Offer #{$offer->id} expiry reset to {$expiryDays} days from now", [
            'offer_id' => $offer->id,
            'new_expires_at' => $offer->expires_at->toDateTimeString(),
        ]);

        return $offer;
    }

    /**
     * Validate if offer can be accepted (not expired)
     * 
     * @param Offer $offer
     * @return array [bool $canAccept, string|null $reason]
     */
    public function validateOfferAcceptance(Offer $offer): array
    {
        // Check if offer is sent
        if ($offer->status !== Offer::STATUS_SENT) {
            return [false, "Offer is not in 'sent' status"];
        }

        // Check if expired
        if ($this->isOfferExpired($offer)) {
            return [false, 'Offer has expired and cannot be accepted'];
        }

        return [true, null];
    }

    /**
     * Get days remaining until expiry
     * 
     * @param Offer $offer
     * @return int|null Null if no expiry set or already expired
     */
    public function getDaysRemaining(Offer $offer): ?int
    {
        if ($offer->status !== Offer::STATUS_SENT) {
            return null;
        }

        $expiryDate = $offer->expires_at ?? $offer->valid_until;

        if (!$expiryDate) {
            return null;
        }

        if ($expiryDate->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($expiryDate, false);
    }
}
