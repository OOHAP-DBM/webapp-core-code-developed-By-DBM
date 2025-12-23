<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Quotation;
use App\Models\Enquiry;
use App\Models\User;


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
        'expiry_days',        // PROMPT 105: Auto-expiry configuration
        'expires_at',         // PROMPT 105: Calculated expiry timestamp
        'sent_at',            // PROMPT 105: When offer was sent
        'expired_at',         // PROMPT 105: When marked as expired
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_snapshot' => 'array',
        'valid_until' => 'datetime',
        'expires_at' => 'datetime',   // PROMPT 105
        'sent_at' => 'datetime',       // PROMPT 105
        'expired_at' => 'datetime',    // PROMPT 105
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
     * Get all quotations for this offer
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
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
     * PROMPT 105: Updated to check both expires_at and valid_until
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_SENT)
            ->where(function ($q) {
                $q->where(function ($subQ) {
                    // Check expires_at (PROMPT 105)
                    $subQ->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })->where(function ($subQ) {
                    // Check valid_until (backward compatibility)
                    $subQ->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now());
                });
            });
    }

    /**
     * Scope for expired offers
     * PROMPT 105: New scope
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope for offers due to expire (sent + past expiry time)
     * PROMPT 105: New scope
     */
    public function scopeDueToExpire($query)
    {
        return $query->where('status', self::STATUS_SENT)
            ->where(function ($q) {
                $q->where(function ($subQ) {
                    $subQ->whereNotNull('expires_at')
                        ->where('expires_at', '<', now());
                })->orWhere(function ($subQ) {
                    $subQ->whereNotNull('valid_until')
                        ->where('valid_until', '<', now());
                });
            });
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->status === self::STATUS_SENT) {
            // Check expires_at (PROMPT 105)
            if ($this->expires_at && $this->expires_at->isPast()) {
                return true;
            }

            // Backward compatibility: check valid_until
            if ($this->valid_until && $this->valid_until->isPast()) {
                return true;
            }
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
     * Get days remaining until expiry
     * PROMPT 105: New method
     * 
     * @return int|null Null if no expiry, 0 if expired
     */
    public function getDaysRemaining(): ?int
    {
        if ($this->status !== self::STATUS_SENT) {
            return null;
        }

        $expiryDate = $this->expires_at ?? $this->valid_until;

        if (!$expiryDate) {
            return null; // No expiry set
        }

        if ($expiryDate->isPast()) {
            return 0; // Already expired
        }

        return (int) now()->diffInDays($expiryDate, false);
    }

    /**
     * Get formatted expiry information
     * PROMPT 105: New method
     * 
     * @return string
     */
    public function getExpiryLabel(): string
    {
        if ($this->status !== self::STATUS_SENT) {
            return 'N/A';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        $daysRemaining = $this->getDaysRemaining();

        if ($daysRemaining === null) {
            return 'No expiry';
        }

        if ($daysRemaining === 0) {
            return 'Expires today';
        }

        if ($daysRemaining === 1) {
            return 'Expires tomorrow';
        }

        if ($daysRemaining < 7) {
            return "Expires in {$daysRemaining} days";
        }

        return 'Expires on ' . $this->expires_at->format('M d, Y')
            ?? $this->valid_until->format('M d, Y');
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
    // public function isExpired(): bool
    // {
    //     if ($this->status === self::STATUS_EXPIRED) {
    //         return true;
    //     }

    //     if ($this->valid_until && $this->valid_until->isPast() && $this->status === self::STATUS_SENT) {
    //         return true;
    //     }

    //     return false;
    // }

    /**
     * Check if offer can be accepted
     */
    // public function canAccept(): bool
    // {
    //     return $this->isSent() && !$this->isExpired();
    // }

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
