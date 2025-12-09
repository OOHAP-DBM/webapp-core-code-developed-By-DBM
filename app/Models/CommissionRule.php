<?php

namespace App\Models;

use App\Traits\HasSnapshots;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CommissionRule extends Model
{
    use SoftDeletes, HasSnapshots, Auditable;
    
    protected $snapshotType = 'commission_rule';
    protected $snapshotOnCreate = true;
    protected $snapshotOnUpdate = true;
    
    protected $auditModule = 'commission';
    protected $priceFields = ['commission_value'];

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'priority',
        'rule_type',
        'vendor_id',
        'hoarding_id',
        'city',
        'area',
        'hoarding_type',
        'valid_from',
        'valid_to',
        'days_of_week',
        'time_range',
        'is_seasonal',
        'season_name',
        'commission_type',
        'commission_value',
        'tiered_config',
        'enable_distribution',
        'distribution_config',
        'min_booking_amount',
        'max_booking_amount',
        'min_duration_days',
        'max_duration_days',
        'usage_count',
        'last_used_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'commission_value' => 'decimal:2',
        'tiered_config' => 'array',
        'days_of_week' => 'array',
        'time_range' => 'array',
        'is_seasonal' => 'boolean',
        'enable_distribution' => 'boolean',
        'distribution_config' => 'array',
        'min_booking_amount' => 'decimal:2',
        'max_booking_amount' => 'decimal:2',
        'min_duration_days' => 'integer',
        'max_duration_days' => 'integer',
        'usage_count' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Constants
    const RULE_TYPE_VENDOR = 'vendor';
    const RULE_TYPE_HOARDING = 'hoarding';
    const RULE_TYPE_LOCATION = 'location';
    const RULE_TYPE_FLAT = 'flat';
    const RULE_TYPE_TIME_BASED = 'time_based';
    const RULE_TYPE_SEASONAL = 'seasonal';

    const COMMISSION_TYPE_PERCENTAGE = 'percentage';
    const COMMISSION_TYPE_FIXED = 'fixed';
    const COMMISSION_TYPE_TIERED = 'tiered';

    /**
     * Relationships
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where(function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId)
              ->orWhereNull('vendor_id');
        });
    }

    public function scopeForHoarding($query, int $hoardingId)
    {
        return $query->where(function ($q) use ($hoardingId) {
            $q->where('hoarding_id', $hoardingId)
              ->orWhereNull('hoarding_id');
        });
    }

    public function scopeForLocation($query, ?string $city, ?string $area)
    {
        return $query->where(function ($q) use ($city, $area) {
            $q->where(function ($subQ) use ($city, $area) {
                if ($city) {
                    $subQ->where('city', $city)->orWhereNull('city');
                }
                if ($area) {
                    $subQ->where('area', $area)->orWhereNull('area');
                }
            })->orWhere(function ($subQ) {
                $subQ->whereNull('city')->whereNull('area');
            });
        });
    }

    public function scopeValidNow($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                $subQ->whereNull('valid_from')
                     ->orWhere('valid_from', '<=', $now);
            })->where(function ($subQ) use ($now) {
                $subQ->whereNull('valid_to')
                     ->orWhere('valid_to', '>=', $now);
            });
        });
    }

    public function scopeSeasonal($query)
    {
        return $query->where('is_seasonal', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('rule_type', $type);
    }

    /**
     * Check if rule is currently valid
     */
    public function isValidNow(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // Check date range
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_to && $now->gt($this->valid_to)) {
            return false;
        }

        // Check day of week
        if ($this->days_of_week && !in_array($now->dayOfWeek, $this->days_of_week)) {
            return false;
        }

        // Check time range
        if ($this->time_range) {
            $currentTime = $now->format('H:i');
            $start = $this->time_range['start'] ?? null;
            $end = $this->time_range['end'] ?? null;
            
            if ($start && $currentTime < $start) {
                return false;
            }
            if ($end && $currentTime > $end) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if rule applies to a booking
     */
    public function appliesTo(array $bookingData): bool
    {
        if (!$this->isValidNow()) {
            return false;
        }

        // Check vendor
        if ($this->vendor_id && $bookingData['vendor_id'] != $this->vendor_id) {
            return false;
        }

        // Check hoarding
        if ($this->hoarding_id && $bookingData['hoarding_id'] != $this->hoarding_id) {
            return false;
        }

        // Check city
        if ($this->city && ($bookingData['city'] ?? null) != $this->city) {
            return false;
        }

        // Check area
        if ($this->area && ($bookingData['area'] ?? null) != $this->area) {
            return false;
        }

        // Check hoarding type
        if ($this->hoarding_type && ($bookingData['hoarding_type'] ?? null) != $this->hoarding_type) {
            return false;
        }

        // Check booking amount
        $amount = $bookingData['amount'] ?? 0;
        if ($this->min_booking_amount && $amount < $this->min_booking_amount) {
            return false;
        }
        if ($this->max_booking_amount && $amount > $this->max_booking_amount) {
            return false;
        }

        // Check duration
        $duration = $bookingData['duration_days'] ?? 0;
        if ($this->min_duration_days && $duration < $this->min_duration_days) {
            return false;
        }
        if ($this->max_duration_days && $duration > $this->max_duration_days) {
            return false;
        }

        return true;
    }

    /**
     * Calculate commission for a given amount
     */
    public function calculateCommission(float $amount): float
    {
        switch ($this->commission_type) {
            case self::COMMISSION_TYPE_FIXED:
                return $this->commission_value;

            case self::COMMISSION_TYPE_PERCENTAGE:
                return round($amount * ($this->commission_value / 100), 2);

            case self::COMMISSION_TYPE_TIERED:
                if (!$this->tiered_config) {
                    return 0;
                }

                foreach ($this->tiered_config as $tier) {
                    $min = $tier['min'] ?? 0;
                    $max = $tier['max'] ?? PHP_FLOAT_MAX;
                    $rate = $tier['rate'] ?? 0;

                    if ($amount >= $min && $amount <= $max) {
                        return round($amount * ($rate / 100), 2);
                    }
                }
                return 0;

            default:
                return 0;
        }
    }

    /**
     * Increment usage counter
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get human-readable rule summary
     */
    public function getSummaryAttribute(): string
    {
        $parts = [];

        if ($this->vendor_id) {
            $parts[] = "Vendor: {$this->vendor->name}";
        }
        if ($this->hoarding_id) {
            $parts[] = "Hoarding: {$this->hoarding->title}";
        }
        if ($this->city) {
            $parts[] = "City: {$this->city}";
        }
        if ($this->area) {
            $parts[] = "Area: {$this->area}";
        }
        if ($this->is_seasonal) {
            $parts[] = "Season: {$this->season_name}";
        }

        $commission = $this->commission_type === self::COMMISSION_TYPE_PERCENTAGE
            ? "{$this->commission_value}%"
            : "₹{$this->commission_value}";

        $parts[] = "Commission: {$commission}";

        return implode(' | ', $parts);
    }

    /**
     * Get rule type label
     */
    public function getRuleTypeLabel(): string
    {
        return match($this->rule_type) {
            self::RULE_TYPE_VENDOR => 'Per Vendor',
            self::RULE_TYPE_HOARDING => 'Per Hoarding',
            self::RULE_TYPE_LOCATION => 'Per Location',
            self::RULE_TYPE_FLAT => 'Flat Commission',
            self::RULE_TYPE_TIME_BASED => 'Time-Based',
            self::RULE_TYPE_SEASONAL => 'Seasonal Offer',
            default => ucfirst($this->rule_type),
        };
    }

    /**
     * Get commission type label
     */
    public function getCommissionTypeLabel(): string
    {
        return match($this->commission_type) {
            self::COMMISSION_TYPE_PERCENTAGE => 'Percentage',
            self::COMMISSION_TYPE_FIXED => 'Fixed Amount',
            self::COMMISSION_TYPE_TIERED => 'Tiered',
            default => ucfirst($this->commission_type),
        };
    }
}
