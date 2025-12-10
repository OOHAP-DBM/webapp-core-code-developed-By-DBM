<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class TaxRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'tax_type',
        'rate',
        'calculation_method',
        'applies_to',
        'conditions',
        'is_reverse_charge',
        'reverse_charge_conditions',
        'is_tds',
        'tds_threshold',
        'tds_section',
        'country_code',
        'applicable_states',
        'priority',
        'is_active',
        'effective_from',
        'effective_until',
        'description',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'tds_threshold' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'is_reverse_charge' => 'boolean',
        'is_tds' => 'boolean',
        'conditions' => 'array',
        'applicable_states' => 'array',
        'metadata' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Tax types
     */
    const TYPE_GST = 'gst';
    const TYPE_TDS = 'tds';
    const TYPE_VAT = 'vat';
    const TYPE_SERVICE_TAX = 'service_tax';
    const TYPE_REVERSE_CHARGE = 'reverse_charge';
    const TYPE_OTHER = 'other';

    /**
     * Calculation methods
     */
    const METHOD_PERCENTAGE = 'percentage';
    const METHOD_FLAT = 'flat';
    const METHOD_TIERED = 'tiered';

    /**
     * Applies to
     */
    const APPLIES_BOOKING = 'booking';
    const APPLIES_COMMISSION = 'commission';
    const APPLIES_PAYOUT = 'payout';
    const APPLIES_ALL = 'all';

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(TaxCalculation::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDate($query, Carbon $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('effective_until')
              ->orWhere('effective_until', '>=', $date);
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeByAppliesTo($query, string $appliesTo)
    {
        return $query->where(function ($q) use ($appliesTo) {
            $q->where('applies_to', $appliesTo)
              ->orWhere('applies_to', self::APPLIES_ALL);
        });
    }

    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Check if rule is currently effective
     */
    public function isEffective(Carbon $date = null): bool
    {
        $date = $date ?? now();

        if (!$this->is_active) {
            return false;
        }

        if ($this->effective_from && $date->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if rule applies to specific amount
     */
    public function appliesTo(float $amount): bool
    {
        // TDS threshold check
        if ($this->is_tds && $this->tds_threshold) {
            return $amount >= $this->tds_threshold;
        }

        // Check conditions if any
        if ($this->conditions) {
            return $this->evaluateConditions($this->conditions, $amount);
        }

        return true;
    }

    /**
     * Check if reverse charge applies
     */
    public function shouldApplyReverseCharge(array $context = []): bool
    {
        if (!$this->is_reverse_charge) {
            return false;
        }

        if (!$this->reverse_charge_conditions) {
            return true; // Apply by default if no conditions
        }

        // Parse conditions (could be JSON or text rules)
        if (is_string($this->reverse_charge_conditions)) {
            // Simple parsing for now
            return true;
        }

        return false;
    }

    /**
     * Calculate tax amount
     */
    public function calculateTaxAmount(float $baseAmount, array $options = []): float
    {
        switch ($this->calculation_method) {
            case self::METHOD_PERCENTAGE:
                return round($baseAmount * ($this->rate / 100), 2);
            
            case self::METHOD_FLAT:
                return round($this->rate, 2);
            
            case self::METHOD_TIERED:
                return $this->calculateTieredTax($baseAmount, $options);
            
            default:
                return round($baseAmount * ($this->rate / 100), 2);
        }
    }

    /**
     * Calculate tiered tax (for complex scenarios)
     */
    protected function calculateTieredTax(float $baseAmount, array $options): float
    {
        // Tiered calculation logic based on metadata
        $tiers = $this->metadata['tiers'] ?? [];
        $totalTax = 0;

        foreach ($tiers as $tier) {
            $min = $tier['min'] ?? 0;
            $max = $tier['max'] ?? PHP_FLOAT_MAX;
            $rate = $tier['rate'] ?? $this->rate;

            if ($baseAmount > $min) {
                $taxableAmount = min($baseAmount, $max) - $min;
                $totalTax += ($taxableAmount * $rate / 100);
            }
        }

        return round($totalTax, 2);
    }

    /**
     * Evaluate conditions
     */
    protected function evaluateConditions(array $conditions, float $amount): bool
    {
        if (isset($conditions['min_amount']) && $amount < $conditions['min_amount']) {
            return false;
        }

        if (isset($conditions['max_amount']) && $amount > $conditions['max_amount']) {
            return false;
        }

        // Add more condition evaluations as needed

        return true;
    }

    /**
     * Get formatted rate
     */
    public function getFormattedRateAttribute(): string
    {
        return $this->rate . '%';
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->tax_type) {
            self::TYPE_GST => 'GST',
            self::TYPE_TDS => 'TDS',
            self::TYPE_VAT => 'VAT',
            self::TYPE_SERVICE_TAX => 'Service Tax',
            self::TYPE_REVERSE_CHARGE => 'Reverse Charge',
            default => ucfirst($this->tax_type),
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Get all tax types
     */
    public static function getTaxTypes(): array
    {
        return [
            self::TYPE_GST => 'GST',
            self::TYPE_TDS => 'TDS',
            self::TYPE_VAT => 'VAT',
            self::TYPE_SERVICE_TAX => 'Service Tax',
            self::TYPE_REVERSE_CHARGE => 'Reverse Charge',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get all calculation methods
     */
    public static function getCalculationMethods(): array
    {
        return [
            self::METHOD_PERCENTAGE => 'Percentage',
            self::METHOD_FLAT => 'Flat Amount',
            self::METHOD_TIERED => 'Tiered',
        ];
    }

    /**
     * Get all applies to options
     */
    public static function getAppliesToOptions(): array
    {
        return [
            self::APPLIES_BOOKING => 'Booking Amount',
            self::APPLIES_COMMISSION => 'Commission',
            self::APPLIES_PAYOUT => 'Payout',
            self::APPLIES_ALL => 'All',
        ];
    }
}
