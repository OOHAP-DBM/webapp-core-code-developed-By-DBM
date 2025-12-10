<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TaxCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxable_type',
        'taxable_id',
        'tax_rule_id',
        'tax_code',
        'tax_name',
        'tax_type',
        'base_amount',
        'tax_rate',
        'tax_amount',
        'is_reverse_charge',
        'paid_by',
        'is_tds',
        'tds_section',
        'tds_deducted',
        'calculation_snapshot',
        'calculated_by',
        'calculated_at',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tds_deducted' => 'decimal:2',
        'is_reverse_charge' => 'boolean',
        'is_tds' => 'boolean',
        'calculation_snapshot' => 'array',
        'calculated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Tax is immutable - disable updates
     */
    protected $updatable = [];

    /**
     * Relationships
     */
    public function taxable(): MorphTo
    {
        return $this->morphTo();
    }

    public function taxRule(): BelongsTo
    {
        return $this->belongsTo(TaxRule::class)->withTrashed();
    }

    /**
     * Scopes
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeForDate($query, $startDate, $endDate = null)
    {
        $query->whereDate('calculated_at', '>=', $startDate);
        
        if ($endDate) {
            $query->whereDate('calculated_at', '<=', $endDate);
        }

        return $query;
    }

    public function scopeForTaxable($query, $taxableType, $taxableId)
    {
        return $query->where('taxable_type', $taxableType)
                    ->where('taxable_id', $taxableId);
    }

    /**
     * Get formatted amounts
     */
    public function getFormattedBaseAmountAttribute(): string
    {
        return '₹' . number_format($this->base_amount, 2);
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return '₹' . number_format($this->tax_amount, 2);
    }

    public function getFormattedTaxRateAttribute(): string
    {
        return $this->tax_rate . '%';
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->tax_type) {
            'gst' => 'GST',
            'tds' => 'TDS',
            'vat' => 'VAT',
            'service_tax' => 'Service Tax',
            'reverse_charge' => 'Reverse Charge',
            default => ucfirst($this->tax_type),
        };
    }

    /**
     * Get paid by label
     */
    public function getPaidByLabelAttribute(): string
    {
        if (!$this->is_reverse_charge) {
            return 'N/A';
        }

        return match($this->paid_by) {
            'customer' => 'Customer',
            'vendor' => 'Vendor',
            'platform' => 'Platform',
            default => ucfirst($this->paid_by ?? 'Unknown'),
        };
    }

    /**
     * Get TDS display
     */
    public function getTdsDisplayAttribute(): string
    {
        if (!$this->is_tds) {
            return 'No';
        }

        $display = 'Yes';
        if ($this->tds_section) {
            $display .= " (Section {$this->tds_section})";
        }
        if ($this->tds_deducted) {
            $display .= " - ₹" . number_format($this->tds_deducted, 2);
        }

        return $display;
    }

    /**
     * Get reverse charge display
     */
    public function getReverseChargeDisplayAttribute(): string
    {
        if (!$this->is_reverse_charge) {
            return 'No';
        }

        return "Yes (Paid by: {$this->paid_by_label})";
    }

    /**
     * Override update to prevent modifications
     */
    public function update(array $attributes = [], array $options = [])
    {
        // Tax calculations are immutable
        \Log::warning('Attempted to update immutable TaxCalculation record', [
            'id' => $this->id,
            'attributes' => $attributes,
        ]);

        return false;
    }

    /**
     * Override delete to prevent deletions
     */
    public function delete()
    {
        // Tax calculations should not be deleted (audit trail)
        \Log::warning('Attempted to delete TaxCalculation record', [
            'id' => $this->id,
        ]);

        return false;
    }

    /**
     * Get snapshot details
     */
    public function getSnapshotDetail(string $key, $default = null)
    {
        return data_get($this->calculation_snapshot, $key, $default);
    }

    /**
     * Check if tax was calculated recently
     */
    public function isRecent(int $minutes = 5): bool
    {
        return $this->calculated_at->diffInMinutes(now()) <= $minutes;
    }

    /**
     * Get summary
     */
    public function getSummaryAttribute(): string
    {
        $parts = [
            $this->type_label,
            "{$this->tax_rate}%",
            "on {$this->formatted_base_amount}",
            "= {$this->formatted_tax_amount}",
        ];

        if ($this->is_tds) {
            $parts[] = "(TDS {$this->tds_section})";
        }

        if ($this->is_reverse_charge) {
            $parts[] = "[Reverse Charge]";
        }

        return implode(' ', $parts);
    }
}
