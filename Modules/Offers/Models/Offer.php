<?php

namespace Modules\Offers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
     use HasFactory;
 
    protected $fillable = [
        'enquiry_id',
        'vendor_id',
        'description',
        'valid_until',
        'status',
        'version',
        'expiry_days',
        'expires_at',
        'sent_at',
        'expired_at',
        // price / price_type / price_snapshot are set at QUOTATION stage, not here
    ];
 
    protected $casts = [
        'valid_until' => 'datetime',
        'expires_at'  => 'datetime',
        'sent_at'     => 'datetime',
        'expired_at'  => 'datetime',
    ];
 
    /* ── Status constants ── */
    const STATUS_DRAFT    = 'draft';
    const STATUS_SENT     = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED  = 'expired';
 
    /* ════════════════════════════════════════
       RELATIONSHIPS
    ════════════════════════════════════════ */
 
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(\Modules\Enquiries\Models\Enquiry::class);
    }
 
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
 
    /**
     * Line items — one per hoarding in the offer.
     * Dates, pricing, and package details live here, NOT on the parent offer.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OfferItem::class);
    }
 
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
 
    /* ════════════════════════════════════════
       SCOPES
    ════════════════════════════════════════ */
 
    public function scopeDraft($q)      { return $q->where('status', self::STATUS_DRAFT); }
    public function scopeSent($q)       { return $q->where('status', self::STATUS_SENT); }
    public function scopeAccepted($q)   { return $q->where('status', self::STATUS_ACCEPTED); }
    public function scopeExpired($q)    { return $q->where('status', self::STATUS_EXPIRED); }
 
    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_SENT)
            ->where(fn($sub) =>
                $sub->whereNull('expires_at')->orWhere('expires_at', '>', now())
            )
            ->where(fn($sub) =>
                $sub->whereNull('valid_until')->orWhere('valid_until', '>', now())
            );
    }
 
    public function scopeDueToExpire($q)
    {
        return $q->where('status', self::STATUS_SENT)
            ->where(fn($sub) =>
                $sub->where(fn($s) => $s->whereNotNull('expires_at')->where('expires_at', '<', now()))
                    ->orWhere(fn($s) => $s->whereNotNull('valid_until')->where('valid_until', '<', now()))
            );
    }
 
    /* ════════════════════════════════════════
       STATUS HELPERS
    ════════════════════════════════════════ */
 
    public function isDraft(): bool    { return $this->status === self::STATUS_DRAFT; }
    public function isSent(): bool     { return $this->status === self::STATUS_SENT; }
    public function isAccepted(): bool { return $this->status === self::STATUS_ACCEPTED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }
 
    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) return true;
 
        if ($this->status === self::STATUS_SENT) {
            if ($this->expires_at?->isPast())  return true;
            if ($this->valid_until?->isPast()) return true;
        }
 
        return false;
    }
 
    public function canSend(): bool   { return $this->isDraft(); }
    public function canAccept(): bool { return $this->isSent() && !$this->isExpired(); }
 
    /* ════════════════════════════════════════
       COMPUTED HELPERS
    ════════════════════════════════════════ */
 
    /**
     * Total across all line items (sum of each item's total price).
     * Call after eager-loading items.
     */
    public function getTotalAmount(): float
    {
        return (float) $this->items->sum(fn($item) => $item->getTotalPrice());
    }
 
    /**
     * Campaign date range across all items.
     */
    public function getCampaignStartDate(): ?\Carbon\Carbon
    {
        $date = $this->items->min('preferred_start_date');
        return $date ? \Carbon\Carbon::parse($date) : null;
    }
 
    public function getCampaignEndDate(): ?\Carbon\Carbon
    {
        $date = $this->items->max('preferred_end_date');
        return $date ? \Carbon\Carbon::parse($date) : null;
    }
 
    public function getDaysRemaining(): ?int
    {
        if ($this->status !== self::STATUS_SENT) return null;
        $expiryDate = $this->expires_at ?? $this->valid_until;
        if (!$expiryDate) return null;
        if ($expiryDate->isPast()) return 0;
        return (int) now()->diffInDays($expiryDate, false);
    }
}
