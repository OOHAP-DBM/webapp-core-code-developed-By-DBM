<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\Booking;
use App\Models\Hoarding;
use Illuminate\Support\Facades\Log;

class CommissionRuleService
{
    /**
     * Find the best matching commission rule for a booking
     *
     * @param array $bookingData
     * @return CommissionRule|null
     */
    public function findBestRule(array $bookingData): ?CommissionRule
    {
        // Get all active rules ordered by priority
        $rules = CommissionRule::active()
            ->byPriority()
            ->validNow()
            ->get();

        // Find first matching rule
        foreach ($rules as $rule) {
            if ($rule->appliesTo($bookingData)) {
                Log::info("Commission rule matched", [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'booking_data' => $bookingData,
                ]);
                return $rule;
            }
        }

        // No specific rule found, look for fallback flat rate
        $fallback = CommissionRule::active()
            ->byType(CommissionRule::RULE_TYPE_FLAT)
            ->validNow()
            ->whereNull('vendor_id')
            ->whereNull('hoarding_id')
            ->whereNull('city')
            ->whereNull('area')
            ->first();

        if ($fallback) {
            Log::info("Using fallback commission rule", [
                'rule_id' => $fallback->id,
                'rule_name' => $fallback->name,
            ]);
        }

        return $fallback;
    }

    /**
     * Calculate commission for a booking using rules
     *
     * @param Booking|array $booking
     * @param float $amount
     * @return array [commission_amount, commission_rate, rule_id, rule_name]
     */
    public function calculateCommission($booking, float $amount): array
    {
        // Prepare booking data
        $bookingData = $this->prepareBookingData($booking);
        
        // Find matching rule
        $rule = $this->findBestRule($bookingData);

        if (!$rule) {
            // No rule found, use default from settings or 0
            Log::warning("No commission rule found for booking", ['booking_data' => $bookingData]);
            
            return [
                'commission_amount' => 0,
                'commission_rate' => 0,
                'commission_type' => 'none',
                'rule_id' => null,
                'rule_name' => 'No Rule Applied',
                'rule_type' => null,
            ];
        }

        // Calculate commission
        $commissionAmount = $rule->calculateCommission($amount);

        // Record usage
        $rule->recordUsage();

        $commissionRate = $rule->commission_type === CommissionRule::COMMISSION_TYPE_PERCENTAGE
            ? $rule->commission_value
            : 0;

        return [
            'commission_amount' => $commissionAmount,
            'commission_rate' => $commissionRate,
            'commission_type' => $rule->commission_type,
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'rule_type' => $rule->rule_type,
            'distribution_config' => $rule->enable_distribution ? $rule->distribution_config : null,
        ];
    }

    /**
     * Prepare booking data for rule matching
     *
     * @param Booking|array $booking
     * @return array
     */
    protected function prepareBookingData($booking): array
    {
        if (is_array($booking)) {
            return $booking;
        }

        // Load relationships if not loaded
        if (!$booking->relationLoaded('hoarding')) {
            $booking->load('hoarding');
        }

        $hoarding = $booking->hoarding;
        $address = $hoarding->address ?? '';

        // Extract city and area from address (simple parsing)
        $addressParts = explode(',', $address);
        $city = trim($addressParts[count($addressParts) - 1] ?? '');
        $area = trim($addressParts[0] ?? '');

        return [
            'vendor_id' => $booking->vendor_id,
            'hoarding_id' => $booking->hoarding_id,
            'hoarding_type' => $hoarding->type ?? null,
            'city' => $city,
            'area' => $area,
            'amount' => $booking->total_amount,
            'duration_days' => $booking->duration_days,
            'booking_date' => $booking->created_at,
        ];
    }

