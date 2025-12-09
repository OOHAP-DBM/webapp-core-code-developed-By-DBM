<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CancellationPolicy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_default',
        'applies_to',
        'booking_type',
        'time_windows',
        'customer_fee_type',
        'customer_fee_value',
        'customer_min_fee',
        'customer_max_fee',
        'vendor_penalty_type',
        'vendor_penalty_value',
        'vendor_min_penalty',
        'vendor_max_penalty',
        'auto_refund_enabled',
        'refund_processing_days',
        'refund_method',
        'pos_auto_refund_disabled',
        'pos_refund_note',
        'allow_admin_override',
        'override_conditions',
        'min_hours_before_start',
        'max_hours_before_start',
        'min_booking_amount',
        'max_booking_amount',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'time_windows' => 'array',
        'customer_fee_value' => 'decimal:2',
        'customer_min_fee' => 'decimal:2',
        'customer_max_fee' => 'decimal:2',
        'vendor_penalty_value' => 'decimal:2',
        'vendor_min_penalty' => 'decimal:2',
        'vendor_max_penalty' => 'decimal:2',
        'refund_processing_days' => 'integer',
        'auto_refund_enabled' => 'boolean',
        'pos_auto_refund_disabled' => 'boolean',
        'allow_admin_override' => 'boolean',
        'min_hours_before_start' => 'integer',
        'max_hours_before_start' => 'integer',
        'min_booking_amount' => 'decimal:2',
        'max_booking_amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(BookingRefund::class);
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

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForRole($query, string $role)
    {
        return $query->where(function($q) use ($role) {
            $q->where('applies_to', $role)
              ->orWhere('applies_to', 'all');
        });
    }

    public function scopeForBookingType($query, string $type)
    {
        return $query->where(function($q) use ($type) {
            $q->where('booking_type', $type)
              ->orWhereNull('booking_type');
        });
    }

    /**
     * Calculate refund based on hours before start
     */
    public function calculateRefund(float $bookingAmount, int $hoursBeforeStart, string $cancelledByRole = 'customer'): array
    {
        // Find applicable time window
        $timeWindow = $this->findTimeWindow($hoursBeforeStart);

        if (!$timeWindow) {
            // No refund if outside all windows
            return [
                'refundable_amount' => 0,
                'refund_percent' => 0,
                'customer_fee' => $bookingAmount,
                'vendor_penalty' => 0,
                'refund_amount' => 0,
                'time_window' => null,
            ];
        }

        $refundPercent = $timeWindow['refund_percent'] ?? 100;
        $refundableAmount = ($bookingAmount * $refundPercent) / 100;

        // Calculate customer fee
        $customerFee = 0;
        if ($cancelledByRole === 'customer') {
            $customerFee = $this->calculateFee(
                $bookingAmount,
                $this->customer_fee_type,
                $this->customer_fee_value,
                $this->customer_min_fee,
                $this->customer_max_fee,
                $timeWindow['customer_fee_percent'] ?? 0
            );
        }

        // Calculate vendor penalty
        $vendorPenalty = 0;
        if ($cancelledByRole === 'vendor') {
            $vendorPenalty = $this->calculateFee(
                $bookingAmount,
                $this->vendor_penalty_type,
                $this->vendor_penalty_value,
                $this->vendor_min_penalty,
                $this->vendor_max_penalty,
                $timeWindow['vendor_penalty_percent'] ?? 0
            );
        }

        $finalRefund = max(0, $refundableAmount - $customerFee);

        return [
            'refundable_amount' => round($refundableAmount, 2),
            'refund_percent' => $refundPercent,
            'customer_fee' => round($customerFee, 2),
            'vendor_penalty' => round($vendorPenalty, 2),
            'refund_amount' => round($finalRefund, 2),
            'time_window' => $timeWindow,
            'hours_before_start' => $hoursBeforeStart,
        ];
    }

    /**
     * Find applicable time window
     */
    protected function findTimeWindow(int $hoursBeforeStart): ?array
    {
        if (!$this->time_windows || !is_array($this->time_windows)) {
            return null;
        }

        // Sort windows by hours_before descending
        $windows = collect($this->time_windows)->sortByDesc('hours_before');

        foreach ($windows as $window) {
            if ($hoursBeforeStart >= ($window['hours_before'] ?? 0)) {
                return $window;
            }
        }

        return null;
    }

    /**
     * Calculate fee/penalty
     */
    protected function calculateFee(
        float $amount,
        string $type,
        float $value,
        ?float $min,
        ?float $max,
        float $windowPercent = 0
    ): float {
        // Use window-specific percent if provided, otherwise use policy default
        $effectiveValue = $windowPercent > 0 ? $windowPercent : $value;

        if ($type === 'percentage') {
            $fee = ($amount * $effectiveValue) / 100;
        } else {
            $fee = $effectiveValue;
        }

        // Apply min/max constraints
        if ($min !== null && $fee < $min) {
            $fee = $min;
        }
        if ($max !== null && $fee > $max) {
            $fee = $max;
        }

        return $fee;
    }

    /**
     * Check if policy applies to booking
     */
    public function appliesTo(array $bookingData): bool
    {
        // Check booking amount
        $amount = $bookingData['amount'] ?? 0;
        if ($this->min_booking_amount && $amount < $this->min_booking_amount) {
            return false;
        }
        if ($this->max_booking_amount && $amount > $this->max_booking_amount) {
            return false;
        }

        // Check hours before start
        $hoursBefore = $bookingData['hours_before_start'] ?? null;
        if ($hoursBefore !== null) {
            if ($this->min_hours_before_start && $hoursBefore < $this->min_hours_before_start) {
                return false;
            }
            if ($this->max_hours_before_start && $hoursBefore > $this->max_hours_before_start) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if auto-refund is allowed
     */
    public function allowsAutoRefund(string $bookingType = 'ooh'): bool
    {
        if ($bookingType === 'pos' && $this->pos_auto_refund_disabled) {
            return false;
        }

        return $this->auto_refund_enabled;
    }
}
