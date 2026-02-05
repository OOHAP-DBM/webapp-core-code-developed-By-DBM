<?php

class PricingHelper
{
    public static function calculate(
        float $baseMonthlyPrice,
        int $months,
        ?float $discountPercent = null
    ): array {
        $baseTotal = $baseMonthlyPrice * $months;

        if ($discountPercent && $discountPercent > 0) {
            $final = self::calculateDiscountedPrice($baseTotal, $discountPercent);
            $discount = self::calculateOffPercentage($baseTotal, $final);
        } else {
            $final = $baseTotal;
            $discount = 0;
        }

        return [
            'base_total'       => round($baseTotal, 2),
            'final_price'      => round($final, 2),
            'discount_percent' => $discount,
        ];
    }

    public static function calculateOffPercentage(float $base, float $final): int
    {
        if ($base <= 0 || $final >= $base) {
            return 0;
        }

        return (int) round((($base - $final) / $base) * 100);
    }

    public static function calculateDiscountedPrice(float $price, float $discount): float
    {
        return round($price - (($price * $discount) / 100), 2);
    }
}
