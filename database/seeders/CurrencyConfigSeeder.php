<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CurrencyConfig;

/**
 * PROMPT 109: Currency Configuration Seeder
 * 
 * Seeds default currency configurations
 */
class CurrencyConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            // Indian Rupee (Default)
            [
                'code' => 'INR',
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimal_places' => 2,
                'exchange_rate' => 1.000000, // Base currency
                'is_default' => true,
                'is_active' => true,
                'country_code' => 'IN',
                'format_pattern' => '{symbol} {amount}',
                'metadata' => [
                    'countries' => ['India'],
                    'iso_numeric' => '356',
                    'minor_unit' => 2,
                ],
            ],

            // US Dollar
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimal_places' => 2,
                'exchange_rate' => 83.000000, // 1 USD = 83 INR (approximate, should be updated)
                'is_default' => false,
                'is_active' => true,
                'country_code' => 'US',
                'format_pattern' => '{symbol}{amount}',
                'metadata' => [
                    'countries' => ['United States'],
                    'iso_numeric' => '840',
                    'minor_unit' => 2,
                ],
            ],

            // Euro
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'symbol_position' => 'before',
                'decimal_separator' => ',',
                'thousand_separator' => '.',
                'decimal_places' => 2,
                'exchange_rate' => 90.000000, // 1 EUR = 90 INR (approximate)
                'is_default' => false,
                'is_active' => true,
                'country_code' => 'EU',
                'format_pattern' => '{symbol} {amount}',
                'metadata' => [
                    'countries' => ['Eurozone countries'],
                    'iso_numeric' => '978',
                    'minor_unit' => 2,
                ],
            ],

            // British Pound
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimal_places' => 2,
                'exchange_rate' => 105.000000, // 1 GBP = 105 INR (approximate)
                'is_default' => false,
                'is_active' => true,
                'country_code' => 'GB',
                'format_pattern' => '{symbol}{amount}',
                'metadata' => [
                    'countries' => ['United Kingdom'],
                    'iso_numeric' => '826',
                    'minor_unit' => 2,
                ],
            ],

            // UAE Dirham
            [
                'code' => 'AED',
                'name' => 'UAE Dirham',
                'symbol' => 'د.إ',
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimal_places' => 2,
                'exchange_rate' => 22.600000, // 1 AED = 22.6 INR (approximate)
                'is_default' => false,
                'is_active' => false, // Disabled by default
                'country_code' => 'AE',
                'format_pattern' => '{symbol} {amount}',
                'metadata' => [
                    'countries' => ['United Arab Emirates'],
                    'iso_numeric' => '784',
                    'minor_unit' => 2,
                ],
            ],
        ];

        foreach ($currencies as $currencyData) {
            CurrencyConfig::updateOrCreate(
                ['code' => $currencyData['code']],
                $currencyData
            );
        }

        $this->command->info('Currency configurations seeded successfully!');
        $this->command->info('Default currency: INR (Indian Rupee)');
        $this->command->warn('Note: Exchange rates are approximate. Update them via admin panel or API.');
    }
}
