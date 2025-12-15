<?php

namespace App\Services;

use App\Models\TaxConfiguration;
use App\Models\TaxRule;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * PROMPT 109: Tax Configuration Service
 * 
 * Manages admin-configurable tax settings (GST, TCS, TDS)
 * Works alongside TaxService (PROMPT 62) for complete tax management
 */
class TaxConfigurationService
{
    protected TaxService $taxService;
    protected CurrencyService $currencyService;

    public function __construct(TaxService $taxService, CurrencyService $currencyService)
    {
        $this->taxService = $taxService;
        $this->currencyService = $currencyService;
    }

    /**
     * Calculate comprehensive taxes for transaction
     * 
     * Returns complete tax breakdown including GST, TCS, TDS
     */
    public function calculateCompleteTax(
        float $baseAmount,
        array $context = []
    ): array {
        $result = [
            'base_amount' => $baseAmount,
            'subtotal' => $baseAmount,
            'currency_code' => $context['currency_code'] ?? $this->currencyService->getDefaultCurrency()->code,
            'currency_symbol' => $context['currency_symbol'] ?? $this->currencyService->getSymbol(),
            
            // GST
            'gst_applicable' => false,
            'gst_rate' => 0,
            'gst_amount' => 0,
            'is_intra_state' => null,
            'cgst_rate' => null,
            'cgst_amount' => 0,
            'sgst_rate' => null,
            'sgst_amount' => 0,
            'igst_rate' => null,
            'igst_amount' => 0,
            
            // TCS (Tax Collected at Source)
            'tcs_applicable' => false,
            'tcs_rate' => 0,
            'tcs_amount' => 0,
            'tcs_section' => null,
            'tcs_threshold' => null,
            'tcs_reason' => null,
            
            // TDS (Tax Deducted at Source)
            'tds_applicable' => false,
            'tds_rate' => 0,
            'tds_amount' => 0,
            'tds_section' => null,
            'tds_threshold' => null,
            'tds_reason' => null,
            
            // Reverse charge
            'is_reverse_charge' => false,
            'paid_by' => null,
            
            // Totals
            'total_tax' => 0,
            'grand_total' => $baseAmount,
            
            // Metadata
            'tax_calculation_details' => [],
            'applied_rules' => [],
        ];

        // 1. Calculate GST
        $gstResult = $this->calculateGST($baseAmount, $context);
        $result = array_merge($result, $gstResult);

        // 2. Calculate TCS if applicable
        if ($this->isTCSApplicable($context)) {
            $tcsResult = $this->calculateTCS($baseAmount, $context);
            $result = array_merge($result, $tcsResult);
        }

        // 3. Calculate TDS if applicable
        if ($this->isTDSApplicable($context)) {
            $tdsResult = $this->calculateTDS($baseAmount, $context);
            $result = array_merge($result, $tdsResult);
        }

        // 4. Calculate grand total
        $result['total_tax'] = $result['gst_amount'] + $result['tcs_amount'];
        $result['grand_total'] = $baseAmount + $result['total_tax'] - $result['tds_amount'];

        // 5. Store calculation details
        $result['tax_calculation_details'] = [
            'calculated_at' => now()->toISOString(),
            'calculation_method' => 'TaxConfigurationService',
            'context' => $context,
            'breakdown' => [
                'base' => $baseAmount,
                'gst' => $result['gst_amount'],
                'tcs' => $result['tcs_amount'],
                'tds' => $result['tds_amount'],
                'total' => $result['grand_total'],
            ],
        ];

        return $result;
    }

