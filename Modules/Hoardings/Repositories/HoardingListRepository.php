<?php

namespace Modules\Hoardings\Repositories;

use App\Models\Hoarding;
use Modules\Hoardings\Models\HoardingMedia;
use Modules\Hoardings\Models\HoardingPackage;
use Illuminate\Support\Str;

class HoardingListRepository
{
    public function createStep1($vendor, $data)
    {
        $width = floatval($data['width']);
        $height = floatval($data['height']);
        $measurement_unit = $data['measurement_unit'] ?? $data['unit'] ?? null;
        $areaSqft = $measurement_unit === 'sqm'
            ? round($width * $height * 10.7639, 2)
            : round($width * $height, 2);

        $hoarding = Hoarding::create([
            'vendor_id'        => $vendor->id,
            'category'         => $data['category'],
            // 'hoarding_type'    => $data['hoarding_type'],
            'width'            => $width,
            'height'           => $height,
            'measurement_unit' => $measurement_unit,
            'area_sqft'        => $areaSqft,
            'address'          => $data['address'],
            'pincode'          => $data['pincode'],
            'locality'         => $data['locality'],
            'city'             => $data['city'] ?? null,
            'state'            => $data['state'] ?? null,
            'latitude'              => $data['lat'] ?? null,
            'longitude'              => $data['lng'] ?? null,
            'base_monthly_price'   => $data['base_monthly_price'] ?? 0,
            'monthly_price'   => $data['monthly_price'] ?? null,
            'status'           => Hoarding::STATUS_DRAFT,
            'current_step'     => 1,
        ]);

        // Also create OOHHoarding record
        \Modules\Hoardings\Models\OOHHoarding::create([
            'hoarding_id' => $hoarding->id,
            'width' => $width,
            'height' => $height,
            'measurement_unit' => $measurement_unit,
        ]);

        return $hoarding;
    }

    public function storeMedia(int $hoardingId, array $mediaFiles): array
    {
        $hoarding = Hoarding::findOrFail($hoardingId);
        $savedMedia = [];
        foreach ($mediaFiles as $index => $file) {
            $uuid = Str::uuid()->toString();
            $ext  = strtolower($file->getClientOriginalExtension());
            $directory = "hoardings/media/{$hoardingId}";
            $filename  = "{$uuid}.{$ext}";
            $path = $file->storeAs($directory, $filename, 'public');
            $savedMedia[] = HoardingMedia::create([
                'hoarding_id' => $hoardingId,
                'file_path'   => $path,
                'media_type'  => $ext,
                'is_primary'  => $index === 0,
                'sort_order'  => $index,
            ]);
        }
        return $savedMedia;
    }

    /**
     * Update the hoarding instance with Step 2 data.
     *
     * @param  Hoarding  $hoarding
     * @param  array  $data
     * @return Hoarding
     */
    public function updateStep2($hoarding, array $data)
    {
        $hoarding->fill($data);
        try {
            $result = $hoarding->save();
        } catch (\Throwable $e) {
            \Log::error('Step2 updateStep2: save error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
        return $hoarding;
    }

    /**
     * Handle brand logo storage.
     */
    // public function storeBrandLogos(int $hoardingId, array $files): void
    // {
    //     foreach ($files as $file) {
    //         $path = $file->store("hoardings/{$hoardingId}/logos", 'public');

    //         // Assuming you have a related BrandLogo model
    //         // $this->brandLogoModel->create(['hoarding_id' => $hoardingId, 'path' => $path]);
    //     }
    // }

    public function storeBrandLogos($hoardingId, array $logoFiles): array
    {
        $hoarding = \App\Models\Hoarding::findOrFail($hoardingId);
        $saved = [];
        foreach ($logoFiles as $index => $file) {
            $uuid = \Illuminate\Support\Str::uuid()->toString();
            $ext  = strtolower($file->getClientOriginalExtension());
            $directory = "oohHoardings/brand_logos/{$hoardingId}";
            $filename  = "{$uuid}.{$ext}";
            $path = $file->storeAs($directory, $filename, 'public');
            $saved[] = $hoarding->brandLogos()->create([
                'file_path'  => $path,
                'sort_order' => $index,
            ]);
        }
        return $saved;
    }

    public function updateStep3($hoarding, array $data)
    {
        // Move parent fields to parent hoarding if present
        $parent = $hoarding->hoarding;
        $parentChanged = false;
        if (isset($data['survey_charge'])) {
            $parent->survey_charge = $data['survey_charge'];
            unset($data['survey_charge']);
            $parentChanged = true;
        }
        if (isset($data['graphics_included'])) {
            $parent->graphics_included = $data['graphics_included'];
            unset($data['graphics_included']);
            $parentChanged = true;
        }
        if (isset($data['graphics_charge'])) {
            $parent->graphics_charge = $data['graphics_charge'];
            unset($data['graphics_charge']);
            $parentChanged = true;
        }
        if (isset($data['weekly_price_1'])) {
            $parent->weekly_price_1 = $data['weekly_price_1'];
            $parentChanged = true;
        }
        if (isset($data['weekly_price_2'])) {
            $parent->weekly_price_2 = $data['weekly_price_2'];
            $parentChanged = true;
        }
        if (isset($data['weekly_price_3'])) {
            $parent->weekly_price_3 = $data['weekly_price_3'];
            $parentChanged = true;
        }
        if ($parentChanged) {
            $parent->save();
        }
        $hoarding->fill($data);
        $hoarding->save();
        return $hoarding;
    }

    public function storePackages($hoardingId, array $data)
    {
        // Find the child OOHHoarding for this parent hoarding
        $oohHoarding = \Modules\Hoardings\Models\OOHHoarding::where('hoarding_id', $hoardingId)->first();
        $childHoardingId = $oohHoarding ? $oohHoarding->id : null;
        if (!$childHoardingId) {
            // fallback to parent if not found
            $childHoardingId = $hoardingId;
        }
        HoardingPackage::where('hoarding_id', $childHoardingId)->delete();
        $parent = \App\Models\Hoarding::find($hoardingId);
        if (isset($data['offer_name']) && is_array($data['offer_name'])) {
            foreach ($data['offer_name'] as $index => $name) {
                if (!empty($name)) {
                    HoardingPackage::create([
                        'hoarding_id'         => $childHoardingId,
                        'vendor_id'           => $parent->vendor_id,
                        'package_name'        => $name,
                        'min_booking_duration'=> $data['offer_duration'][$index] ?? 1,
                        'duration_unit'       => $data['offer_unit'][$index] ?? 'months',
                        'discount_percent'    => $data['offer_discount'][$index] ?? 0,
                        'start_date'          => $data['offer_start_date'][$index] ?? null,
                        'end_date'            => $data['offer_end_date'][$index] ?? null,
                        'is_active'           => true,
                        'services_included'   => isset($data['offer_services'][$index]) ? (array)$data['offer_services'][$index] : [],
                    ]);
                }
            }
        }
    }
}
