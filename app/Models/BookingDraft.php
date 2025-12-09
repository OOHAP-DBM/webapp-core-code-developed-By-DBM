<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class BookingDraft extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'hoarding_id',
        'package_id',
        'start_date',
        'end_date',
        'duration_days',
        'duration_type',
        'price_snapshot',
        'base_price',
        'discount_amount',
        'gst_amount',
        'total_amount',
        'applied_offers',
        'coupon_code',
        'step',
        'last_updated_step_at',
        'session_id',
        'expires_at',
        'is_converted',
        'booking_id',
        'converted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_days' => 'integer',
        'price_snapshot' => 'array',
        'base_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'applied_offers' => 'array',
        'last_updated_step_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_converted' => 'boolean',
        'converted_at' => 'datetime',
    ];

    // Step constants
    const STEP_HOARDING_SELECTED = 'hoarding_selected';
    const STEP_PACKAGE_SELECTED = 'package_selected';
    const STEP_DATES_SELECTED = 'dates_selected';
    const STEP_REVIEW = 'review';
    const STEP_PAYMENT_PENDING = 'payment_pending';

    // Duration types
    const DURATION_DAYS = 'days';
    const DURATION_WEEKS = 'weeks';
    const DURATION_MONTHS = 'months';

    /**
     * Relationships
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(\Modules\DOOH\Models\DOOHPackage::class, 'package_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_converted', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('is_converted', false)
            ->where('expires_at', '<=', now());
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByHoarding($query, $hoardingId)
    {
        return $query->where('hoarding_id', $hoardingId);
    }

    /**
     * Methods
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function markConverted(Booking $booking): void
    {
        $this->update([
            'is_converted' => true,
            'booking_id' => $booking->id,
            'converted_at' => now(),
        ]);
    }

    public function updateStep(string $step): void
    {
        $this->update([
            'step' => $step,
            'last_updated_step_at' => now(),
        ]);
    }

    public function refreshExpiry(int $minutes = 30): void
    {
        $this->update([
            'expires_at' => now()->addMinutes($minutes),
        ]);
    }

    public function hasValidDates(): bool
    {
        return $this->start_date && $this->end_date && $this->start_date <= $this->end_date;
    }

    public function calculateDuration(): int
    {
        if (!$this->hasValidDates()) {
            return 0;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($draft) {
            // Set default expiry if not set (30 minutes)
            if (!$draft->expires_at) {
                $draft->expires_at = now()->addMinutes(30);
            }

            // Set session ID if not set
            if (!$draft->session_id) {
                $draft->session_id = session()->getId();
            }
        });
    }
}