    /**
     * Calculate GST using TaxService (PROMPT 62)
     */
    protected function calculateGST(float $amount, array $context): array
    {
        // Check if GST is enabled
        if (!TaxConfiguration::getValue('gst_enabled', true)) {
            return [
                'gst_applicable' => false,
                'gst_amount' => 0,
            ];
        }

        // Use existing TaxService for GST calculation
        $gstResult = $this->taxService->calculateGST($amount, $context);

        // Determine if intra-state (CGST+SGST) or inter-state (IGST)
        $isIntraState = $this->isIntraStateTransaction($context);

        $result = [
            'gst_applicable' => $gstResult['gst_amount'] > 0,
            'gst_rate' => $gstResult['gst_rate'],
            'gst_amount' => $gstResult['gst_amount'],
            'is_intra_state' => $isIntraState,
            'is_reverse_charge' => $gstResult['is_reverse_charge'] ?? false,
            'paid_by' => $gstResult['paid_by'] ?? null,
        ];

        // Split GST into CGST+SGST or IGST
        if ($isIntraState) {
            $halfRate = $gstResult['gst_rate'] / 2;
            $result['cgst_rate'] = $halfRate;
            $result['cgst_amount'] = round($gstResult['gst_amount'] / 2, 2);
            $result['sgst_rate'] = $halfRate;
            $result['sgst_amount'] = round($gstResult['gst_amount'] / 2, 2);
            $result['igst_rate'] = null;
            $result['igst_amount'] = 0;
        } else {
            $result['cgst_rate'] = null;
            $result['cgst_amount'] = 0;
            $result['sgst_rate'] = null;
            $result['sgst_amount'] = 0;
            $result['igst_rate'] = $gstResult['gst_rate'];
            $result['igst_amount'] = $gstResult['gst_amount'];
        }

        if (isset($gstResult['rule'])) {
            $result['applied_rules'][] = [
                'type' => 'gst',
                'rule_id' => $gstResult['rule']->id,
                'rule_code' => $gstResult['rule']->code,
                'rule_name' => $gstResult['rule']->name,
            ];
        }

        return $result;
    }

    /**
     * Calculate TCS (Tax Collected at Source)
     */
    protected function calculateTCS(float $amount, array $context): array
    {
        $tcsEnabled = TaxConfiguration::getValue('tcs_enabled', false);
        
        if (!$tcsEnabled) {
            return [
                'tcs_applicable' => false,
                'tcs_amount' => 0,
            ];
        }

        // Get TCS configuration
        $tcsThreshold = TaxConfiguration::getValue('tcs_threshold_amount', 50000000); // 5 Cr default
        $tcsRate = TaxConfiguration::getValue('tcs_rate_percentage', 0.1); // 0.1% default
        $tcsSection = TaxConfiguration::getValue('tcs_section_code', '206C(1H)');

        // Check if amount exceeds threshold
        if ($amount < $tcsThreshold) {
            return [
                'tcs_applicable' => false,
                'tcs_amount' => 0,
                'tcs_threshold' => $tcsThreshold,
                'tcs_reason' => "Amount below threshold of {$this->currencyService->format($tcsThreshold)}",
            ];
        }

        // Calculate TCS on total amount (including GST if applicable)
        $taxableAmount = $amount;
        if (isset($context['include_gst_in_tcs']) && $context['include_gst_in_tcs']) {
            $taxableAmount += ($context['gst_amount'] ?? 0);
        }

        $tcsAmount = round(($taxableAmount * $tcsRate) / 100, 2);

        return [
            'tcs_applicable' => true,
            'tcs_rate' => $tcsRate,
            'tcs_amount' => $tcsAmount,
            'tcs_section' => $tcsSection,
            'tcs_threshold' => $tcsThreshold,
            'tcs_reason' => 'Amount exceeds threshold',
            'applied_rules' => [[
                'type' => 'tcs',
                'section' => $tcsSection,
                'rate' => $tcsRate,
                'threshold' => $tcsThreshold,
            ]],
        ];
    }

    /**
     * Calculate TDS (Tax Deducted at Source)
     */
    protected function calculateTDS(float $amount, array $context): array
    {
        // Use existing TaxService for TDS calculation
        $tdsResult = $this->taxService->calculateTDS($amount, $context);

        if (!$tdsResult['applies']) {
            return [
                'tds_applicable' => false,
                'tds_amount' => 0,
                'tds_reason' => $tdsResult['reason'] ?? 'TDS not applicable',
            ];
        }

        return [
            'tds_applicable' => true,
            'tds_rate' => $tdsResult['tds_rate'],
            'tds_amount' => $tdsResult['tds_amount'],
            'tds_section' => $tdsResult['tds_section'],
            'tds_threshold' => $tdsResult['threshold'] ?? null,
            'applied_rules' => [[
                'type' => 'tds',
                'section' => $tdsResult['tds_section'],
                'rate' => $tdsResult['tds_rate'],
                'rule_id' => $tdsResult['rule']->id ?? null,
            ]],
        ];
    }

