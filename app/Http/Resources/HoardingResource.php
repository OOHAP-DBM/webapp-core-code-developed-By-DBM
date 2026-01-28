<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        // Build pricing based on hoarding type
        $pricing = [];
       
            $pricing = [
                'base_monthly' => $this->base_monthly_price ? (float) $this->base_monthly_price : null,
                'monthly' => $this->monthly_price ? (float) $this->monthly_price : ($this->base_monthly_price ? (float) $this->base_monthly_price : null),
                'weekly' => $this->weekly_price_1 ? (float) $this->weekly_price_1 : null,
                'enable_weekly_booking' => (bool) $this->enable_weekly_booking,
                'weekly_price' => $this->weekly_price_1 ? (float) $this->weekly_price_1 : null,
                'weekly_price_2' => $this->weekly_price_2 ? (float) $this->weekly_price_2 : null,
                'weekly_price_3' => $this->weekly_price_3 ? (float) $this->weekly_price_3 : null,
            ];
         if ($this->hoarding_type === 'dooh' && $this->doohScreen) {
            $pricing = [
                // 'price_per_slot' => $this->doohScreen->price_per_slot ? (float) $this->doohScreen->price_per_slot : null,
                // 'price_per_30_sec' => $this->doohScreen->display_price_per_30s ? (float) $this->doohScreen->display_price_per_30s : null,
                // 'minimum_booking' => $this->doohScreen->minimum_booking_amount ? (float) $this->doohScreen->minimum_booking_amount : null,
                // 'base_monthly' => $this->base_monthly_price ? (float) $this->base_monthly_price : null,
                'price_per_slot' => $this->doohScreen->price_per_slot ? (float) $this->doohScreen->price_per_slot : null,
                'slot_duration_seconds' => $this->doohScreen->slot_duration_seconds,
                'loop_duration_seconds' => $this->doohScreen->loop_duration_seconds,
                // 'monthly' => $this->monthly_price ? (float) $this->monthly_price : null,

            ];
        }

        // Build DOOH technical specs
        $dooh_specs = null;
        if ($this->hoarding_type === 'dooh' && $this->doohScreen) {
            $dooh_specs = [
                'screen_type' => $this->doohScreen->screen_type,
                'resolution' => [
                    'width' => $this->doohScreen->resolution_width,
                    'height' => $this->doohScreen->resolution_height,
                ],
                'screen_size' => [
                    'width' => $this->doohScreen->width,
                    'height' => $this->doohScreen->height,
                    'unit' => $this->doohScreen->measurement_unit,
                ],
                'slot_duration_seconds' => $this->doohScreen->slot_duration_seconds,
                'loop_duration_seconds' => $this->doohScreen->loop_duration_seconds,
                'slots_per_loop' => $this->doohScreen->slots_per_loop,
                'total_slots_per_day' => $this->doohScreen->total_slots_per_day,
                'available_slots_per_day' => $this->doohScreen->available_slots_per_day,
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
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'email' => $this->vendor->email,
                'phone' => $this->vendor->phone,
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