    /**
     * Get all rules for a specific vendor
     *
     * @param int $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVendorRules(int $vendorId)
    {
        return CommissionRule::active()
            ->where(function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                  ->orWhere('rule_type', CommissionRule::RULE_TYPE_VENDOR);
            })
            ->byPriority()
            ->get();
    }

    /**
     * Get all rules for a specific hoarding
     *
     * @param int $hoardingId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHoardingRules(int $hoardingId)
    {
        return CommissionRule::active()
            ->where(function ($q) use ($hoardingId) {
                $q->where('hoarding_id', $hoardingId)
                  ->orWhere('rule_type', CommissionRule::RULE_TYPE_HOARDING);
            })
            ->byPriority()
            ->get();
    }

    /**
     * Get all seasonal offers currently active
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveSeasonalOffers()
    {
        return CommissionRule::active()
            ->seasonal()
            ->validNow()
            ->byPriority()
            ->get();
    }

    /**
     * Get rules statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_rules' => CommissionRule::count(),
            'active_rules' => CommissionRule::active()->count(),
            'inactive_rules' => CommissionRule::where('is_active', false)->count(),
            'vendor_rules' => CommissionRule::byType(CommissionRule::RULE_TYPE_VENDOR)->count(),
            'hoarding_rules' => CommissionRule::byType(CommissionRule::RULE_TYPE_HOARDING)->count(),
            'location_rules' => CommissionRule::byType(CommissionRule::RULE_TYPE_LOCATION)->count(),
            'seasonal_offers' => CommissionRule::seasonal()->count(),
            'active_seasonal' => CommissionRule::seasonal()->active()->validNow()->count(),
            'most_used_rule' => CommissionRule::orderBy('usage_count', 'desc')->first(),
        ];
    }

    /**
     * Preview commission calculation without saving
     *
     * @param array $bookingData
     * @param float $amount
     * @return array
     */
    public function previewCommission(array $bookingData, float $amount): array
    {
        $rule = $this->findBestRule($bookingData);

        if (!$rule) {
            return [
                'found_rule' => false,
                'message' => 'No matching commission rule found',
                'commission_amount' => 0,
                'commission_rate' => 0,
            ];
        }

        $commissionAmount = $rule->calculateCommission($amount);
        $commissionRate = $rule->commission_type === CommissionRule::COMMISSION_TYPE_PERCENTAGE
            ? $rule->commission_value
            : 0;

        return [
            'found_rule' => true,
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'rule_type' => $rule->rule_type,
            'commission_type' => $rule->commission_type,
            'commission_amount' => $commissionAmount,
            'commission_rate' => $commissionRate,
            'vendor_payout' => $amount - $commissionAmount,
            'summary' => $rule->summary,
        ];
    }

    /**
     * Validate rule configuration
     *
     * @param array $ruleData
     * @return array [valid: bool, errors: array]
     */
    public function validateRuleConfig(array $ruleData): array
    {
        $errors = [];

        // Validate tiered config if commission type is tiered
        if (($ruleData['commission_type'] ?? null) === CommissionRule::COMMISSION_TYPE_TIERED) {
            $tieredConfig = $ruleData['tiered_config'] ?? null;
            
            if (!$tieredConfig || !is_array($tieredConfig)) {
                $errors[] = 'Tiered configuration is required for tiered commission type';
            } else {
                foreach ($tieredConfig as $index => $tier) {
                    if (!isset($tier['min']) || !isset($tier['max']) || !isset($tier['rate'])) {
                        $errors[] = "Tier #{$index}: min, max, and rate are required";
                    }
                    if (($tier['min'] ?? 0) > ($tier['max'] ?? 0)) {
                        $errors[] = "Tier #{$index}: min value cannot be greater than max value";
                    }
                }
            }
        }

        // Validate distribution config if enabled
        if (($ruleData['enable_distribution'] ?? false)) {
            $distConfig = $ruleData['distribution_config'] ?? null;
            
            if (!$distConfig || !is_array($distConfig)) {
                $errors[] = 'Distribution configuration is required when distribution is enabled';
            } else {
                $total = array_sum($distConfig);
                if (abs($total - 100) > 0.01) {
                    $errors[] = "Distribution percentages must total 100% (current: {$total}%)";
                }
            }
        }

        // Validate date range
        if (isset($ruleData['valid_from']) && isset($ruleData['valid_to'])) {
            if ($ruleData['valid_from'] > $ruleData['valid_to']) {
                $errors[] = 'Valid from date cannot be after valid to date';
            }
        }

        // Validate amount range
        if (isset($ruleData['min_booking_amount']) && isset($ruleData['max_booking_amount'])) {
            if ($ruleData['min_booking_amount'] > $ruleData['max_booking_amount']) {
                $errors[] = 'Minimum booking amount cannot be greater than maximum';
            }
        }

        // Validate duration range
        if (isset($ruleData['min_duration_days']) && isset($ruleData['max_duration_days'])) {
            if ($ruleData['min_duration_days'] > $ruleData['max_duration_days']) {
                $errors[] = 'Minimum duration cannot be greater than maximum';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
