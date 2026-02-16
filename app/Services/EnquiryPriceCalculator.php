<?php

namespace App\Services;

class EnquiryPriceCalculator
{
    /**
     * Calculate final price for an enquiry item
     *
     * @param  \Modules\Enquiries\Models\EnquiryItem  $item
     * @return float
     */
    public static function calculate($item): float
    {
        // Meta safe decode
        $meta = is_array($item->meta)
            ? $item->meta
            : json_decode($item->meta, true);

        /* =====================================================
         | OOH PRICING
         ===================================================== */
        if ($item->hoarding_type === 'ooh') {

            // ✅ OOH WITHOUT PACKAGE
            if (empty($item->package_id)) {

                $months = (int) ($meta['months'] ?? 1);
                $monthlyPrice = (float) ($item->hoarding->monthly_price ?? 0);

                return round($months * $monthlyPrice, 2);
            }

            // ✅ OOH WITH PACKAGE
            $package = $item->package;

            $baseMonthlyPrice = (float) ($item->hoarding->base_monthly_price ?? 0);
            $minMonths        = (int) ($package->min_booking_duration ?? 1);
            $discountPercent  = (float) ($package->discount_percent ?? 0);

            $basePrice = $baseMonthlyPrice * $minMonths;
            $discount  = $basePrice * ($discountPercent / 100);

            return round($basePrice - $discount, 2);
        }

        /* =====================================================
         | DOOH PRICING
         ===================================================== */
        if ($item->hoarding_type === 'dooh') {
            // If no package, use monthly_price or base_monthly_price (unified logic)
            if (empty($item->package_id)) {
                $months = (int) ($meta['months'] ?? 1);
                $monthlyPrice = (float) ($item->hoarding->monthly_price ?? 0);
                $baseMonthlyPrice = (float) ($item->hoarding->base_monthly_price ?? 0);
                $price = $monthlyPrice > 0 ? $monthlyPrice : $baseMonthlyPrice;
                return round($months * $price, 2);
            }

            // With package, use base_monthly_price, min_booking_duration, discount_percent
            $package = $item->package;
            $baseMonthlyPrice = (float) ($item->hoarding->base_monthly_price ?? 0);
            $minMonths        = (int) ($package->min_booking_duration ?? 1);
            $discountPercent  = (float) ($package->discount_percent ?? 0);
            $basePrice = $baseMonthlyPrice * $minMonths;
            $discount  = $basePrice * ($discountPercent / 100);
            return round($basePrice - $discount, 2);
        }

        return 0;
    }
}
