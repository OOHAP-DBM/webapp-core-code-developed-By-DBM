<?php

namespace Modules\Hoardings\Services;

use Modules\Hoardings\Repositories\HoardingListRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HoardingListService
{
    protected $repo;

    public function __construct(HoardingListRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Store Step 1 (Basic Info & Media)
     */
    public function storeStep1($vendor, $data, $mediaFiles)
    {
        // dd($data);

        $validator = Validator::make($data, [
            'category'          => 'required|string|max:100',
            // 'hoarding_type'     => 'required|string|max:50',
            'width'             => 'required|numeric|min:0.1',
            'height'            => 'required|numeric|min:0.1',
            'measurement_unit'  => 'required|in:sqft,sqm',
            'address'           => 'required|string|max:255',
            'pincode'           => 'required|string|max:20',
            'locality'          => 'required|string|max:100',
            // 'price_per_slot'    => 'required|numeric|min:1',
        ]);

        if ($validator->fails() || empty($mediaFiles)) {
            $errors = $validator->errors()->toArray();
            if (empty($mediaFiles)) {
                $errors['media'][] = 'At least one media file is required.';
            }
            throw new ValidationException($validator, response()->json(['errors' => $errors], 422));
        }

        return DB::transaction(function () use ($vendor, $data, $mediaFiles) {
            $hoarding = $this->repo->createStep1($vendor, $data);
            $this->repo->storeMedia($hoarding->id, $mediaFiles);

            $hoarding->current_step = 1;
            $hoarding->save();

            return ['success' => true, 'hoarding' => $hoarding->fresh('media')];
        });
    }

    /**
     * Store Step 2 (Additional Settings, Visibility & Brand Logos)
     */
    public function storeStep2($hoarding, $data, $brandLogoFiles = [])
    {
        return \DB::transaction(function () use ($hoarding, $data, $brandLogoFiles) {
            $hoarding = $this->repo->updateStep2($hoarding, $data);
            if (!empty($brandLogoFiles)) {
                $this->repo->storeBrandLogos($hoarding->id, $brandLogoFiles);
            }
            return ['success' => true, 'hoarding' => $hoarding->fresh(['brandLogos'])];
        });
    }

    /**
     * Store Step 3 (Packages)
     */
    public function storeStep3($hoarding, $data)
    {
        return \DB::transaction(function () use ($hoarding, $data) {
            $hoarding = $this->repo->updateStep3($hoarding, $data);
            if (!empty($data['offer_name'])) {
                $this->repo->storePackages($hoarding->id, $data);
            }
            return ['success' => true, 'hoarding' => $hoarding->fresh(['packages'])];
        });
    }
}
