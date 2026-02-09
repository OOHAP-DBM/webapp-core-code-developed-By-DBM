<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\HoardingHelper;

class WishlistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hoarding = $this->hoarding;

        if (! $hoarding) {
            return [
                'wishlist_id' => $this->id,
                'hoarding'    => null,
                'message'     => 'Hoarding not available',
                'created_at'  => $this->created_at?->format('Y-m-d'),
            ];
        }

        $basePrice = HoardingHelper::basePrice($hoarding);
        $sellPrice = (float) ($hoarding->monthly_price ?? 0);
        $discount  = HoardingHelper::discountBeforeOffer($basePrice, $sellPrice);

        return [
            'wishlist_id'    => $this->id,
            'hoarding_id'    => $hoarding->id,
            'title'          => $hoarding->title,
            'city'           => $hoarding->city,
            'hoarding_type'  => $hoarding->hoarding_type,
            'category'       => $hoarding->category,

            'base_price'     => $basePrice,
            'sell_price'     => $sellPrice,

            'has_discount'   => $discount['has_discount'],
            'off_percentage' => $discount['off_percentage'],
            'off_amount'     => $discount['off_amount'],

            'image_url'      => HoardingHelper::imageUrl($hoarding),
            'size'           => HoardingHelper::size($hoarding),
            'packages'       => HoardingHelper::activePackages($hoarding),

            'created_at'     => $this->created_at->format('Y-m-d'),
        ];
    }
}

