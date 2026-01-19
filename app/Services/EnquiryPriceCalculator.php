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

            $screen = $item->hoarding->doohScreen;

            if (!$screen) {
                return 0;
            }

            $pricePer10Sec = (float) ($screen->price_per_10_sec_slot ?? 0);

            $videoDuration = (int) ($meta['dooh_specs']['video_duration'] ?? 10);
            $slotsPerDay   = (int) ($meta['dooh_specs']['slots_per_day'] ?? 1);
            $totalDays     = (int) ($meta['dooh_specs']['total_days'] ?? 1);

            // 🔥 Core DOOH Formula
            $pricePerSecond = $pricePer10Sec / 10;
            $pricePerPlay   = $pricePerSecond * $videoDuration;
            $perDayPrice    = $pricePerPlay * $slotsPerDay;
            $basePrice      = $perDayPrice * $totalDays;

            // ✅ DOOH WITHOUT PACKAGE
            if (empty($item->package_id)) {
                return round($basePrice, 2);
            }

            // ✅ DOOH WITH PACKAGE
            $discountPercent = (float) ($item->package->discount_percent ?? 0);
            $discount        = $basePrice * ($discountPercent / 100);

            return round($basePrice - $discount, 2);
        }

        return 0;
    }
}
