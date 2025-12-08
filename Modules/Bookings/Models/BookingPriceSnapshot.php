<?php

namespace Modules\Bookings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPriceSnapshot extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'booking_price_snapshots';

    /**
     * Disable updated_at timestamp (only created_at needed)
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'booking_id',
        'quotation_snapshot',
        'services_price',
        'discounts',
        'taxes',
        'total_amount',
        'currency',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quotation_snapshot' => 'array',
        'services_price' => 'decimal:2',
        'discounts' => 'decimal:2',
        'taxes' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get line items from quotation snapshot
     */
    public function getLineItemsAttribute(): array
    {
        return $this->quotation_snapshot['line_items'] ?? [];
    }

    /**
     * Get quotation metadata
     */
    public function getQuotationMetadataAttribute(): array
    {
        return [
            'quotation_id' => $this->quotation_snapshot['quotation_id'] ?? null,
            'quotation_version' => $this->quotation_snapshot['quotation_version'] ?? null,
            'vendor_name' => $this->quotation_snapshot['vendor_name'] ?? null,
            'customer_name' => $this->quotation_snapshot['customer_name'] ?? null,
            'created_at' => $this->quotation_snapshot['created_at'] ?? null,
        ];
    }

    /**
     * Get price breakdown summary
     */
    public function getPriceBreakdownAttribute(): array
    {
        return [
            'services_price' => (float) $this->services_price,
            'discounts' => (float) $this->discounts,
            'taxes' => (float) $this->taxes,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'line_items_count' => count($this->line_items),
        ];
    }

    /**
     * Calculate effective price (services - discounts)
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->services_price - $this->discounts);
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute(): float
    {
        if ($this->services_price <= 0) {
            return 0.0;
        }

        return round(($this->discounts / $this->services_price) * 100, 2);
    }

    /**
     * Get tax percentage
     */
    public function getTaxPercentageAttribute(): float
    {
        $effectivePrice = $this->effective_price;
        
        if ($effectivePrice <= 0) {
            return 0.0;
        }

        return round(($this->taxes / $effectivePrice) * 100, 2);
    }
}

