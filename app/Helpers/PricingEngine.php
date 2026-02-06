<?php

namespace App\Helpers;

use Modules\Hoardings\Models\HoardingPackage;
use Modules\DOOH\Models\DOOHPackage;

class PricingEngine
{
    public static function resolve($item): array
    {
        $hoarding = $item->hoarding;

        $baseMonthly = (float) (
            $hoarding->base_monthly_price
            ?? $hoarding->doohScreen?->price_per_slot
            ?? 0
        );

        $sellMonthly = (float) ($hoarding->monthly_price ?? 0);

        $duration = DurationHelper::normalize($item->expected_duration);

        if (!$duration) {
            return self::empty();
        }

        // ================= PACKAGE LOGIC =================
        if ($item->package_id) {
            return self::packagePrice($item, $baseMonthly, $duration);
        }

        // ================= NO PACKAGE ====================
        if ($duration['unit'] === 'month') {
            return self::monthlyPrice($baseMonthly, $sellMonthly, $duration);
        }

        return self::weeklyPrice($hoarding, $baseMonthly, $sellMonthly, $duration);
    }

    /* ================================================= */
    /* ================= PACKAGE ======================= */
    /* ================================================= */

   protected static function packagePrice($item, float $baseMonthly, array $duration): array
    {
        $package = $item->hoarding_type === 'dooh'
            ? DOOHPackage::find($item->package_id)
            : HoardingPackage::find($item->package_id);

        if (!$package) {
            return self::empty();
        }

        $months = $duration['value'];
        $discountPercent = (float) $package->discount_percent;

        // ORIGINAL (no discount)
        $originalMonthly = $baseMonthly;
        $originalTotal   = round($originalMonthly * $months, 2);

        // DISCOUNTED
        $discountedMonthly = calculateDiscountedPrice(
            $baseMonthly,
            $discountPercent
        );

        $finalTotal = round($discountedMonthly * $months, 2);

        return [
            'type'               => 'package',
            'package_id'         => $package->id,
            'package_name'       => $package->name,

            // 👇 NEW (for offer UI)
            'original_monthly'   => display_price($originalMonthly),
            'original_price'     => display_price($originalTotal),

            'discount_percent'   => $discountPercent,
            'discounted_monthly' => display_price($discountedMonthly),
            'final_price'        => display_price($finalTotal),

            'duration'           => $duration,
        ];
    }


    /* ================================================= */
    /* ================= MONTHLY ======================= */
    /* ================================================= */

    protected static function monthlyPrice(float $base, float $sell, array $duration): array
{
    $months = $duration['value'];

    $originalMonthly = $base;
    $originalTotal   = round($base * $months, 2);

    $finalMonthly = $sell > 0 ? $sell : $base;
    $finalTotal   = round($finalMonthly * $months, 2);

    return [
        'type'             => 'monthly',

        //  original vs final
        'original_monthly' => display_price($originalMonthly),
        'original_price'   => display_price($originalTotal),

        'final_monthly'    => display_price($finalMonthly),
        'final_price'      => display_price($finalTotal),
        'discount_percent' => $sell > 0
            ? calculateOffPercentage($originalMonthly, $finalMonthly)
            : 0,
        'duration'         => $duration,
    ];
}


    /* ================================================= */
    /* ================= WEEKLY ======================== */
    /* ================================================= */

  protected static function weeklyPrice($hoarding, float $base, float $sell, array $duration): array
{
    $weeks = $duration['value'];

    $monthlyBase = $base;
    $monthlyUsed = $sell > 0 ? $sell : $base;

    $originalWeekly = round(($monthlyBase / 4) * $weeks, 2);

    $weeklyPrice = match ($weeks) {
        1 => $hoarding->weekly_price_1,
        2 => $hoarding->weekly_price_2,
        3 => $hoarding->weekly_price_3,
        default => 0,
    };

    if (!$weeklyPrice || $weeklyPrice <= 0) {
        $weeklyPrice = round(($monthlyUsed / 4) * $weeks, 2);
    }

    return [
        'type'             => 'weekly',
        'weeks'            => $weeks,

        // 👇 original vs final
        'original_price'   => display_price($originalWeekly),
        'final_price'      => display_price($weeklyPrice),

        'discount_percent' => calculateOffPercentage(
            $originalWeekly,
            $weeklyPrice
        ),
    ];
}

    protected static function empty(): array
    {
        return [
            'final_price' => null,
        ];
    }
}
