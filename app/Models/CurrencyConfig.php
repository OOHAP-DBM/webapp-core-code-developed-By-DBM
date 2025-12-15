<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * PROMPT 109: Global Currency Configuration
 * 
 * Admin-configurable currency settings
 */
class CurrencyConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'symbol_position',
        'decimal_separator',
        'thousand_separator',
        'decimal_places',
        'exchange_rate',
        'is_default',
        'is_active',
        'country_code',
        'format_pattern',
        'metadata',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Symbol positions
    const POSITION_BEFORE = 'before';
    const POSITION_AFTER = 'after';

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($currency) {
            // Ensure only one default currency
            if ($currency->is_default) {
                static::where('id', '!=', $currency->id)->update(['is_default' => false]);
            }
        });

        static::saved(function () {
            Cache::forget('currency_config_default');
            Cache::forget('currency_config_active');
        });

        static::deleted(function () {
            Cache::forget('currency_config_default');
            Cache::forget('currency_config_active');
        });
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

    /**
     * Get default currency with caching
     */
    public static function getDefault(): ?self
    {
        return Cache::remember('currency_config_default', 3600, function () {
            return static::where('is_default', true)->where('is_active', true)->first();
        });
    }

    /**
     * Get all active currencies with caching
     */
    public static function getActiveCurrencies()
    {
        return Cache::remember('currency_config_active', 3600, function () {
            return static::where('is_active', true)->get();
        });
    }

    /**
     * Format amount with currency
     *
     * @param float $amount
     * @param bool $includeSymbol
     * @return string
     */
    public function format(float $amount, bool $includeSymbol = true): string
    {
        // Format number with separators
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );

        if (!$includeSymbol) {
            return $formatted;
        }

        // Apply symbol position
        if ($this->symbol_position === self::POSITION_BEFORE) {
            return $this->symbol . ' ' . $formatted;
        }

        return $formatted . ' ' . $this->symbol;
    }

    /**
     * Convert amount from one currency to another
     *
     * @param float $amount
     * @param CurrencyConfig $toCurrency
     * @return float
     */
    public function convert(float $amount, CurrencyConfig $toCurrency): float
    {
        // Convert to base currency (exchange_rate = 1.0) then to target
        $baseAmount = $amount / $this->exchange_rate;
        return $baseAmount * $toCurrency->exchange_rate;
    }

    /**
     * Get formatted pattern
     */
    public function getFormatExample(): string
    {
        return $this->format(1234.56);
    }

    /**
     * Check if currency is INR (Indian Rupee)
     */
    public function isINR(): bool
    {
        return $this->code === 'INR';
    }

    /**
     * Get currency symbol only
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * Get display name with code
     */
    public function getDisplayName(): string
    {
        return "{$this->name} ({$this->code})";
    }
}
