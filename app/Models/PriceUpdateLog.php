<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceUpdateLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'update_type',
        'batch_id',
        'hoarding_id',
        'old_weekly_price',
        'old_monthly_price',
        'new_weekly_price',
        'new_monthly_price',
        'bulk_criteria',
        'update_method',
        'update_value',
        'reason',
        'notes',
        'affected_hoardings_count',
        'hoarding_snapshot',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_weekly_price' => 'decimal:2',
        'old_monthly_price' => 'decimal:2',
        'new_weekly_price' => 'decimal:2',
        'new_monthly_price' => 'decimal:2',
        'update_value' => 'decimal:2',
        'bulk_criteria' => 'array',
        'hoarding_snapshot' => 'array',
        'affected_hoardings_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Update types
     */
    const TYPE_SINGLE = 'single';
    const TYPE_BULK = 'bulk';

    /**
     * Update methods
     */
    const METHOD_FIXED = 'fixed';
    const METHOD_PERCENTAGE = 'percentage';
    const METHOD_INCREMENT = 'increment';
    const METHOD_DECREMENT = 'decrement';

    /**
     * Get the admin who performed the update.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the hoarding that was updated.
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /**
     * Scope for single updates.
     */
    public function scopeSingleUpdates($query)
    {
        return $query->where('update_type', self::TYPE_SINGLE);
    }

    /**
     * Scope for bulk updates.
     */
    public function scopeBulkUpdates($query)
    {
        return $query->where('update_type', self::TYPE_BULK);
    }

    /**
     * Scope for a specific batch.
     */
    public function scopeBatch($query, string $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Get price change for weekly price.
     */
    public function getWeeklyPriceChangeAttribute(): ?float
    {
        if ($this->old_weekly_price === null || $this->new_weekly_price === null) {
            return null;
        }
        return $this->new_weekly_price - $this->old_weekly_price;
    }

    /**
     * Get price change for monthly price.
     */
    public function getMonthlyPriceChangeAttribute(): ?float
    {
        if ($this->old_monthly_price === null || $this->new_monthly_price === null) {
            return null;
        }
        return $this->new_monthly_price - $this->old_monthly_price;
    }

    /**
     * Get percentage change for weekly price.
     */
    public function getWeeklyPriceChangePercentAttribute(): ?float
    {
        if ($this->old_weekly_price === null || $this->old_weekly_price == 0 || $this->new_weekly_price === null) {
            return null;
        }
        return (($this->new_weekly_price - $this->old_weekly_price) / $this->old_weekly_price) * 100;
    }

    /**
     * Get percentage change for monthly price.
     */
    public function getMonthlyPriceChangePercentAttribute(): ?float
    {
        if ($this->old_monthly_price === null || $this->old_monthly_price == 0 || $this->new_monthly_price === null) {
            return null;
        }
        return (($this->new_monthly_price - $this->old_monthly_price) / $this->old_monthly_price) * 100;
    }

    /**
     * Format bulk criteria for display.
     */
    public function getFormattedCriteriaAttribute(): array
    {
        if (!$this->bulk_criteria) {
            return [];
        }

        $formatted = [];
        foreach ($this->bulk_criteria as $key => $value) {
            if ($value !== null && $value !== '') {
                $formatted[ucwords(str_replace('_', ' ', $key))] = $value;
            }
        }
        return $formatted;
    }
}
