<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxConfiguration;

/**
 * PROMPT 109: Tax Configuration Seeder
 * 
 * Seeds default tax configurations (GST, TCS, TDS settings)
 * Works alongside TaxRulesSeeder (PROMPT 62) for complete tax system
 */
class TaxConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            // ===== GST CONFIGURATIONS =====
            [
                'key' => 'gst_enabled',
                'name' => 'GST Enabled',
                'description' => 'Enable/disable GST calculations globally',
                'config_type' => TaxConfiguration::TYPE_GST,
                'data_type' => TaxConfiguration::DATA_BOOLEAN,
                'group' => TaxConfiguration::GROUP_TAX_RULES,
                'value' => '1', // true
                'is_active' => true,
                'applies_to' => 'all',
                'country_code' => 'IN',
                'metadata' => ['default' => true],
            ],

            [
                'key' => 'default_gst_rate',
                'name' => 'Default GST Rate (%)',
                'description' => 'Default GST rate if no specific rule applies',
                'config_type' => TaxConfiguration::TYPE_GST,
                'data_type' => TaxConfiguration::DATA_FLOAT,
                'group' => TaxConfiguration::GROUP_TAX_RATES,
                'value' => '18.00',
                'is_active' => true,
                'applies_to' => 'all',
                'country_code' => 'IN',
                'validation_rules' => ['min' => 0, 'max' => 100],
                'metadata' => ['recommended' => 18],
            ],

            [
                'key' => 'company_state_code',
                'name' => 'Company State Code',
                'description' => 'State code for company registration (for intra/inter state GST)',
                'config_type' => TaxConfiguration::TYPE_GENERAL,
                'data_type' => TaxConfiguration::DATA_STRING,
                'group' => TaxConfiguration::GROUP_TAX_RULES,
                'value' => 'MH', // Maharashtra
                'is_active' => true,
                'applies_to' => 'all',
                'country_code' => 'IN',
                'metadata' => ['examples' => ['MH', 'DL', 'KA', 'TN', 'GJ']],
            ],

            [
                'key' => 'company_gstin',
                'name' => 'Company GSTIN',
                'description' => 'Company GST Identification Number',
                'config_type' => TaxConfiguration::TYPE_GENERAL,
                'data_type' => TaxConfiguration::DATA_STRING,
                'group' => TaxConfiguration::GROUP_TAX_RULES,
                'value' => '',
                'is_active' => true,
                'applies_to' => 'all',
                'country_code' => 'IN',
                'validation_rules' => ['regex' => '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
                'metadata' => ['format' => '27AABCU9603R1ZX'],
            ],

            // ===== TCS CONFIGURATIONS =====
            [
                'key' => 'tcs_enabled',
                'name' => 'TCS Enabled',
                'description' => 'Enable Tax Collected at Source (Section 206C(1H))',
                'config_type' => TaxConfiguration::TYPE_TCS,
                'data_type' => TaxConfiguration::DATA_BOOLEAN,
                'group' => TaxConfiguration::GROUP_TCS_RULES,
                'value' => '0', // false by default
                'is_active' => true,
                'applies_to' => 'invoice,purchase_order',
                'country_code' => 'IN',
                'metadata' => [
                    'applicable_from' => '2020-10-01',
                    'note' => 'Applicable on high-value transactions',
                ],
            ],

            [
                'key' => 'tcs_threshold_amount',
                'name' => 'TCS Threshold Amount',
                'description' => 'Minimum transaction amount for TCS applicability',
                'config_type' => TaxConfiguration::TYPE_TCS,
                'data_type' => TaxConfiguration::DATA_FLOAT,
                'group' => TaxConfiguration::GROUP_TCS_RULES,
                'value' => '50000000', // 5 Crore
                'is_active' => true,
                'applies_to' => 'invoice,purchase_order',
                'country_code' => 'IN',
                'validation_rules' => ['min' => 0],
                'metadata' => [
                    'note' => 'As per Section 206C(1H)',
                    'default' => 50000000,
                ],
            ],

            [
                'key' => 'tcs_rate_percentage',
                'name' => 'TCS Rate (%)',
                'description' => 'Rate of TCS to be collected',
                'config_type' => TaxConfiguration::TYPE_TCS,
                'data_type' => TaxConfiguration::DATA_FLOAT,
                'group' => TaxConfiguration::GROUP_TCS_RULES,
                'value' => '0.1', // 0.1%
                'is_active' => true,
                'applies_to' => 'invoice,purchase_order',
                'country_code' => 'IN',
                'validation_rules' => ['min' => 0, 'max' => 100],
                'metadata' => [
                    'section' => '206C(1H)',
                    'with_pan' => 0.1,
                    'without_pan' => 1.0,
                ],
            ],

            [
                'key' => 'tcs_section_code',
                'name' => 'TCS Section Code',
                'description' => 'Income Tax section for TCS',
                'config_type' => TaxConfiguration::TYPE_TCS,
                'data_type' => TaxConfiguration::DATA_STRING,
                'group' => TaxConfiguration::GROUP_TCS_RULES,
                'value' => '206C(1H)',
                'is_active' => true,
                'applies_to' => 'invoice,purchase_order',
                'country_code' => 'IN',
                'metadata' => ['description' => 'TCS on sale of goods exceeding specified limit'],
            ],

            [
                'key' => 'tcs_applies_to',
                'name' => 'TCS Applies To',
                'description' => 'Transaction types where TCS should be calculated',
                'config_type' => TaxConfiguration::TYPE_TCS,
                'data_type' => TaxConfiguration::DATA_ARRAY,
                'group' => TaxConfiguration::GROUP_TCS_RULES,
                'value' => '["invoice","purchase_order"]',
                'is_active' => true,
                'applies_to' => 'all',
                'country_code' => 'IN',
                'metadata' => ['options' => ['invoice', 'purchase_order', 'booking']],
            ],

            // ===== TDS CONFIGURATIONS =====
            [
                'key' => 'tds_enabled',
                'name' => 'TDS Enabled',
                'description' => 'Enable Tax Deducted at Source globally',
                'config_type' => TaxConfiguration::TYPE_TDS,
                'data_type' => TaxConfiguration::DATA_BOOLEAN,
                'group' => TaxConfiguration::GROUP_TDS_RULES,
                'value' => '1', // true
                'is_active' => true,
                'applies_to' => 'payout,vendor_payment',
                'country_code' => 'IN',
                'metadata' => ['note' => 'Controlled by TaxRule model for specific sections'],
            ],

            [
                'key' => 'tds_default_threshold',
                'name' => 'Default TDS Threshold',
                'description' => 'Default minimum amount for TDS deduction',
                'config_type' => TaxConfiguration::TYPE_TDS,
                'data_type' => TaxConfiguration::DATA_FLOAT,
                'group' => TaxConfiguration::GROUP_TDS_RULES,
                'value' => '30000', // ₹30,000
                'is_active' => true,
                'applies_to' => 'payout,vendor_payment',
                'country_code' => 'IN',
                'validation_rules' => ['min' => 0],
                'metadata' => [
                    'note' => 'Specific sections may override this',
                    'common_thresholds' => [
                        '194C' => 30000, // Contractors
                        '194J' => 30000, // Professional services
                        '194H' => 15000, // Commission
                    ],
                ],
            ],

            // ===== GENERAL TAX SETTINGS =====
            [
                'key' => 'auto_calculate_taxes',
                'name' => 'Auto Calculate Taxes',
                'description' => 'Automatically calculate taxes for all transactions',
                'config_type' => TaxConfiguration::TYPE_GENERAL,
                'data_type' => TaxConfiguration::DATA_BOOLEAN,
                'group' => TaxConfiguration::GROUP_TAX_RULES,
                'value' => '1', // true
                'is_active' => true,
                'applies_to' => 'all',
                'country_code' => 'IN',
                'metadata' => ['recommended' => true],
            ],

            [
                'key' => 'include_gst_in_tcs_calculation',
                'name' => 'Include GST in TCS Calculation',
                'description' => 'Whether to include GST amount when calculating TCS',
                'config_type' => TaxConfiguration::TYPE_GENERAL,
                'data_type' => TaxConfiguration::DATA_BOOLEAN,
                'group' => TaxConfiguration::GROUP_TAX_RULES,
                'value' => '1', // true
                'is_active' => true,
                'applies_to' => 'all',
                'country_code' => 'IN',
                'metadata' => ['note' => 'TCS is calculated on invoice value including GST'],
            ],

            [
                'key' => 'tax_rounding_method',
                'name' => 'Tax Rounding Method',
                'description' => 'Method to round calculated tax amounts',
                'config_type' => TaxConfiguration::TYPE_GENERAL,
                'data_type' => TaxConfiguration::DATA_STRING,
                'group' => TaxConfiguration::GROUP_TAX_RULES,
                'value' => 'round', // Options: round, ceil, floor
                'is_active' => true,
                'applies_to' => 'all',
                'country_code' => 'IN',
                'validation_rules' => ['in' => ['round', 'ceil', 'floor']],
                'metadata' => ['options' => ['round' => 'Standard rounding', 'ceil' => 'Round up', 'floor' => 'Round down']],
            ],
        ];

        foreach ($configurations as $configData) {
            TaxConfiguration::updateOrCreate(
                ['key' => $configData['key']],
                $configData
            );
        }

        $this->command->info('Tax configurations seeded successfully!');
        $this->command->info('✓ GST: Enabled (18% default)');
        $this->command->info('✓ TCS: Disabled by default (enable in admin if needed)');
        $this->command->info('✓ TDS: Enabled (controlled by TaxRule model)');
        $this->command->warn('Note: Configure company GSTIN and state code in admin settings');
    }
}
