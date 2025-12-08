<?php

namespace Modules\Quotations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    protected $fillable = [
        'offer_id',
        'customer_id',
        'vendor_id',
        'version',
        'items',
        'total_amount',
        'tax',
        'discount',
        'grand_total',
        'approved_snapshot',
        'status',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'approved_snapshot' => 'array',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVISED = 'revised';

    /**
     * Relationships
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeRevised($query)
    {
        return $query->where('status', self::STATUS_REVISED);
    }

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isRevised(): bool
    {
        return $this->status === self::STATUS_REVISED;
    }

    /**
     * Action checks
     */
    public function canSend(): bool
    {
        return $this->isDraft();
    }

    public function canApprove(): bool
    {
        return $this->isSent();
    }

    public function canRevise(): bool
    {
        return $this->isSent() || $this->isRejected();
    }

    /**
     * Helpers
     */
    public function getSnapshotValue(string $key, $default = null)
    {
        return data_get($this->approved_snapshot, $key, $default);
    }

    public function getItemsValue(string $key, $default = null)
    {
        return data_get($this->items, $key, $default);
    }

    public function calculateGrandTotal(): float
    {
        return (float) ($this->total_amount + $this->tax - $this->discount);
    }

    public function getFormattedGrandTotal(): string
    {
        return '₹' . number_format($this->grand_total, 2);
    }

    public function getFormattedTotalAmount(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    public function getFormattedTax(): string
    {
        return '₹' . number_format($this->tax, 2);
    }

    public function getFormattedDiscount(): string
    {
        return '₹' . number_format($this->discount, 2);
    }

    public function getItemsCount(): int
    {
        return is_array($this->items) ? count($this->items) : 0;
    }

    public function getTotalQuantity(): float
    {
        if (!is_array($this->items)) {
            return 0;
        }

        return array_reduce($this->items, function ($carry, $item) {
            return $carry + (float) ($item['quantity'] ?? 0);
        }, 0);
    }
}

