<?php

namespace Modules\Offers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Enquiries\Models\EnquiryItem;

class OfferItem extends Model
{
    use HasFactory;

    protected $table = 'offer_items';

    protected $fillable = [
        'offer_id',
        'enquiry_item_id',
        'hoarding_id',
        'hoarding_type',
        'package_id',
        'package_type',
        'package_label',
        'preferred_start_date',
        'preferred_end_date',
        'duration_months',
        'price_per_month',
        'offered_price',
        'discount_percent',
        'services',
        'meta',
    ];

    protected $casts = [
        'preferred_start_date' => 'date',
        'preferred_end_date'   => 'date',
        'services'             => 'array',
        'meta'                 => 'array',
        'price_per_month'      => 'decimal:2',
        'offered_price'        => 'decimal:2',
        'discount_percent'     => 'decimal:2',
    ];

    /* ── Relationships ── */

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function enquiryItem(): BelongsTo
    {
        return $this->belongsTo(EnquiryItem::class);
    }

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /* ── Helpers ── */

    public function isOOH(): bool
    {
        return $this->hoarding_type === 'ooh';
    }

    public function isDOOH(): bool
    {
        return $this->hoarding_type === 'dooh';
    }

    public function getDurationMonths(): int
    {
        if ($this->duration_months) {
            return (int) $this->duration_months;
        }

        if ($this->preferred_start_date && $this->preferred_end_date) {
            $days = $this->preferred_start_date->diffInDays($this->preferred_end_date) + 1;
            return (int) max(1, ceil($days / 30));
        }

        return 1;
    }

    public function getTotalPrice(): float
    {
        if ($this->offered_price !== null) {
            return (float) $this->offered_price;
        }

        return (float) ($this->price_per_month * $this->getDurationMonths());
    }
}