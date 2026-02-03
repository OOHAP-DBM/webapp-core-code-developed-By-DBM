<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class WishlistResource extends JsonResource
{
   public function toArray(Request $request): array
    {
        $hoarding = $this->hoarding;
        $basePrice = $this->resolveBasePrice($hoarding);
        
        // Use monthly_price as the selling price by default
        $sellPrice = (float) ($hoarding->monthly_price ?? 0);
        $hasDiscount = ($sellPrice > 0 && $sellPrice < $basePrice);
        $offAmount = $hasDiscount ? ($basePrice - $sellPrice) : 0;

        return [
            'wishlist_id'    => $this->id,
            'hoarding_id'    => $hoarding->id,
            'title'          => $hoarding->title,
            'city'           => $hoarding->city,
            'hoarding_type'  => $hoarding->hoarding_type,
            'category'       => $hoarding->category,
            'base_price'     => $basePrice,
            'sell_price'     => $sellPrice,
            'has_discount'   => $hasDiscount,
            'off_percentage' => $hasDiscount ? $this->calculateOffPercentage($basePrice, $sellPrice) : 0,
            'off_amount'     => round($offAmount, 2),
            'image_url'      => $this->getHoardingImage($hoarding),
            'size'           => $this->getHoardingSize($hoarding),
            'packages'       => $this->getPackages($hoarding),
            'created_at'     => $this->created_at->format('Y-m-d'),
        ];
    }

    /**
     * Helper: Calculate Percentage Off
     */
    private function calculateOffPercentage($base, $sell): int
    {
        if ($base <= 0 || $sell >= $base) return 0;
        return (int) round((($base - $sell) / $base) * 100);
    }

    private function resolveBasePrice($hoarding)
    {
        if ($hoarding->hoarding_type === 'dooh') {
            // Using relationship if loaded, fallback to DB query
            $screen = $hoarding->doohScreen ?? DB::table('dooh_screens')->where('hoarding_id', $hoarding->id)->first();
            return (float) ($screen ? ($screen->price_per_slot ?? 0) : 0);
        }
        return (float) ($hoarding->base_monthly_price ?? 0);
    }

    private function getHoardingImage($hoarding)
    {
        $path = null;
        if ($hoarding->hoarding_type === 'ooh') {
            $media = DB::table('hoarding_media')
                ->where('hoarding_id', $hoarding->id)
                ->orderByDesc('is_primary')->first();
            $path = $media?->file_path;
        } else {
            $screen = $hoarding->doohScreen ?? DB::table('dooh_screens')->where('hoarding_id', $hoarding->id)->first();
            if ($screen) {
                $media = DB::table('dooh_screen_media')->where('dooh_screen_id', $screen->id)->first();
                $path = $media?->file_path;
            }
        }
        return $path ? asset('storage/' . ltrim($path, '/')) : asset('assets/images/placeholder.jpg');
    }

    private function getHoardingSize($hoarding)
    {
        if ($hoarding->hoarding_type === 'ooh') {
            $spec = DB::table('ooh_hoardings')->where('hoarding_id', $hoarding->id)->first();
        } else {
            $spec = $hoarding->doohScreen ?? DB::table('dooh_screens')->where('hoarding_id', $hoarding->id)->first();
        }
        
        if (!$spec) return 'N/A';
        return ($spec->width && $spec->height) 
            ? "{$spec->width}×{$spec->height} " . ($spec->measurement_unit ?? '') 
            : 'N/A';
    }

    private function getPackages($hoarding)
    {
        if ($hoarding->hoarding_type === 'ooh') {
            return DB::table('hoarding_packages')
                ->where('hoarding_id', $hoarding->id)
                ->where('is_active', 1)
                ->get(['id', 'package_name', 'discount_percent', 'min_booking_duration', 'duration_unit']);
        }

        $screen = $hoarding->doohScreen ?? DB::table('dooh_screens')->where('hoarding_id', $hoarding->id)->first();
        if ($screen) {
            return DB::table('dooh_packages')
                ->where('dooh_screen_id', $screen->id)
                ->where('is_active', 1)
                ->get(['id', 'package_name', 'discount_percent', 'min_booking_duration', 'duration_unit']);
        }
        return [];
    }
}