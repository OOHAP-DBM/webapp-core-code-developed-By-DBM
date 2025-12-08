<?php

namespace Modules\DOOH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DOOHPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dooh_packages';

    protected $fillable = [
        'dooh_screen_id',
        'package_name',
        'description',
        'slots_per_day',
        'slots_per_month',
        'loop_interval_minutes',
        'time_slots',
        'price_per_month',
        'price_per_day',
        'min_booking_months',
        'max_booking_months',
        'discount_percent',
        'package_type',
        'is_active',
    ];

    protected $casts = [
        'price_per_month' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'time_slots' => 'array',
        'is_active' => 'boolean',
    ];

    // Package type constants
    const TYPE_STANDARD = 'standard';
    const TYPE_PREMIUM = 'premium';
    const TYPE_CUSTOM = 'custom';

    /**
     * Get the screen this package belongs to
     */
    public function screen(): BelongsTo
    {
        return $this->belongsTo(DOOHScreen::class, 'dooh_screen_id');
    }

    /**
     * Get bookings using this package
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(DOOHBooking::class, 'dooh_package_id');
    }

    /**
     * Scope: Active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By screen
     */
    public function scopeByScreen($query, int $screenId)
    {
        return $query->where('dooh_screen_id', $screenId);
    }

    /**
     * Scope: By package type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('package_type', $type);
    }

    /**
     * Calculate total slots per month
     */
    public function calculateTotalSlotsPerMonth(): int
    {
        // Assuming 30 days per month
        return $this->slots_per_day * 30;
    }

    /**
     * Calculate discounted price for given months
     */
    public function calculateDiscountedPrice(int $months): float
    {
        $basePrice = $this->price_per_month * $months;
        
        if ($this->discount_percent > 0) {
            $discount = ($basePrice * $this->discount_percent) / 100;
            return $basePrice - $discount;
        }
        
        return $basePrice;
    }

    /**
     * Get package display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->package_name . ' (' . $this->slots_per_day . ' slots/day)';
    }

    /**
     * Get package type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->package_type) {
            self::TYPE_STANDARD => 'Standard',
            self::TYPE_PREMIUM => 'Premium',
            self::TYPE_CUSTOM => 'Custom',
            default => 'Unknown',
        };
    }

    /**
     * Check if package meets minimum slots requirement
     */
    public function meetsMinimumRequirement(): bool
    {
        $screen = $this->screen;
        return $this->slots_per_day >= ($screen->min_slots_per_day ?? 1);
    }
}
