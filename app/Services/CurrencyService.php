<?php

namespace App\Services;

use App\Models\CurrencyConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * PROMPT 109: Currency Service
 * 
 * Handles currency formatting, conversion, and configuration
 */
class CurrencyService
{
    /**
     * Get default currency
     */
    public function getDefaultCurrency(): CurrencyConfig
    {
        $currency = CurrencyConfig::getDefault();
        
        if (!$currency) {
            // Fallback to INR if no default is set
            $currency = CurrencyConfig::where('code', 'INR')->first();
            
            if (!$currency) {
                throw new \Exception('No default currency configured. Please configure a currency in admin settings.');
            }
        }
        
        return $currency;
    }

    /**
     * Get currency by code
     */
    public function getCurrency(string $code): ?CurrencyConfig
    {
        return CurrencyConfig::where('code', $code)->where('is_active', true)->first();
    }

    /**
     * Get all active currencies
     */
    public function getActiveCurrencies()
    {
        return CurrencyConfig::getActiveCurrencies();
    }

    /**
     * Format amount with default currency
     */
    public function format(float $amount, bool $includeSymbol = true): string
    {
        $currency = $this->getDefaultCurrency();
        return $currency->format($amount, $includeSymbol);
    }

    /**
     * Format amount with specific currency
     */
    public function formatWith(float $amount, string $currencyCode, bool $includeSymbol = true): string
    {
        $currency = $this->getCurrency($currencyCode);
        
        if (!$currency) {
            Log::warning("Currency code {$currencyCode} not found, using default");
            $currency = $this->getDefaultCurrency();
        }
        
        return $currency->format($amount, $includeSymbol);
    }

    /**
     * Convert amount between currencies
     */
    public function convert(float $amount, string $fromCode, string $toCode): float
    {
        if ($fromCode === $toCode) {
            return $amount;
        }

        $fromCurrency = $this->getCurrency($fromCode);
        $toCurrency = $this->getCurrency($toCode);

        if (!$fromCurrency || !$toCurrency) {
            throw new \Exception("Invalid currency codes: {$fromCode} or {$toCode}");
        }

        return $fromCurrency->convert($amount, $toCurrency);
    }

    /**
     * Get currency symbol
     */
    public function getSymbol(?string $currencyCode = null): string
    {
        if ($currencyCode) {
            $currency = $this->getCurrency($currencyCode);
            return $currency ? $currency->getSymbol() : '₹';
        }

        return $this->getDefaultCurrency()->getSymbol();
    }

    /**
     * Get currency details for API responses
     */
    public function getCurrencyDetails(?string $currencyCode = null): array
    {
        $currency = $currencyCode 
            ? $this->getCurrency($currencyCode) 
            : $this->getDefaultCurrency();

        return [
            'code' => $currency->code,
            'name' => $currency->name,
            'symbol' => $currency->symbol,
            'symbol_position' => $currency->symbol_position,
            'decimal_places' => $currency->decimal_places,
            'format_example' => $currency->getFormatExample(),
        ];
    }

    /**
     * Parse formatted currency string to float
     */
    public function parse(string $formatted, ?string $currencyCode = null): float
    {
        $currency = $currencyCode 
            ? $this->getCurrency($currencyCode) 
            : $this->getDefaultCurrency();

        // Remove currency symbol
        $cleaned = str_replace($currency->symbol, '', $formatted);
        
        // Remove thousand separators
        $cleaned = str_replace($currency->thousand_separator, '', $cleaned);
        
        // Replace decimal separator with period
        if ($currency->decimal_separator !== '.') {
            $cleaned = str_replace($currency->decimal_separator, '.', $cleaned);
        }
        
        return (float) trim($cleaned);
    }

    /**
     * Set default currency
     */
    public function setDefault(string $currencyCode): bool
    {
        $currency = $this->getCurrency($currencyCode);
        
        if (!$currency) {
            return false;
        }

        $currency->is_default = true;
        $currency->save(); // Model boot will handle unsetting other defaults

        return true;
    }

    /**
     * Update exchange rates (typically from external API)
     */
    public function updateExchangeRates(array $rates): void
    {
        foreach ($rates as $code => $rate) {
            $currency = $this->getCurrency($code);
            if ($currency) {
                $currency->update(['exchange_rate' => $rate]);
            }
        }

        Cache::flush(); // Clear all currency caches
    }

    /**
     * Create or update currency configuration
     */
    public function createOrUpdate(array $data): CurrencyConfig
    {
        if (isset($data['code'])) {
            $currency = CurrencyConfig::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        } else {
            $currency = CurrencyConfig::create($data);
        }

        return $currency;
    }

    /**
     * Get formatted breakdown for display
     */
    public function getFormattedBreakdown(array $amounts, ?string $currencyCode = null): array
    {
        $currency = $currencyCode 
            ? $this->getCurrency($currencyCode) 
            : $this->getDefaultCurrency();

        $formatted = [];
        foreach ($amounts as $key => $value) {
            $formatted[$key] = [
                'raw' => $value,
                'formatted' => $currency->format($value),
                'formatted_no_symbol' => $currency->format($value, false),
            ];
        }

        return $formatted;
    }
}
