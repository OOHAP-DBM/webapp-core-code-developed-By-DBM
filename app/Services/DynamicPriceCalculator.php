<?php

namespace App\Services;

use App\Models\Hoarding;
use Modules\DOOH\Models\DOOHPackage;
use Modules\Settings\Services\SettingsService;
use Carbon\Carbon;
use Exception;

/**
 * Dynamic Price Calculator for Customer Booking
 * 
 * Calculates booking prices with support for:
 * - Base pricing from hoarding rates
 * - Optional package-based pricing
 * - Vendor discounts
 * - GST/tax calculation
 * - Duration-based pricing
 */
class DynamicPriceCalculator
{
    protected SettingsService $settingsService;
    protected TaxService $taxService;

    public function __construct(SettingsService $settingsService, TaxService $taxService)
    {
        $this->settingsService = $settingsService;
        $this->taxService = $taxService;
    }

    /**
     * Calculate complete pricing breakdown for a booking
     *
     * @param int $hoardingId
     * @param string $bookingStart (Y-m-d format)
     * @param string $bookingEnd (Y-m-d format)
     * @param int|null $packageId Optional DOOH package ID
     * @param array $vendorDiscounts Optional vendor discounts ['type' => 'percent'|'fixed', 'value' => float]
     * @return array [
     *   'base_price' => float,
     *   'discount_applied' => float,
     *   'vendor_offer_applied' => array|null,
     *   'gst' => float,
     *   'final_price' => float,
     *   'breakdown' => array (detailed calculation steps)
     * ]
     * @throws Exception
     */
    public function calculate(
        int $hoardingId,
        string $bookingStart,
        string $bookingEnd,
        ?int $packageId = null,
        array $vendorDiscounts = []
    ): array {
        // Validate and parse dates
        $startDate = $this->parseAndValidateDate($bookingStart, 'start');
        $endDate = $this->parseAndValidateDate($bookingEnd, 'end');

        // Validate date range
        $this->validateDateRange($startDate, $endDate);

        // Get hoarding
        $hoarding = $this->getHoarding($hoardingId);

        // Calculate duration
        $duration = $this->calculateDuration($startDate, $endDate);

        // Calculate base price
        $basePrice = $this->calculateBasePrice($hoarding, $duration, $packageId);

        // Apply vendor discounts
        $discountResult = $this->applyVendorDiscounts($basePrice, $vendorDiscounts);
        $discountApplied = $discountResult['discount_amount'];
        $priceAfterDiscount = $discountResult['price_after_discount'];

        // Calculate GST
        $gstRate = $this->getGSTRate();
        $gstAmount = $this->calculateGST($priceAfterDiscount, $gstRate);

        // Calculate final price
        $finalPrice = $priceAfterDiscount + $gstAmount;

        // Build detailed breakdown
        $breakdown = $this->buildBreakdown(
            $hoarding,
            $duration,
            $basePrice,
            $discountResult,
            $gstRate,
            $gstAmount,
            $packageId
        );

        return [
            'base_price' => round($basePrice, 2),
            'discount_applied' => round($discountApplied, 2),
            'vendor_offer_applied' => $vendorDiscounts ? [
                'type' => $vendorDiscounts['type'] ?? null,
                'value' => $vendorDiscounts['value'] ?? 0,
                'amount' => round($discountApplied, 2)
            ] : null,
            'gst' => round($gstAmount, 2),
            'gst_rate' => $gstRate,
            'final_price' => round($finalPrice, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Validate and parse date string
     *
     * @param string $dateString
     * @param string $type ('start' or 'end')
     * @return Carbon
     * @throws Exception
     */
    protected function parseAndValidateDate(string $dateString, string $type = 'start'): Carbon
    {
        try {
            $date = Carbon::parse($dateString);
        } catch (\Exception $e) {
            throw new Exception("Invalid {$type} date format: {$dateString}");
        }
        
        // Validate start date is not in the past
        if ($type === 'start' && $date->lt(Carbon::today())) {
            throw new Exception('Start date cannot be in the past');
        }

        return $date;
    }

    /**
     * Validate date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @throws Exception
     */
    protected function validateDateRange(Carbon $startDate, Carbon $endDate): void
    {
        if ($endDate->lt($startDate)) {
            throw new Exception('End date must be after start date');
        }

        // Check maximum booking duration (e.g., 365 days)
        $maxDays = (int) $this->settingsService->get('max_booking_duration_days', 365);
        $durationDays = $startDate->diffInDays($endDate) + 1;

        if ($durationDays > $maxDays) {
            throw new Exception("Booking duration cannot exceed {$maxDays} days");
        }
    }

    /**
     * Get hoarding by ID
     *
     * @param int $hoardingId
     * @return Hoarding
     * @throws Exception
     */
    protected function getHoarding(int $hoardingId): Hoarding
    {
        $hoarding = Hoarding::where('id', $hoardingId)
            ->where('status', Hoarding::STATUS_ACTIVE)
            ->first();

        if (!$hoarding) {
            throw new Exception('Hoarding not found or not available for booking');
        }

        return $hoarding;
    }

    /**
     * Calculate booking duration
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array ['days' => int, 'weeks' => int, 'months' => int]
     */
    protected function calculateDuration(Carbon $startDate, Carbon $endDate): array
    {
        $days = $startDate->diffInDays($endDate) + 1; // Include both start and end dates
        $weeks = floor($days / 7);
        $months = $startDate->diffInMonths($endDate);
        $remainingDays = $days % 7;

        return [
            'days' => $days,
            'weeks' => (int) $weeks,
            'months' => $months,
            'remaining_days' => (int) $remainingDays,
        ];
    }

    /**
     * Calculate base price
     *
     * @param Hoarding $hoarding
     * @param array $duration
     * @param int|null $packageId
     * @return float
     * @throws Exception
     */
    protected function calculateBasePrice(Hoarding $hoarding, array $duration, ?int $packageId = null): float
    {
        // If package ID is provided, use DOOH package pricing
        if ($packageId) {
            return $this->calculatePackagePrice($packageId, $duration);
        }

        // Use hoarding's standard pricing
        return $this->calculateHoardingPrice($hoarding, $duration);
    }

    /**
     * Calculate price using DOOH package
     *
     * @param int $packageId
     * @param array $duration
     * @return float
     * @throws Exception
     */
    protected function calculatePackagePrice(int $packageId, array $duration): float
    {
        $package = DOOHPackage::where('id', $packageId)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            throw new Exception('Package not found or not available');
        }

        // Calculate based on months (DOOH packages are typically monthly)
        $months = max(1, ceil($duration['days'] / 30)); // Round up to nearest month

        // Validate against package constraints
        if ($months < $package->min_booking_months) {
            throw new Exception("Package requires minimum {$package->min_booking_months} months booking");
        }

        if ($months > $package->max_booking_months) {
            throw new Exception("Package allows maximum {$package->max_booking_months} months booking");
        }

        $basePrice = $package->price_per_month * $months;

        // Apply package discount if available
        if ($package->discount_percent > 0) {
            $discount = ($basePrice * $package->discount_percent) / 100;
            $basePrice -= $discount;
        }

        return $basePrice;
    }

    /**
     * Calculate price using hoarding rates
     *
     * @param Hoarding $hoarding
     * @param array $duration
     * @return float
     */
    protected function calculateHoardingPrice(Hoarding $hoarding, array $duration): float
    {
        $totalPrice = 0;

        // Strategy: Use the most economical pricing structure
        // 1. Try monthly pricing if available
        if ($duration['months'] > 0 && $hoarding->monthly_price) {
            $totalPrice += $hoarding->monthly_price * $duration['months'];
            
            // Calculate remaining days after full months
            $remainingDays = $duration['days'] - ($duration['months'] * 30);
            
            if ($remainingDays > 0) {
                // For remaining days, use daily rate derived from monthly price
                $dailyRate = $hoarding->monthly_price / 30;
                $totalPrice += $dailyRate * $remainingDays;
            }
        }
        // 2. Use weekly pricing if enabled and applicable
        elseif ($duration['weeks'] > 0 && $hoarding->enable_weekly_booking && $hoarding->weekly_price) {
            $totalPrice += $hoarding->weekly_price * $duration['weeks'];
            
            // Add remaining days at daily rate
            if ($duration['remaining_days'] > 0) {
                $dailyRate = $hoarding->weekly_price / 7;
                $totalPrice += $dailyRate * $duration['remaining_days'];
            }
        }
        // 3. Fall back to daily pricing
        else {
            // Derive daily rate from monthly price
            $dailyRate = $hoarding->monthly_price / 30;
            $totalPrice = $dailyRate * $duration['days'];
        }

        return $totalPrice;
    }

    /**
     * Apply vendor discounts
     *
     * @param float $basePrice
     * @param array $vendorDiscounts
     * @return array ['discount_amount' => float, 'price_after_discount' => float, 'details' => array]
     */
    protected function applyVendorDiscounts(float $basePrice, array $vendorDiscounts): array
    {
        if (empty($vendorDiscounts)) {
            return [
                'discount_amount' => 0,
                'price_after_discount' => $basePrice,
                'details' => null,
            ];
        }

        $discountType = $vendorDiscounts['type'] ?? 'fixed';
        $discountValue = (float) ($vendorDiscounts['value'] ?? 0);

        $discountAmount = 0;

        if ($discountType === 'percent') {
            // Percentage discount
            $discountAmount = ($basePrice * $discountValue) / 100;
        } elseif ($discountType === 'fixed') {
            // Fixed amount discount
            $discountAmount = min($discountValue, $basePrice); // Can't discount more than base price
        }

        $priceAfterDiscount = max(0, $basePrice - $discountAmount);

        return [
            'discount_amount' => $discountAmount,
            'price_after_discount' => $priceAfterDiscount,
            'details' => [
                'type' => $discountType,
                'value' => $discountValue,
                'applied_amount' => $discountAmount,
            ],
        ];
    }

    /**
     * Get GST rate from settings (backwards compatible)
     *
     * @return float
     */
    protected function getGSTRate(): float
    {
        return $this->taxService->getDefaultTaxRate('booking');
    }

    /**
     * Calculate GST amount using TaxService
     *
     * @param float $amount
     * @param float $gstRate (deprecated, kept for compatibility)
     * @return float
     */
    protected function calculateGST(float $amount, float $gstRate = null): float
    {
        $gstResult = $this->taxService->calculateGST($amount, [
            'applies_to' => 'booking',
        ]);
        return $gstResult['gst_amount'];
    }

    /**
     * Build detailed breakdown
     *
     * @param Hoarding $hoarding
     * @param array $duration
     * @param float $basePrice
     * @param array $discountResult
     * @param float $gstRate
     * @param float $gstAmount
     * @param int|null $packageId
     * @return array
     */
    protected function buildBreakdown(
        Hoarding $hoarding,
        array $duration,
        float $basePrice,
        array $discountResult,
        float $gstRate,
        float $gstAmount,
        ?int $packageId = null
    ): array {
        return [
            'hoarding' => [
                'id' => $hoarding->id,
                'title' => $hoarding->title,
                'location' => $hoarding->address,
                'monthly_price' => $hoarding->monthly_price,
                'weekly_price' => $hoarding->weekly_price,
                'weekly_booking_enabled' => $hoarding->enable_weekly_booking,
            ],
            'duration' => [
                'days' => $duration['days'],
                'weeks' => $duration['weeks'],
                'months' => $duration['months'],
                'remaining_days' => $duration['remaining_days'] ?? 0,
            ],
            'pricing' => [
                'base_price' => round($basePrice, 2),
                'discount' => [
                    'applied' => $discountResult['discount_amount'] > 0,
                    'type' => $discountResult['details']['type'] ?? null,
                    'value' => $discountResult['details']['value'] ?? 0,
                    'amount' => round($discountResult['discount_amount'], 2),
                ],
                'price_after_discount' => round($discountResult['price_after_discount'], 2),
                'gst' => [
                    'rate' => $gstRate,
                    'amount' => round($gstAmount, 2),
                ],
                'final_price' => round($discountResult['price_after_discount'] + $gstAmount, 2),
            ],
            'package_used' => $packageId !== null,
            'package_id' => $packageId,
            'calculated_at' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Quick price estimate (simplified version)
     *
     * @param int $hoardingId
     * @param int $durationDays
     * @return array ['estimated_price' => float, 'daily_rate' => float]
     * @throws Exception
     */
    public function quickEstimate(int $hoardingId, int $durationDays): array
    {
        $hoarding = $this->getHoarding($hoardingId);
        
        // Calculate daily rate from monthly price
        $dailyRate = $hoarding->monthly_price / 30;
        $basePrice = $dailyRate * $durationDays;
        
        // Add GST
        $gstRate = $this->getGSTRate();
        $gstAmount = $this->calculateGST($basePrice, $gstRate);
        $estimatedPrice = $basePrice + $gstAmount;

        return [
            'estimated_price' => round($estimatedPrice, 2),
            'daily_rate' => round($dailyRate, 2),
            'base_price' => round($basePrice, 2),
            'gst_amount' => round($gstAmount, 2),
            'gst_rate' => $gstRate,
        ];
    }

    /**
     * Compare prices with and without discount
     *
     * @param int $hoardingId
     * @param string $bookingStart
     * @param string $bookingEnd
     * @param array $vendorDiscounts
     * @return array ['without_discount' => array, 'with_discount' => array, 'savings' => float]
     * @throws Exception
     */
    public function compareWithDiscount(
        int $hoardingId,
        string $bookingStart,
        string $bookingEnd,
        array $vendorDiscounts
    ): array {
        // Calculate without discount
        $withoutDiscount = $this->calculate($hoardingId, $bookingStart, $bookingEnd, null, []);
        
        // Calculate with discount
        $withDiscount = $this->calculate($hoardingId, $bookingStart, $bookingEnd, null, $vendorDiscounts);
        
        // Calculate savings
        $savings = $withoutDiscount['final_price'] - $withDiscount['final_price'];

        return [
            'without_discount' => $withoutDiscount,
            'with_discount' => $withDiscount,
            'savings' => round($savings, 2),
            'savings_percent' => $withoutDiscount['final_price'] > 0 
                ? round(($savings / $withoutDiscount['final_price']) * 100, 2) 
                : 0,
        ];
    }
}
