<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class HoardingHelper
{
    public static function basePrice($hoarding): float
    {
        if ($hoarding->hoarding_type === 'dooh') {
            return (float) ($hoarding->doohScreen?->price_per_slot ?? 0);
        }

        return (float) ($hoarding->base_monthly_price ?? 0);
    }

    public static function discountBeforeOffer(float $base, float $sell): array
    {
            // Invalid base
        if ($base <= 0) {
            return [
                'sell_price'     => 0,
                'has_discount'   => false,
                'off_percentage' => 0,
                'off_amount'     => 0,
            ];
        }

        // Sell price not set
        if ($sell <= 0) {
            return [
                'sell_price'     => round($base, 2),
                'has_discount'   => false,
                'off_percentage' => 0,
                'off_amount'     => 0,
            ];
        }

        // No discount
        if ($sell >= $base) {
            return [
                'sell_price'     => round($sell, 2),
                'has_discount'   => false,
                'off_percentage' => 0,
                'off_amount'     => 0,
            ];
        }

        // VERY LOW sell price (< 1% of base)
        if ($sell < ($base * 0.01)) {
            $realOffPercentage = (($base - $sell) / $base) * 100;

            return [
                'sell_price'     => round($sell, 2),
                'has_discount'   => true,
                'off_percentage' => number_format($realOffPercentage, 2),
                'off_amount'     => round($base - $sell, 2),
            ];
        }

        // Normal discount
        return [
            'sell_price'     => round($sell, 2),
            'has_discount'   => true,
            'off_percentage' => number_format((($base - $sell) / $base) * 100, 2),
            'off_amount'     => round($base - $sell, 2),
        ];
    }


    public static function imageUrl($hoarding): string
    {
        if ($hoarding->hoarding_type === 'ooh') {
            $media = $hoarding->hoardingMedia
                ?->sortByDesc('is_primary')
                ->first();
        } else {
            $media = $hoarding->doohScreen?->media?->first();
        }

        return $media
            ? asset('storage/' . ltrim($media->file_path, '/'))
            : asset('assets/images/placeholder.jpg');
    }

    public static function size($hoarding): string
    {
        $spec = $hoarding->hoarding_type === 'ooh'
            ? $hoarding->ooh
            : $hoarding->doohScreen;

        return ($spec && $spec->width && $spec->height)
            ? "{$spec->width}×{$spec->height} {$spec->measurement_unit}"
            : 'N/A';
    }

    public static function activePackages($hoarding): Collection
    {
        if ($hoarding->hoarding_type === 'ooh') {
            return $hoarding->packages
                ?->where('is_active', 1)
                ->values() ?? collect();
        }

        return $hoarding->doohScreen?->packages
            ?->where('is_active', 1)
            ->values() ?? collect();
    }
}
