<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquiry_id',
        'vendor_id',
        'price',
        'price_type',
        'price_snapshot',
        'description',
        'valid_until',
        'status',
        'version',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_snapshot' => 'array',
        'valid_until' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    /**
     * Price type constants
     */
    const PRICE_TOTAL = 'total';
    const PRICE_MONTHLY = 'monthly';
    const PRICE_WEEKLY = 'weekly';
    const PRICE_DAILY = 'daily';

    /**
     * Boot method to handle events
     */
    protected static function boot()
    {
        parent::boot();

        // Post system message when offer is sent
        static::updated(function ($offer) {
            if ($offer->isDirty('status') && $offer->status === self::STATUS_SENT) {
                $thread = Thread::where('enquiry_id', $offer->enquiry_id)->first();
                if ($thread) {
                    ThreadMessage::create([
                        'thread_id' => $thread->id,
                        'sender_id' => $offer->vendor_id,
                        'sender_type' => 'vendor',
                        'message_type' => 'offer',
                        'message' => "Vendor sent an offer: ₹" . number_format($offer->price, 2) . " ({$offer->price_type})",
                        'offer_id' => $offer->id,
                    ]);
                }
            }
            
            // Post message when offer is accepted
            if ($offer->isDirty('status') && $offer->status === self::STATUS_ACCEPTED) {
                $thread = Thread::where('enquiry_id', $offer->enquiry_id)->first();
                if ($thread) {
                    ThreadMessage::create([
                        'thread_id' => $thread->id,
                        'sender_id' => $offer->enquiry->customer_id,
                        'sender_type' => 'customer',
                        'message_type' => 'system',
                        'message' => "Offer #" . $offer->id . " accepted by customer",
                        'offer_id' => $offer->id,
                    ]);
                }
            }
        });
    }

    /**
     * Get the enquiry for this offer
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    /**
     * Get the vendor who created this offer
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Scope for draft offers
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for sent offers
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope for accepted offers
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope for non-expired sent offers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_SENT)
            ->where(function($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>', now());
            });
    }

    /**
     * Check if offer is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if offer is sent
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Check if offer is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if offer is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if offer is expired
     */
    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->valid_until && $this->valid_until->isPast() && $this->status === self::STATUS_SENT) {
            return true;
        }

        return false;
    }

    /**
     * Check if offer can be accepted
     */
    public function canAccept(): bool
    {
        return $this->isSent() && !$this->isExpired();
    }

    /**
     * Check if offer can be sent (is draft)
     */
    public function canSend(): bool
    {
        return $this->isDraft();
    }

    /**
     * Get snapshot value by key
     */
    public function getSnapshotValue(string $key, $default = null)
    {
        return $this->price_snapshot[$key] ?? $default;
    }

    /**
     * Get formatted price with type
     */
    public function getFormattedPrice(): string
    {
        $formatted = '₹' . number_format($this->price, 2);
        
        switch ($this->price_type) {
            case self::PRICE_MONTHLY:
                return $formatted . '/month';
            case self::PRICE_WEEKLY:
                return $formatted . '/week';
            case self::PRICE_DAILY:
                return $formatted . '/day';
            case self::PRICE_TOTAL:
            default:
                return $formatted . ' (Total)';
        }
    }

    /**
     * Get days until expiry
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->valid_until) {
            return null;
        }

        return max(0, now()->diffInDays($this->valid_until, false));
    }
}
