<?php

namespace Modules\DOOH\Services;

use Modules\DOOH\Repositories\DOOHScreenRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DOOHScreenService
{
    protected $repo;

    public function __construct(DOOHScreenRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Store Step 1 (Basic Info & Media)
     */
    public function storeStep1($vendor, $data, $mediaFiles)
    {
        $validator = Validator::make($data, [
            'category'          => 'required|string|max:100',
            'screen_type'       => 'required|string|max:50',
            'width'             => 'required|numeric|min:0.1',
            'height'            => 'required|numeric|min:0.1',
            'measurement_unit'  => 'required|in:sqft,sqm',
            'address'           => 'required|string|max:255',
            'pincode'           => 'required|string|max:20',
            'locality'          => 'required|string|max:100',
            'price_per_slot'    => 'required|numeric|min:1',
        ]);

        if ($validator->fails() || empty($mediaFiles)) {
            $errors = $validator->errors()->toArray();
            if (empty($mediaFiles)) {
                $errors['media'][] = 'At least one media file is required.';
            }
            throw new ValidationException($validator, response()->json(['errors' => $errors], 422));
        }

        return DB::transaction(function () use ($vendor, $data, $mediaFiles) {
            $screen = $this->repo->createStep1($vendor, $data);
            $this->repo->storeMedia($screen->id, $mediaFiles);

            $screen->current_step = 1;
            $screen->save();

            return ['success' => true, 'screen' => $screen->fresh('media')];
        });
    }

    /**
     * Store Step 2 (Additional Settings, Visibility & Brand Logos)
     */
    public function storeStep2($screen, $data, $brandLogoFiles = [])
    {
        return DB::transaction(function () use ($screen, $data, $brandLogoFiles) {
            try {
                $update = [
                    'nagar_nigam_approved' => $data['nagar_nigam_approved'] ?? null,
                    'block_dates'          => $data['block_dates'] ?? null,
                    'grace_period'         => $data['grace_period'] ?? null,
                    'audience_types'       => $data['audience_types'] ?? null,
                    'visible_from'         => $data['visible_from'] ?? null,
                    'located_at'           => $data['located_at'] ?? null,
                    'hoarding_visibility'  => $data['hoarding_visibility'] ?? null,
                    'visibility_details'   => $data['visibility_details'] ?? null,
                ];

                $screen = $this->repo->updateStep2($screen, $update);

                if (!empty($brandLogoFiles)) {
                    $this->repo->storeBrandLogos($screen->id, $brandLogoFiles);
                }

                $screen->current_step = 2;
                $screen->save();

                return ['success' => true, 'screen' => $screen->fresh(['brandLogos'])];
            } catch (\Throwable $e) {
                Log::error('DOOH step2 failed', ['error' => $e->getMessage()]);
                return ['success' => false, 'errors' => ['step2' => ['Failed to save step 2 settings.']]];
            }
        });
    }

    /**
     * Store Step 3 (Pricing, Slots, Campaigns, Services)
     */
    // public function storeStep3($screen, $data)
    // {
    //     return DB::transaction(function () use ($screen, $data) {
    //         try {
    //             $update = [
    //                 'display_price_per_30s' => $data['display_price_per_30s'] ?? null,
    //                 'video_length'          => $data['video_length'] ?? null,
    //                 'base_monthly_price'    => $data['base_monthly_price'] ?? null,
    //                 'monthly_price'         => $data['monthly_price'] ?? null,
    //                 'weekly_price'          => $data['weekly_price'] ?? null,
    //                 'offer_discount'        => $data['offer_discount'] ?? null,
    //                 'long_term_offers'      => $data['long_term_offers'] ?? null, // Capturing Campaign Packages
    //                 'graphics_included'     => $data['graphics_included'] ?? null,
    //                 'graphics_price'        => $data['graphics_price'] ?? null,
    //                 'survey_charge'         => $data['survey_charge'] ?? null,
    //                 'services_included'     => $data['services_included'] ?? null,
    //             ];

    //             $screen = $this->repo->updateStep3($screen, $update);

    //             // Handle separate Slots table if not using JSON field
    //             if (!empty($data['slots'])) {
    //                 $this->repo->storeSlots($screen->id, $data['slots']);
    //             }

    //             // Handle separate Packages table if not using JSON field
    //             if (!empty($data['packages'])) {
    //                 $this->repo->storePackages($screen->id, $data['packages']);
    //             }

    //             $screen->current_step = 3;
    //             $screen->status = 'active'; // Optionally activate upon completion
    //             $screen->save();

    //             return ['success' => true, 'screen' => $screen->fresh(['slots', 'packages'])];
    //         } catch (\Throwable $e) {
    //             Log::error('DOOH step3 failed', ['error' => $e->getMessage()]);
    //             return ['success' => false, 'errors' => ['step3' => ['Failed to save pricing and package details.']]];
    //         }
    //     });
    // }
    /**
     * Store Step 3 (Pricing, Slots, Campaigns, Services)
     */
    public function storeStep3($screen, $data)
    {
        return DB::transaction(function () use ($screen, $data) {
            try {
                // 1. Update the Screen (Hoarding) basic pricing info
                $update = [
                    'display_price_per_30s' => $data['display_price_per_30s'] ?? null,
                    'video_length'          => $data['video_length'] ?? null,
                    'base_monthly_price'    => $data['base_monthly_price'] ?? null,
                    'offer_discount'        => isset($data['has_offer_discount']) ? 1 : 0,
                    'graphics_included'     => $data['graphics_included'] ?? 0,
                    'graphics_price'        => $data['graphics_price'] ?? null,
                    'survey_charge'         => $data['survey_charge'] ?? null,
                ];

                // Use repo to update step 3 fields
                $screen = $this->repo->updateStep3($screen, $update);

                // 2. IMPORTANT: Store relational Packages (Using the model we built)
                // This replaces the 'formatCampaignPackages' JSON logic
                if (!empty($data['offer_name'])) {
                    $this->repo->storePackages($screen->id, $data);
                }

                // 3. Store relational Slots (If your UI has time-slot checkboxes)
                if (!empty($data['slots'])) {
                    $this->repo->storeSlots($screen->id, $data['slots']);
                }

                $screen->current_step = 3;
                $screen->status = 'active';
                $screen->save();

                return ['success' => true, 'screen' => $screen->fresh(['packages', 'slots'])];
            } catch (\Throwable $e) {
                \Log::error('DOOH step3 failed', ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Helper to structure the dynamic package inputs into a clean array
     */
    private function formatCampaignPackages($data)
    {
        if (!isset($data['offer_name'])) return null;

        $packages = [];
        foreach ($data['offer_name'] as $index => $name) {
            if (!empty($name)) {
                $packages[] = [
                    'name'     => $name,
                    'duration' => $data['offer_duration'][$index] ?? 0,
                    'unit'     => $data['offer_unit'][$index] ?? 'months',
                    'discount' => $data['offer_discount'][$index] ?? 0, // This is where those "25", "15" go!
                ];
            }
        }
        return $packages;
    }
}
