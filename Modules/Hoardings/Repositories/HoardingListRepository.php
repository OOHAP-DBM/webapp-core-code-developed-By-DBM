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
            'monthly_price'   => $data['monthly_price'] ?? 0,
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
        // dd("hera");
        $hoarding->fill($data);
        try {
            $result = $hoarding->save();
        } catch (\Throwable $e) {
            \Log::error('Step2 updateStep2: save error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new \Exception('Failed to update hoarding: ' . $e->getMessage(), 0, $e);
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

    public function storeBrandLogos($childhoardingId, array $logoFiles): array
    {
        $hoarding = \Modules\Hoardings\Models\OOHHoarding::findOrFail($childhoardingId);
        $saved = [];
        foreach ($logoFiles as $index => $file) {
            $uuid = \Illuminate\Support\Str::uuid()->toString();
            $ext  = strtolower($file->getClientOriginalExtension());
            $directory = "oohHoardings/brand_logos/{$childhoardingId}";
            $filename  = "{$uuid}.{$ext}";
            $path = $file->storeAs($directory, $filename, 'public');
            $saved[] = $hoarding->oohBrandLogos()->create([
                'file_path'  => $path,
                'sort_order' => $index,
            ]);
        }
        return $saved;
    }

    // public function updateStep3($hoarding, array $data)
    // {
    //     // Move parent fields to parent hoarding if present
    //     $parent = $hoarding->hoarding;
    //     $parentChanged = false;
    //     // dd($parent);
    //     if (isset($data['survey_charge'])) {
    //         $parent->survey_charge = $data['survey_charge'];
    //         unset($data['survey_charge']);
    //         $parentChanged = true;
    //     }
    //     if (isset($data['graphics_included'])) {
    //         $parent->graphics_included = $data['graphics_included'];
    //         unset($data['graphics_included']);
    //         $parentChanged = true;
    //     }
    //     if (isset($data['graphics_charge'])) {
    //         $parent->graphics_charge = $data['graphics_charge'];
    //         unset($data['graphics_charge']);
    //         $parentChanged = true;
    //     }
    //     if (isset($data['weekly_price_1'])) {
    //         $parent->weekly_price_1 = $data['weekly_price_1'];
    //         $parentChanged = true;
    //     }
    //     if (isset($data['weekly_price_2'])) {
    //         $parent->weekly_price_2 = $data['weekly_price_2'];
    //         $parentChanged = true;
    //     }
    //     if (isset($data['weekly_price_3'])) {
    //         $parent->weekly_price_3 = $data['weekly_price_3'];
    //         $parentChanged = true;
    //     }
    //     if ($parentChanged) {
    //         $parent->save();
    //     }
    //     $hoarding->fill($data);
    //     $hoarding->save();
    //     return $hoarding;
    // }
   

    //for update step 3
    public function updateStep3($oohHoarding, array $data)
    {
       $offers = [];
            if (!empty($data['offers_json'])) {
                
                $offers = json_decode($data['offers_json'], true);
            }
            // If not using offers_json, fallback to old array fields
            if (empty($offers) && !empty($data['offer_name'])) {
                $count = is_array($data['offer_name']) ? count($data['offer_name']) : 0;
                for ($i = 0; $i < $count; $i++) {
                    $offers[] = [
                        'name' => $data['offer_name'][$i] ?? '',
                        'min_booking_duration' => $data['offer_duration'][$i] ?? 1,
                        'duration_unit' => $data['offer_unit'][$i] ?? 'months',
                        'discount' => $data['offer_discount'][$i] ?? 0,
                        'start_date' => $data['offer_start_date'][$i] ?? null,
                        'end_date' => $data['offer_end_date'][$i] ?? null,
                        'services' => $data['offer_services'][$i] ?? [],
                    ];
                }
            }
            if (!empty($offers)) {
                $data['offers'] = $offers;
                $this->storePackages($oohHoarding->id, $data);
            }
        // Get the parent hoarding
        $parent = $oohHoarding->hoarding;
        $parentChanged = false;

        // Move parent fields to parent hoarding if present
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
        if (isset($data['enable_weekly_booking'])) {
            $parent->enable_weekly_booking = $data['enable_weekly_booking'];
            unset($data['enable_weekly_booking']);
            $parentChanged = true;
        }
        if (isset($data['weekly_price_1'])) {
            $parent->weekly_price_1 = $data['weekly_price_1'];
            unset($data['weekly_price_1']);
            $parentChanged = true;
        }
        if (isset($data['weekly_price_2'])) {
            $parent->weekly_price_2 = $data['weekly_price_2'];
            unset($data['weekly_price_2']);
            $parentChanged = true;
        }
        if (isset($data['weekly_price_3'])) {
            $parent->weekly_price_3 = $data['weekly_price_3'];
            unset($data['weekly_price_3']);
            $parentChanged = true;
        }
        
        if ($parentChanged) {
            $parent->save();
        }

        // Update child OOHHoarding fields (printing, mounting, lighting, remounting)
        $oohHoarding->fill($data);
        $oohHoarding->save();
        
        return $oohHoarding;
    }

    public function storePackages($hoardingId, array $data)
    {
        // Find the child OOHHoarding for this parent hoarding
        $oohHoarding = \Modules\Hoardings\Models\OOHHoarding::where('id', $hoardingId)->first();
        // $childHoardingId = $oohHoarding ? $oohHoarding->id : null;
        // if (!$childHoardingId) {
        //     // fallback to parent if not found
        //     $childHoardingId = $hoardingId;
        // }
        $actualParentId = $oohHoarding->hoarding_id;

        // 3. Fetch the parent to get the vendor_id (Fixing the double 'find()->first()' syntax)
        $parent = \App\Models\Hoarding::findOrFail($actualParentId);
        $vendorId = $parent->vendor_id;

        $existingIds = [];
        if (isset($data['offers']) && is_array($data['offers'])) {
            foreach ($data['offers'] as $offer) {
                if (!empty($offer['name'])) {
                    if (!empty($offer['package_id'])) {
                        // Update existing
                        $pkg = HoardingPackage::where('id', $offer['package_id'])->where('hoarding_id', $actualParentId)->first();
                        if ($pkg) {
                            $pkg->update([
                                'package_name'         => $offer['name'],
                                'min_booking_duration' => $offer['duration'] ?? $offer['min_booking_duration'] ?? 1,
                                'duration_unit'        => $offer['unit'] ?? $offer['duration_unit'] ?? 'months',
                                'discount_percent'     => $offer['discount'] ?? $offer['discount_value'] ?? 0,
                                'start_date'           => $offer['start_date'] ?? null,
                                'end_date'             => $offer['end_date'] ?? null,
                                'is_active'            => $offer['is_active'] ?? true,
                                'services_included'    => $offer['services'] ?? [],
                            ]);
                            $existingIds[] = $pkg->id;
                        }
                    } else {
                        // Create new
                        $pkg = HoardingPackage::create([
                            'hoarding_id'           => $actualParentId,
                            'vendor_id'             => $parent->vendor_id,
                            'package_name'          => $offer['name'],
                            'min_booking_duration'  => $offer['duration'] ?? $offer['min_booking_duration'] ?? 1,
                            'duration_unit'         => $offer['unit'] ?? $offer['duration_unit'] ?? 'months',
                            'discount_percent'      => $offer['discount'] ?? $offer['discount_value'] ?? 0,
                            'start_date'            => $offer['start_date'] ?? null,
                            'end_date'              => $offer['end_date'] ?? null,
                            'is_active'             => $offer['is_active'] ?? true,
                            'services_included'     => $offer['services'] ?? [],
                        ]);
                        $existingIds[] = $pkg->id;
                    }
                }
            }
            // Delete removed packages
            HoardingPackage::where('hoarding_id', $actualParentId)
                ->whereNotIn('id', $existingIds)
                ->delete();
        } elseif (isset($data['offer_name']) && is_array($data['offer_name'])) {
            // Legacy: handle old array fields (no id support)
            HoardingPackage::where('hoarding_id', $actualParentId)->delete();
            foreach ($data['offer_name'] as $index => $name) {
                if (!empty($name)) {
                    HoardingPackage::create([
                        'hoarding_id'         => $actualParentId,
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