    /**
     * Check if transaction is intra-state (same state)
     */
    protected function isIntraStateTransaction(array $context): bool
    {
        // Get seller and buyer states
        $sellerState = $context['seller_state_code'] ?? $context['vendor_state_code'] ?? null;
        $buyerState = $context['buyer_state_code'] ?? $context['customer_state_code'] ?? null;

        if (!$sellerState || !$buyerState) {
            // Default to company state from settings
            $companyState = TaxConfiguration::getValue('company_state_code', 'MH');
            $sellerState = $sellerState ?? $companyState;
            $buyerState = $buyerState ?? $companyState;
        }

        return $sellerState === $buyerState;
    }

    /**
     * Check if TCS is applicable for this transaction
     */
    protected function isTCSApplicable(array $context): bool
    {
        // TCS typically applies to high-value B2C transactions
        $tcsEnabled = TaxConfiguration::getValue('tcs_enabled', false);
        
        if (!$tcsEnabled) {
            return false;
        }

        // Check transaction type
        $appliesTo = TaxConfiguration::getValue('tcs_applies_to', ['invoice', 'purchase_order']);
        $transactionType = $context['transaction_type'] ?? $context['applies_to'] ?? 'invoice';

        return in_array($transactionType, (array) $appliesTo);
    }

    /**
     * Check if TDS is applicable for this transaction
     */
    protected function isTDSApplicable(array $context): bool
    {
        // TDS applies to vendor payouts
        $tdsEnabled = TaxConfiguration::getValue('tds_enabled', true);
        
        if (!$tdsEnabled) {
            return false;
        }

        $appliesTo = $context['applies_to'] ?? null;
        return in_array($appliesTo, ['payout', 'vendor_payment']);
    }

    /**
     * Get all tax configurations
     */
    public function getAllConfigurations(): array
    {
        return [
            'gst' => TaxConfiguration::getGSTConfig(),
            'tcs' => TaxConfiguration::getTCSConfig(),
            'tds' => TaxConfiguration::getTDSConfig(),
            'currency' => $this->currencyService->getCurrencyDetails(),
        ];
    }

    /**
     * Update tax configuration
     */
    public function updateConfiguration(string $key, $value, string $type = 'general'): TaxConfiguration
    {
        $dataType = $this->determineDataType($value);
        
        return TaxConfiguration::setValue($key, $value, $dataType, $type);
    }

    /**
     * Determine data type from value
     */
    protected function determineDataType($value): string
    {
        if (is_bool($value)) return TaxConfiguration::DATA_BOOLEAN;
        if (is_int($value)) return TaxConfiguration::DATA_INTEGER;
        if (is_float($value)) return TaxConfiguration::DATA_FLOAT;
        if (is_array($value)) return TaxConfiguration::DATA_ARRAY;
        return TaxConfiguration::DATA_STRING;
    }

    /**
     * Get tax summary for display
     */
    public function getTaxSummary(array $taxCalculation): array
    {
        $currency = $this->currencyService->getDefaultCurrency();

        return [
            'subtotal' => $currency->format($taxCalculation['subtotal']),
            'gst' => [
                'applicable' => $taxCalculation['gst_applicable'],
                'rate' => $taxCalculation['gst_rate'] . '%',
                'amount' => $currency->format($taxCalculation['gst_amount']),
                'breakdown' => $taxCalculation['is_intra_state'] 
                    ? "CGST: {$currency->format($taxCalculation['cgst_amount'])} + SGST: {$currency->format($taxCalculation['sgst_amount'])}"
                    : "IGST: {$currency->format($taxCalculation['igst_amount'])}",
            ],
            'tcs' => [
                'applicable' => $taxCalculation['tcs_applicable'],
                'rate' => $taxCalculation['tcs_rate'] . '%',
                'amount' => $currency->format($taxCalculation['tcs_amount']),
                'section' => $taxCalculation['tcs_section'],
            ],
            'tds' => [
                'applicable' => $taxCalculation['tds_applicable'],
                'rate' => $taxCalculation['tds_rate'] . '%',
                'amount' => $currency->format($taxCalculation['tds_amount']),
                'section' => $taxCalculation['tds_section'],
            ],
            'grand_total' => $currency->format($taxCalculation['grand_total']),
        ];
    }
    
    /**
     * Get all configurations grouped by type
     */
    public function getAllConfigurations(): array
    {
        return TaxConfiguration::all()
            ->groupBy('config_type')
            ->map(function ($configs) {
                return $configs->mapWithKeys(function ($config) {
                    return [$config->key => $config->getTypedValue()];
                });
            })
            ->toArray();
    }
}
