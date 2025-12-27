<?php
// Modules/DOOH/Repositories/DOOHScreenRepository.php

namespace Modules\DOOH\Repositories;

use Modules\DOOH\Models\DOOHScreen;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\DOOH\Models\DOOHScreenMedia;
use Modules\DOOH\Models\DOOHPackage;


class DOOHScreenRepository
{
    public function createStep1($vendor, $data)
    {
        $width = floatval($data['width']);
        $height = floatval($data['height']);
        $measurement_unit = $data['measurement_unit'] ?? $data['unit'] ?? null;
        $areaSqft = $measurement_unit === 'sqm'
            ? round($width * $height * 10.7639, 2)
            : round($width * $height, 2);

        return DOOHScreen::create([
            'vendor_id'        => $vendor->id,
            'category'         => $data['category'],
            'screen_type'      => $data['screen_type'],
            'width'            => $width,
            'height'           => $height,
            'measurement_unit' => $measurement_unit,
            'area_sqft'        => $areaSqft,

            'address'          => $data['address'],
            'pincode'          => $data['pincode'],
            'locality'         => $data['locality'],
            'city'             => $data['city'] ?? null,
            'state'            => $data['state'] ?? null,
            'lat'              => $data['lat'] ?? null,
            'lng'              => $data['lng'] ?? null,
            'price_per_slot'      => $data['price_per_slot'],
            'status'              => DOOHScreen::STATUS_DRAFT,
            'current_step'        => 1,
        ]);
    }

    /**
     * Store media (images/videos)
     */
    public function storeMedia(int $screenId, array $mediaFiles): array
    {
        $screen = DOOHScreen::findOrFail($screenId);

        [$shard1, $shard2] = $this->shardPath($screenId);

        $savedMedia = [];

        foreach ($mediaFiles as $index => $file) {
            $uuid = Str::uuid()->toString();
            $ext  = strtolower($file->getClientOriginalExtension());

            $directory = "dooh/screens/{$shard1}/{$shard2}/{$screenId}";
            $filename  = "{$uuid}.{$ext}";

            $path = $file->storeAs($directory, $filename, 'public');

            $savedMedia[] = DOOHScreenMedia::create([
                'dooh_screen_id' => $screenId,
                'file_path'      => $path,
                'media_type'     => in_array($ext, ['mp4', 'mov']) ? 'video' : 'image',
                'is_primary'     => $index === 0,
                'sort_order'     => $index,
            ]);
        }

        return $savedMedia;
    }

    /**
     * Delete media safely
     */
    public function deleteMedia(DOOHScreenMedia $media): void
    {
        Storage::disk('public')->delete($media->file_path);
        $media->delete();
    }

    /**
     * Folder sharding to avoid filesystem overload
     */
    private function shardPath(int $id): array
    {
        return [
            floor($id / 100) % 100,
            floor($id / 10) % 10,
        ];
    }


    /**
     * Update Step 3 fields for a screen
     */
    public function updateStep3($screen, array $data)
    {
        $screen->fill($data);
        $screen->save();
        return $screen;
    }

    /**
     * Store slots (array of [name, from_time, to_time])
     */
    public function storeSlots(int $screenId, array $slots): array
    {
        $screen = \Modules\DOOH\Models\DOOHScreen::findOrFail($screenId);

        // Delete existing slots to prevent duplicates on update
        $screen->slots()->delete();

        $saved = [];
        foreach ($slots as $details) {
            // Only save if the toggle was turned 'on' (active)
            if (isset($details['active']) && $details['active'] == '1') {
                $saved[] = \Modules\DOOH\Models\DOOHSlot::create([
                    'dooh_screen_id' => $screenId,
                    'slot_name'   => $details['name'],
                    'start_time'  => date("H:i:s", strtotime($details['start_time'])),
                    'end_time'    => date("H:i:s", strtotime($details['end_time'])),
                    'status'      => 'available',
                    'is_active'   => true,
                    // Add these defaults to satisfy the NOT NULL constraint immediately
                    'total_hourly_displays'  => 0,
                    'total_daily_displays'   => 0,
                    'interval_seconds'       => 0,
                    'hourly_cost'            => 0,
                    'daily_cost'             => 0,
                    'monthly_cost'           => 0,
                   
                ]);
            }
        }
        return $saved;
    }

    /**
     * Store campaign packages (array of package data)
     */
    /**
     * Store or Update Campaign Packages for a Screen
     * * @param int $screenId
     * @param array $data This is the request data containing offer_name, offer_duration, etc.
     */
    public function storePackages(int $screenId, array $data)
    {
        \Log::info('Package Data Received:', $data);
        // 1. Clear existing packages if you want a fresh sync, 
        // or use IDs to update specific ones. Usually, for this UI, fresh sync is easier.
        DOOHPackage::where('dooh_screen_id', $screenId)->delete();

        if (isset($data['offer_name']) && is_array($data['offer_name'])) {
            foreach ($data['offer_name'] as $index => $name) {
                // Only save if the name is not empty
                if (!empty($name)) {
                    DOOHPackage::create([
                        'dooh_screen_id'       => $screenId,
                        'package_name'         => $name,
                        'min_booking_duration' => $data['offer_duration'][$index] ?? 1,
                        'duration_unit'        => $data['offer_unit'][$index] ?? 'months',
                        'discount_percent'     => $data['offer_discount'][$index] ?? 0,
                        'is_active'            => true,
                        // 'package_type'         => 'campaign',
                        // Map other fields as needed
                        'price_per_month'      => $data['base_monthly_price'] ?? 0,
                        'slots_per_day'        => 1,
                    ]);
                }
            }
        }
    }
    /**
     * Update Step 2 fields for a screen
     */
    public function updateStep2($screen, array $data)
    {
        $screen->fill($data);
        $screen->save();
        return $screen;
    }

    /**
     * Store brand logos (images)
     */
    public function storeBrandLogos(int $screenId, array $logoFiles): array
    {
        $screen = \Modules\DOOH\Models\DOOHScreen::findOrFail($screenId);
        $saved = [];
        foreach ($logoFiles as $index => $file) {
            $uuid = \Illuminate\Support\Str::uuid()->toString();
            $ext  = strtolower($file->getClientOriginalExtension());
            $directory = "dooh/screens/brand_logos/{$screenId}";
            $filename  = "{$uuid}.{$ext}";
            $path = $file->storeAs($directory, $filename, 'public');
            $saved[] = $screen->brandLogos()->create([
                'file_path'  => $path,
                'sort_order' => $index,
            ]);
        }
        return $saved;
    }
}
