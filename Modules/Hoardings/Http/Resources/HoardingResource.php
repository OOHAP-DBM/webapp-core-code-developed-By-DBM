<?php

namespace Modules\Hoardings\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\HoardingHelper;


class HoardingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get media based on hoarding type
        $media = [];
        if ($this->hoarding_type === 'ooh' && $this->hoardingMedia) {
            $media = $this->hoardingMedia->map(function ($item) {
                return [
                    'id' => $item->id,
                    'url' => asset('storage/' . $item->file_path),
                    'type' => $item->media_type,
                    'is_primary' => (bool) $item->is_primary,
                ];
            });
        } elseif ($this->hoarding_type === 'dooh' && $this->doohScreen && $this->doohScreen->media) {
            $media = $this->doohScreen->media->map(function ($item) {
                return [
                    'id' => $item->id,
                    'url' => asset('storage/' . $item->file_path),
                    'type' => $item->media_type,
                    'is_primary' => (bool) $item->is_primary,
                ];
            });
        }

        // Get packages based on hoarding type
        $packages = [];
        if ($this->hoarding_type === 'ooh' && $this->ooh && $this->ooh->packages) {
            $packages = $this->ooh->packages->map(function ($pkg) {
                return [
                    'id' => $pkg->id,
                    'name' => $pkg->package_name,
                    'duration_months' => $pkg->duration_months,
                    'price' => (float) $pkg->price,
                    'discount_percent' => (float) ($pkg->discount_percent ?? 0),
                ];
            });
        } elseif ($this->hoarding_type === 'dooh' && $this->doohScreen && $this->doohScreen->packages) {
            $packages = $this->doohScreen->packages->map(function ($pkg) {
                return [
                    'id' => $pkg->id,
                    'name' => $pkg->package_name,
                    'duration_months' => $pkg->duration_months,
                    'price' => (float) $pkg->price,
                    'discount_percent' => (float) ($pkg->discount_percent ?? 0),
                ];
            });
        }
        $basePrice = 0;
        $sellPrice = 0;

   
        $basePrice = (float) ($this->base_monthly_price ?? $this->doohScreen->price_per_slot ?? 0);
        $sellPrice = (float) ($this->monthly_price??0);
        

        $discount = HoardingHelper::discountBeforeOffer($basePrice, $sellPrice);
        // Build pricing based on hoarding type
            $pricing = [
            'base_price'      => $discount['base_price'],
            'sell_price'      => $discount['sell_price'],
            'has_discount'    => $discount['has_discount'],
            'off_percentage'  => $discount['off_percentage'],
            'off_amount'      => $discount['off_amount'],

            // OOH extras
            'weekly' => $this->weekly_price_1 ? (float) $this->weekly_price_1 : null,
            'enable_weekly_booking' => (bool) $this->enable_weekly_booking,

            'slot_duration_seconds' => $this->hoarding_type === 'dooh'
                ? $this->doohScreen?->slot_duration_seconds
                : null,
        ];

        // Build DOOH technical specs
        $dooh_specs = null;
        if ($this->hoarding_type === 'dooh' && $this->doohScreen) {
            $dooh_specs = [
                'screen_type' => $this->doohScreen->screen_type,
                // 'resolution' => [
                //     'width' => $this->doohScreen->resolution_width,
                //     'height' => $this->doohScreen->resolution_height,
                // ],
                'screen_size' => [
                    'width' => $this->doohScreen->width,
                    'height' => $this->doohScreen->height,
                    'unit' => $this->doohScreen->measurement_unit,
                ],
                'slot_duration_seconds' => $this->doohScreen->slot_duration_seconds,
                'loop_duration_seconds' => $this->doohScreen->loop_duration_seconds,
                'screen_run_time' => $this->doohScreen->screen_run_time,
                'total_slots_per_day' => $this->doohScreen->total_slots_per_day,
                'loop_duration_seconds' => $this->doohScreen->loop_duration_seconds,
                'video_length' => $this->doohScreen->video_length,
                'allowed_formats' => $this->doohScreen->allowed_formats,
                'max_file_size_mb' => $this->doohScreen->max_file_size_mb,
              
            ];
        }

         // Build OOH technical specs
        $ooh_specs = null;
        if ($this->hoarding_type === 'ooh' && $this->ooh) {
            $ooh_specs = [
                'screen_size' => [
                    'width' => $this->ooh->width,
                    'height' => $this->ooh->height,
                    'unit' => $this->ooh->measurement_unit,
                ]
              
            ];
        }


        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'vendor' => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
                'email' => $this->vendor?->email,
                'phone' => $this->vendor?->phone,
            ],
            'title' => $this->title,
            'slug' => $this->slug,
            'name' => $this->name,

            'description' => $this->description,
            'hoarding_type' => $this->hoarding_type,
            'category' => $this->category,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'pincode' => $this->pincode,
            'locality' => $this->locality,
            'landmark' => $this->landmark,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'available_from' => $this->available_from?->toIso8601String(),
          
            'pricing' => $pricing,
            'dooh_specs' => $dooh_specs,
            'ooh_specs' => $ooh_specs,
            'status' => $this->status,
            'graphics_included' => (bool) $this->graphics_included,
            'graphics_price' => $this->graphics_price ? (float) $this->graphics_price : null,
            'hoarding_visibility' => $this->hoarding_visibility,
            'visibility_details' => $this->visibility_details,
            'located_at' => $this->located_at,
            'expected_footfall' => $this->expected_footfall,
            'expected_eyeball' => $this->expected_eyeball,
            'audience_types' => $this->audience_types,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => $this->isActive(),
            'media' => $media,
            'packages' => $packages,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
