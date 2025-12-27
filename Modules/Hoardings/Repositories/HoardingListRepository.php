<?php

namespace Modules\Hoardings\Repositories;

use Modules\Hoardings\Models\Hoarding;
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

        return Hoarding::create([
            'vendor_id'        => $vendor->id,
            'category'         => $data['category'],
            'hoarding_type'    => $data['hoarding_type'],
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
            'price_per_slot'   => $data['price_per_slot'],
            'status'           => Hoarding::STATUS_DRAFT,
            'current_step'     => 1,
        ]);
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

    public function updateStep2($hoarding, array $data)
    {
        $hoarding->fill($data);
        $hoarding->save();
        return $hoarding;
    }

    public function storeBrandLogos($hoardingId, array $logoFiles): array
    {
        $hoarding = \Modules\Hoardings\Models\Hoarding::findOrFail($hoardingId);
        $saved = [];
        foreach ($logoFiles as $index => $file) {
            $uuid = \Illuminate\Support\Str::uuid()->toString();
            $ext  = strtolower($file->getClientOriginalExtension());
            $directory = "hoardings/brand_logos/{$hoardingId}";
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
        $hoarding->fill($data);
        $hoarding->save();
        return $hoarding;
    }

    public function storePackages($hoardingId, array $data)
    {
        HoardingPackage::where('hoarding_id', $hoardingId)->delete();
        if (isset($data['offer_name']) && is_array($data['offer_name'])) {
            foreach ($data['offer_name'] as $index => $name) {
                if (!empty($name)) {
                    HoardingPackage::create([
                        'hoarding_id'         => $hoardingId,
                        'package_name'        => $name,
                        'min_booking_duration'=> $data['offer_duration'][$index] ?? 1,
                        'duration_unit'       => $data['offer_unit'][$index] ?? 'months',
                        'discount_percent'    => $data['offer_discount'][$index] ?? 0,
                        'is_active'           => true,
                        'price_per_month'     => $data['base_monthly_price'] ?? 0,
                        'slots_per_day'       => 1,
                    ]);
                }
            }
        }
    }
}
