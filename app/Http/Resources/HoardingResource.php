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
        if ($this->hoarding_type === 'ooh') {
            $pricing = [
                'base_monthly' => $this->base_monthly_price ? (float) $this->base_monthly_price : null,
                'monthly' => $this->monthly_price ? (float) $this->monthly_price : ($this->base_monthly_price ? (float) $this->base_monthly_price : null),
                'weekly' => $this->weekly_price_1 ? (float) $this->weekly_price_1 : null,
                'enable_weekly_booking' => (bool) $this->enable_weekly_booking,
            ];
        } elseif ($this->hoarding_type === 'dooh' && $this->doohScreen) {
            $pricing = [
                'price_per_10_sec' => $this->doohScreen->price_per_10_sec_slot ? (float) $this->doohScreen->price_per_10_sec_slot : null,
                'price_per_30_sec' => $this->doohScreen->display_price_per_30s ? (float) $this->doohScreen->display_price_per_30s : null,
                // 'minimum_booking' => $this->doohScreen->minimum_booking_amount ? (float) $this->doohScreen->minimum_booking_amount : null,
                'base_monthly' => $this->base_monthly_price ? (float) $this->base_monthly_price : null,
                'price_per_slot' => $this->doohScreen->price_per_slot ? (float) $this->doohScreen->price_per_slot : null,
                'monthly' => $this->monthly_price ? (float) $this->monthly_price : null,

                'enable_weekly_booking' => (bool) $this->enable_weekly_booking,
              'weekly_price' => $this->weekly_price_1 ? (float) $this->weekly_price_1 : null,
                'weekly_price_2' => $this->weekly_price_2 ? (float) $this->weekly_price_2 : null,
                'weekly_price_3' => $this->weekly_price_3 ? (float) $this->weekly_price_3 : null,

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
                'graphics_included' => (bool) $this->doohScreen->graphics_included,
                'graphics_price' => $this->doohScreen->graphics_price ? (float) $this->doohScreen->graphics_price : null,
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
            'description' => $this->description,
            'hoarding_type' => $this->hoarding_type,
            'category' => $this->category,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'location' => [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ],
            'pricing' => $pricing,
            'dooh_specs' => $dooh_specs,
            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => $this->isActive(),
            'media' => $media,
            'packages' => $packages,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
