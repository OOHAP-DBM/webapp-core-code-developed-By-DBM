<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxRule;
use Carbon\Carbon;

class TaxRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            // GST Rules for India
            [
                'name' => 'GST - Standard Rate (18%)',
                'code' => 'GST_IN_18',
                'tax_type' => TaxRule::TYPE_GST,
                'rate' => 18.00,
                'calculation_method' => TaxRule::METHOD_PERCENTAGE,
                'applies_to' => TaxRule::APPLIES_ALL,
                'conditions' => null,
                'is_reverse_charge' => false,
                'reverse_charge_conditions' => null,
                'is_tds' => false,
                'tds_threshold' => null,
                'tds_section' => null,
                'country_code' => 'IN',
                'applicable_states' => null, // Applies to all states
                'priority' => 10,
                'is_active' => true,
                'effective_from' => Carbon::parse('2017-07-01'), // GST implementation date
                'effective_until' => null,
                'description' => 'Standard GST rate of 18% applicable to most services and goods in India',
                'metadata' => [
                    'hsn_code' => null,
                    'sac_code' => '998599', // Advertising services
                    'notes' => 'Standard rate for advertising and marketing services',
                ],
            ],

            // GST Reverse Charge for B2B
            [
                'name' => 'GST - Reverse Charge (B2B)',
                'code' => 'GST_IN_RC_B2B',
                'tax_type' => TaxRule::TYPE_REVERSE_CHARGE,
                'rate' => 18.00,
                'calculation_method' => TaxRule::METHOD_PERCENTAGE,
                'applies_to' => TaxRule::APPLIES_BOOKING,
                'conditions' => [
                    'customer_type' => 'business',
                    'has_gstin' => true,
                ],
                'is_reverse_charge' => true,
                'reverse_charge_conditions' => 'Applicable when customer is registered business with GSTIN',
                'is_tds' => false,
                'tds_threshold' => null,
                'tds_section' => null,
                'country_code' => 'IN',
                'applicable_states' => null,
                'priority' => 5, // Higher priority than standard GST
                'is_active' => true,
                'effective_from' => Carbon::parse('2017-07-01'),
                'effective_until' => null,
                'description' => 'Reverse charge mechanism for B2B transactions where customer pays GST directly',
                'metadata' => [
                    'applicable_to' => 'B2B transactions',
                    'notes' => 'Customer must have valid GSTIN',
                ],
            ],

            // TDS Section 194J - Professional Services
            [
                'name' => 'TDS - Section 194J (Professional Services)',
                'code' => 'TDS_IN_194J',
                'tax_type' => TaxRule::TYPE_TDS,
                'rate' => 10.00,
                'calculation_method' => TaxRule::METHOD_PERCENTAGE,
                'applies_to' => TaxRule::APPLIES_PAYOUT,
                'conditions' => [
                    'vendor_type' => 'professional',
                ],
                'is_reverse_charge' => false,
                'reverse_charge_conditions' => null,
                'is_tds' => true,
                'tds_threshold' => 30000.00, // Threshold for TDS deduction
                'tds_section' => '194J',
                'country_code' => 'IN',
                'applicable_states' => null,
                'priority' => 10,
                'is_active' => true,
                'effective_from' => Carbon::parse('2020-04-01'), // Financial year 2020-21
                'effective_until' => null,
                'description' => 'TDS on professional or technical services under Section 194J - 10% rate',
                'metadata' => [
                    'pan_required' => true,
                    'form' => '26AS',
                    'notes' => 'Applicable to payments for professional services, royalty, etc.',
                ],
            ],

            // TDS Section 194C - Contract Services
            [
                'name' => 'TDS - Section 194C (Contractors)',
                'code' => 'TDS_IN_194C',
                'tax_type' => TaxRule::TYPE_TDS,
                'rate' => 1.00, // 1% for individuals/HUF, 2% for others
                'calculation_method' => TaxRule::METHOD_PERCENTAGE,
                'applies_to' => TaxRule::APPLIES_PAYOUT,
                'conditions' => [
                    'vendor_type' => 'contractor',
                ],
                'is_reverse_charge' => false,
                'reverse_charge_conditions' => null,
                'is_tds' => true,
                'tds_threshold' => 30000.00,
                'tds_section' => '194C',
                'country_code' => 'IN',
                'applicable_states' => null,
                'priority' => 10,
                'is_active' => true,
                'effective_from' => Carbon::parse('2020-04-01'),
                'effective_until' => null,
                'description' => 'TDS on payments to contractors under Section 194C - 1-2% rate',
                'metadata' => [
                    'rate_individual' => 1.00,
                    'rate_company' => 2.00,
                    'single_transaction_limit' => 30000,
                    'aggregate_limit' => 100000,
                ],
            ],

            // TDS Section 194H - Commission
            [
                'name' => 'TDS - Section 194H (Commission)',
                'code' => 'TDS_IN_194H',
                'tax_type' => TaxRule::TYPE_TDS,
                'rate' => 5.00,
                'calculation_method' => TaxRule::METHOD_PERCENTAGE,
                'applies_to' => TaxRule::APPLIES_COMMISSION,
                'conditions' => null,
                'is_reverse_charge' => false,
                'reverse_charge_conditions' => null,
                'is_tds' => true,
                'tds_threshold' => 15000.00,
                'tds_section' => '194H',
                'country_code' => 'IN',
                'applicable_states' => null,
                'priority' => 10,
                'is_active' => true,
                'effective_from' => Carbon::parse('2020-04-01'),
                'effective_until' => null,
                'description' => 'TDS on commission or brokerage under Section 194H - 5% rate',
                'metadata' => [
                    'threshold' => 15000,
                    'applicable_to' => 'Commission, brokerage, discount payments',
                ],
            ],

            // GST Reduced Rate (5%) - Optional
            [
                'name' => 'GST - Reduced Rate (5%)',
                'code' => 'GST_IN_5',
                'tax_type' => TaxRule::TYPE_GST,
                'rate' => 5.00,
                'calculation_method' => TaxRule::METHOD_PERCENTAGE,
                'applies_to' => TaxRule::APPLIES_BOOKING,
                'conditions' => [
                    'min_amount' => 0,
                    'max_amount' => 10000,
                ],
                'is_reverse_charge' => false,
                'reverse_charge_conditions' => null,
                'is_tds' => false,
                'tds_threshold' => null,
                'tds_section' => null,
                'country_code' => 'IN',
                'applicable_states' => null,
                'priority' => 20, // Lower priority than standard
                'is_active' => false, // Disabled by default
                'effective_from' => Carbon::now(),
                'effective_until' => null,
                'description' => 'Reduced GST rate of 5% for small value transactions (Optional)',
                'metadata' => [
                    'notes' => 'Can be activated for promotional campaigns or small bookings',
                ],
            ],

            // GST 12% Rate - Optional
            [
                'name' => 'GST - Moderate Rate (12%)',
                'code' => 'GST_IN_12',
                'tax_type' => TaxRule::TYPE_GST,
                'rate' => 12.00,
                'calculation_method' => TaxRule::METHOD_PERCENTAGE,
                'applies_to' => TaxRule::APPLIES_BOOKING,
                'conditions' => null,
                'is_reverse_charge' => false,
                'reverse_charge_conditions' => null,
                'is_tds' => false,
                'tds_threshold' => null,
                'tds_section' => null,
                'country_code' => 'IN',
                'applicable_states' => null,
                'priority' => 15,
                'is_active' => false, // Disabled by default
                'effective_from' => Carbon::now(),
                'effective_until' => null,
                'description' => '12% GST rate for specific service categories (Optional)',
                'metadata' => [
                    'notes' => 'Can be used for specific categories if needed',
                ],
            ],
        ];

        foreach ($rules as $ruleData) {
            TaxRule::updateOrCreate(
                ['code' => $ruleData['code']],
                $ruleData
            );
        }

        $this->command->info('Tax rules seeded successfully!');
        $this->command->info('Active rules: ' . TaxRule::active()->count());
        $this->command->info('GST rules: ' . TaxRule::where('tax_type', 'gst')->count());
        $this->command->info('TDS rules: ' . TaxRule::where('tax_type', 'tds')->count());
    }
}
