<?php

namespace App\Services;

use App\Models\TaxRule;
use App\Models\TaxCalculation;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TaxService
{
    /**
     * Calculate and apply tax to a taxable entity
     */
    public function applyTax(
        Model $taxable,
        float $baseAmount,
        string $appliesTo,
        array $context = []
    ): array {
        // Find applicable tax rules
        $rules = $this->getApplicableRules($appliesTo, $baseAmount, $context);

        if ($rules->isEmpty()) {
            Log::info('No tax rules found', [
                'applies_to' => $appliesTo,
                'base_amount' => $baseAmount,
                'context' => $context,
            ]);

            return [
                'tax_amount' => 0,
                'calculations' => [],
                'total_with_tax' => $baseAmount,
            ];
        }

        $totalTax = 0;
        $calculations = [];

        // Apply each rule
        foreach ($rules as $rule) {
            $taxAmount = $rule->calculateTaxAmount($baseAmount, $context);

            // Create calculation record
            $calculation = $this->recordCalculation(
                $taxable,
                $rule,
                $baseAmount,
                $taxAmount,
                $context
            );

            $totalTax += $taxAmount;
            $calculations[] = $calculation;

            Log::info('Tax calculated', [
                'rule' => $rule->code,
                'base_amount' => $baseAmount,
                'tax_amount' => $taxAmount,
                'calculation_id' => $calculation->id,
            ]);
        }

        return [
            'tax_amount' => round($totalTax, 2),
            'calculations' => $calculations,
            'total_with_tax' => round($baseAmount + $totalTax, 2),
            'breakdown' => $this->formatBreakdown($calculations),
        ];
    }

    /**
     * Get applicable tax rules
     */
    public function getApplicableRules(
        string $appliesTo,
        float $amount,
        array $context = []
    ): \Illuminate\Database\Eloquent\Collection {
        $date = $context['date'] ?? now();
        $countryCode = $context['country_code'] ?? 'IN';
        $state = $context['state'] ?? null;

        $query = TaxRule::active()
            ->byAppliesTo($appliesTo)
            ->byCountry($countryCode)
            ->forDate($date)
            ->orderByPriority();

        // State filter
        if ($state) {
            $query->where(function ($q) use ($state) {
                $q->whereNull('applicable_states')
                  ->orWhereJsonContains('applicable_states', $state);
            });
        }

        $rules = $query->get();

        // Filter by conditions
        return $rules->filter(function ($rule) use ($amount, $context) {
            return $rule->appliesTo($amount) && 
                   $this->matchesContextConditions($rule, $context);
        });
    }

    /**
     * Calculate GST specifically
     */
    public function calculateGST(
        float $amount,
        array $context = []
    ): array {
        $context['tax_type'] = TaxRule::TYPE_GST;
        
        $rules = $this->getApplicableRules(
            $context['applies_to'] ?? TaxRule::APPLIES_ALL,
            $amount,
            $context
        )->where('tax_type', TaxRule::TYPE_GST);

        if ($rules->isEmpty()) {
            // Fallback to default GST rate
            $defaultRate = app(SettingsService::class)->get('booking_tax_rate', 18.00);
            return [
                'gst_amount' => round($amount * ($defaultRate / 100), 2),
                'gst_rate' => $defaultRate,
                'is_reverse_charge' => false,
            ];
        }

        $rule = $rules->first();
        $gstAmount = $rule->calculateTaxAmount($amount, $context);
        $isReverseCharge = $rule->shouldApplyReverseCharge($context);

        return [
            'gst_amount' => $gstAmount,
            'gst_rate' => $rule->rate,
            'is_reverse_charge' => $isReverseCharge,
            'paid_by' => $isReverseCharge ? ($context['paid_by'] ?? 'customer') : null,
            'rule' => $rule,
        ];
    }

    /**
     * Calculate TDS specifically
     */
    public function calculateTDS(
        float $amount,
        array $context = []
    ): array {
        $context['tax_type'] = TaxRule::TYPE_TDS;
        
        $rules = $this->getApplicableRules(
            $context['applies_to'] ?? TaxRule::APPLIES_PAYOUT,
            $amount,
            $context
        )->where('tax_type', TaxRule::TYPE_TDS)
          ->where('is_tds', true);

        if ($rules->isEmpty()) {
            return [
                'tds_amount' => 0,
                'tds_rate' => 0,
                'tds_section' => null,
                'applies' => false,
            ];
        }

        $rule = $rules->first();

        // Check TDS threshold
        if ($rule->tds_threshold && $amount < $rule->tds_threshold) {
            return [
                'tds_amount' => 0,
                'tds_rate' => $rule->rate,
                'tds_section' => $rule->tds_section,
                'applies' => false,
                'reason' => "Amount below threshold of ₹" . number_format($rule->tds_threshold, 2),
            ];
        }

        $tdsAmount = $rule->calculateTaxAmount($amount, $context);

        return [
            'tds_amount' => $tdsAmount,
            'tds_rate' => $rule->rate,
            'tds_section' => $rule->tds_section,
            'applies' => true,
            'rule' => $rule,
        ];
    }

    /**
     * Check if reverse charge applies
     */
    public function checkReverseCharge(array $context = []): bool
    {
        $customerType = $context['customer_type'] ?? 'individual';
        $hasGSTIN = $context['has_gstin'] ?? false;
        $vendorType = $context['vendor_type'] ?? 'individual';

        // Reverse charge typically applies for B2B transactions
        return $hasGSTIN && 
               $customerType === 'business' && 
               $vendorType === 'business';
    }

    /**
     * Record tax calculation
     */
    protected function recordCalculation(
        Model $taxable,
        TaxRule $rule,
        float $baseAmount,
        float $taxAmount,
        array $context = []
    ): TaxCalculation {
        $isReverseCharge = $rule->is_reverse_charge && 
                          $rule->shouldApplyReverseCharge($context);

        return TaxCalculation::create([
            'taxable_type' => get_class($taxable),
            'taxable_id' => $taxable->id,
            'tax_rule_id' => $rule->id,
            'tax_code' => $rule->code,
            'tax_name' => $rule->name,
            'tax_type' => $rule->tax_type,
            'base_amount' => $baseAmount,
            'tax_rate' => $rule->rate,
            'tax_amount' => $taxAmount,
            'is_reverse_charge' => $isReverseCharge,
            'paid_by' => $isReverseCharge ? ($context['paid_by'] ?? 'customer') : null,
            'is_tds' => $rule->is_tds,
            'tds_section' => $rule->tds_section,
            'tds_deducted' => $rule->is_tds ? $taxAmount : null,
            'calculation_snapshot' => [
                'rule' => $rule->toArray(),
                'context' => $context,
                'calculated_at' => now()->toISOString(),
                'calculation_method' => $rule->calculation_method,
            ],
            'calculated_by' => $context['calculated_by'] ?? class_basename($this),
            'calculated_at' => now(),
        ]);
    }

    /**
     * Check if rule matches context conditions
     */
    protected function matchesContextConditions(TaxRule $rule, array $context): bool
    {
        if (!$rule->conditions) {
            return true;
        }

        $conditions = $rule->conditions;

        // User type condition
        if (isset($conditions['user_type']) && isset($context['user_type'])) {
            if ($conditions['user_type'] !== $context['user_type']) {
                return false;
            }
        }

        // Customer type condition
        if (isset($conditions['customer_type']) && isset($context['customer_type'])) {
            if ($conditions['customer_type'] !== $context['customer_type']) {
                return false;
            }
        }

        // Has GSTIN condition
        if (isset($conditions['has_gstin']) && isset($context['has_gstin'])) {
            if ($conditions['has_gstin'] !== $context['has_gstin']) {
                return false;
            }
        }

        // Entity type condition (for polymorphic)
        if (isset($conditions['entity_type']) && isset($context['entity_type'])) {
            if ($conditions['entity_type'] !== $context['entity_type']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Format breakdown of calculations
     */
    protected function formatBreakdown(array $calculations): array
    {
        return array_map(function ($calc) {
            return [
                'id' => $calc->id,
                'tax_name' => $calc->tax_name,
                'tax_type' => $calc->tax_type,
                'rate' => $calc->tax_rate,
                'amount' => $calc->tax_amount,
                'is_reverse_charge' => $calc->is_reverse_charge,
                'is_tds' => $calc->is_tds,
            ];
        }, $calculations);
    }

    /**
     * Get tax calculations for entity
     */
    public function getTaxCalculations(Model $taxable): \Illuminate\Database\Eloquent\Collection
    {
        return TaxCalculation::forTaxable(get_class($taxable), $taxable->id)
            ->with('taxRule')
            ->get();
    }

    /**
     * Get tax summary for entity
     */
    public function getTaxSummary(Model $taxable): array
    {
        $calculations = $this->getTaxCalculations($taxable);

        $summary = [
            'total_tax' => $calculations->sum('tax_amount'),
            'total_tds' => $calculations->where('is_tds', true)->sum('tds_deducted'),
            'total_gst' => $calculations->where('tax_type', 'gst')->sum('tax_amount'),
            'reverse_charge_amount' => $calculations->where('is_reverse_charge', true)->sum('tax_amount'),
            'breakdown_by_type' => [],
        ];

        // Group by tax type
        $byType = $calculations->groupBy('tax_type');
        foreach ($byType as $type => $calcs) {
            $summary['breakdown_by_type'][$type] = [
                'count' => $calcs->count(),
                'total' => $calcs->sum('tax_amount'),
                'average_rate' => $calcs->avg('tax_rate'),
            ];
        }

        return $summary;
    }

    /**
     * Get cached tax rate for backwards compatibility
     */
    public function getDefaultTaxRate(string $appliesTo = 'booking'): float
    {
        $cacheKey = "default_tax_rate:{$appliesTo}";

        return Cache::remember($cacheKey, 3600, function () use ($appliesTo) {
            $rule = TaxRule::active()
                ->byAppliesTo($appliesTo)
                ->forDate(now())
                ->orderByPriority()
                ->first();

            return $rule ? $rule->rate : 18.00; // Default fallback
        });
    }

    /**
     * Clear tax rate cache
     */
    public function clearCache(): void
    {
        Cache::forget('default_tax_rate:booking');
        Cache::forget('default_tax_rate:commission');
        Cache::forget('default_tax_rate:payout');
        Cache::forget('default_tax_rate:all');
    }

    /**
     * Validate tax context
     */
    public function validateContext(array $context): array
    {
        return [
            'applies_to' => $context['applies_to'] ?? TaxRule::APPLIES_ALL,
            'country_code' => $context['country_code'] ?? 'IN',
            'state' => $context['state'] ?? null,
            'date' => $context['date'] ?? now(),
            'user_type' => $context['user_type'] ?? null,
            'customer_type' => $context['customer_type'] ?? 'individual',
            'vendor_type' => $context['vendor_type'] ?? 'individual',
            'has_gstin' => $context['has_gstin'] ?? false,
            'entity_type' => $context['entity_type'] ?? null,
            'calculated_by' => $context['calculated_by'] ?? 'TaxService',
        ];
    }
}
