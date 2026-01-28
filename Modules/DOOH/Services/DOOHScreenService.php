<?php

namespace Modules\DOOH\Services;

use Modules\DOOH\Repositories\DOOHScreenRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use \Modules\DOOH\Models\DOOHScreen;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Notifications\NewHoardingPendingApprovalNotification;


class DOOHScreenService
    /**
     * Get DOOH hoarding listing for API (step1, step2, step3 fields)
     */

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
            // 'price_per_30_sec_slot'    => 'required|numeric|min:1',
            'resolution_type' => 'required|string',
            'resolution_width' => 'required_if:resolution_type,custom|nullable|integer|min:1',
            'resolution_height' => 'required_if:resolution_type,custom|nullable|integer|min:1',
        ]);

        // Normalize mediaFiles to always be an array
        // Handle cases: array of files, single file, null, or empty
        if ($mediaFiles instanceof \Illuminate\Http\UploadedFile) {
            // Single file uploaded - wrap it in an array
            $mediaFiles = [$mediaFiles];
        } elseif (!is_array($mediaFiles)) {
            // Null or other non-array types
            $mediaFiles = [];
        }
        
        if ($validator->fails() || empty($mediaFiles)) {
            $errors = $validator->errors()->toArray();
            if (empty($mediaFiles)) {
                $errors['media'][] = 'At least one media file is required.';
            }
            throw new ValidationException($validator, response()->json(['errors' => $errors], 422));
        }

        return DB::transaction(function () use ($vendor, $data, $mediaFiles) {
            
            $screen = $this->repo->createStep1($vendor, $data);
            
            // Only store media if files are present
            if (!empty($mediaFiles) && is_array($mediaFiles)) {
                $this->repo->storeMedia($screen->id, $mediaFiles);
            }

            $screen->hoarding->current_step = 1;
            $screen->save();

            return ['success' => true, 'screen' => $screen->fresh('media')];
        });
    }

    protected function normalizeResolution(array $data): array
    {
        if ($data['resolution_type'] !== 'custom') {
            [$width, $height] = explode('x', $data['resolution_type']);
        } else {
            $width = $data['resolution_width'];
            $height = $data['resolution_height'];
        }

        return [
            'resolution_width'  => (int) $width,
            'resolution_height' => (int) $height,
            'aspect_ratio'      => round($width / $height, 2),
        ];
    }

    /**
     * Store Step 2 (Additional Settings, Visibility & Brand Logos)
     */
    // public function storeStep2($screen, array $data, array $brandLogoFiles = [])
    // {
    //     return DB::transaction(function () use ($screen, $data, $brandLogoFiles) {
    //         try {
    //             $update = [
    //                 'nagar_nigam_approved' => $data['nagar_nigam_approved'] ?? null,
    //                 'block_dates'          => $data['block_dates'] ?? null,
    //                 'grace_period'         => $data['grace_period'] ?? null,
    //                 'audience_types'       => $data['audience_types'] ?? null,
    //                 'visible_from'         => $data['visible_from'] ?? null,
    //                 'located_at'           => $data['located_at'] ?? null,
    //                 'hoarding_visibility'  => $data['hoarding_visibility'] ?? null,
    //                 'visibility_details'   => $data['visibility_details'] ?? null,
    //             ];

    //             $screen = $this->repo->updateStep2($screen, $update);

    //             if (!empty($brandLogoFiles)) {
    //                 $this->repo->storeBrandLogos($screen->id, $brandLogoFiles);
    //             }

    //             $screen->current_step = 2;
    //             $screen->save();

    //             return ['success' => true, 'screen' => $screen->fresh(['brandLogos'])];
    //         } catch (\Throwable $e) {
    //             Log::error('DOOH step2 failed', ['error' => $e->getMessage()]);
    //             return ['success' => false, 'errors' => ['step2' => ['Failed to save step 2 settings.']]];
    //         }
    //     });
    //     return DB::transaction(function () use ($screen, $data, $brandLogoFiles) {
    //         try {
    //         $update = [
    //             'nagar_nigam_approved' => isset($data['nagar_nigam_approved']),
    //             'grace_period'         => isset($data['grace_period']) ? (int)$data['grace_period'] : null,
    //             'block_dates'          => $data['block_dates'] ?? null,
    //             'audience_types'       => $data['audience_types'] ?? null,
    //             'visible_from'         => $data['visible_from'] ?? null,
    //             'located_at'           => $data['located_at'] ?? null,
    //             'hoarding_visibility'  => $data['hoarding_visibility'] ?? null,
    //             'visibility_details'   => $data['visibility_details'] ?? null,
    //         ];

    //         $screen = $this->repo->updateStep2($screen, $update);

    //         if (!empty($brandLogoFiles)) {
    //             $this->repo->storeBrandLogos($screen->id, $brandLogoFiles);
    //         }

    //         $screen->hoarding->current_step = 2;
    //         $screen->save();

    //         return [
    //             'success' => true,
    //             'screen'  => $screen->fresh('brandLogos')
    //         ];
    //         } catch (\Throwable $e) {
    //                     Log::error('DOOH step2 failed', ['error' => $e->getMessage()]);
    //                     return ['success' => false, 'errors' => ['step2' => ['Failed to save step 2 settings.']]];
    //                 }
    //         });
    // }

    public function storeStep2($screen, array $data, array $brandLogoFiles = []): array
    {

        // Always update the parent Hoarding model
        $parentHoarding = method_exists($screen, 'hoarding') && $screen->hoarding ? $screen->hoarding : $screen;
        $childHoarding = $screen;
        // 1. Data Transformation (Mapping form inputs to DB columns)
        $formattedData = $this->mapHoardingStep2Data($data);

        return \DB::transaction(function () use ($parentHoarding, $childHoarding, $formattedData, $brandLogoFiles) {
            // 2. Persist main hoarding data (parent)
            $updatedHoarding = $this->repo->updateStep2($parentHoarding, $formattedData);

            // 3. Handle Brand Logos via Repository (child)
            if (!empty($brandLogoFiles)) {
                $this->repo->storeBrandLogos($childHoarding->id, $brandLogoFiles);
            }

            return [
                'success'  => true,
                'message'  => 'Hoarding details updated successfully.',
                'hoarding' => $updatedHoarding->fresh('brandLogos')
            ];
        });
    }


    /**
     * Maps incoming request data to database-friendly formats.
     */
    protected function mapHoardingStep2Data(array $data): array
    {
        // \Log::info('Step2 incoming data', $data);
        $mapped = [
            // Legal
            'nagar_nigam_approved' => (bool) ($data['nagar_nigam_approved'] ?? false),
            'permit_number'        => $data['permit_number_hidden'] ?? $data['permit_number'] ?? null,
            'permit_valid_till'    => $data['permit_valid_till_hidden'] ?? $data['permit_valid_till'] ?? null,

            // Audience
            'expected_footfall'    => (int) ($data['expected_footfall'] ?? 0),
            'expected_eyeball'     => (int) ($data['expected_eyeball'] ?? 0),
            'audience_types'       => $data['audience_type'] ?? $data['audience_types'] ?? [],

            // Blocked Dates
            'block_dates' => $this->parseBlockDates($data['blocked_dates_json'] ?? null),

            // Grace Period
            'grace_period_days'    => isset($data['needs_grace_period']) && $data['needs_grace_period'] == '1'
                ? (int) $data['grace_period_days']
                : 0,

            // Visibility
            'hoarding_visibility'  => $data['visibility_type'] ?? $data['hoarding_visibility'] ?? null,
            'visibility_start' => $data['visibility_start'] ?? null,
            'visibility_end'   => $data['visibility_end'] ?? null,
            'facing_direction'    => $data['facing_direction'] ?? null,
            'road_type'           => $data['road_type'] ?? null,
            'traffic_type'        => $data['traffic_type'] ?? null,
            'visibility_details'   => $data['visible_from'] ?? null,
            'located_at'   => $data['located_at'] ?? null,
        
            'current_step'         => 2,
        ];
        // \Log::info('Step2 mapped data', $mapped);
        // dd($mapped);

        return $mapped;
    }

    /**
     * Parse block dates from JSON or comma-separated string.
     */
    protected function parseBlockDates($input)
    {
        if (!$input) return null;
        // Remove extra quotes if present
        $input = trim($input, '"');
        // Try JSON decode first
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        // Fallback: comma-separated string
        $arr = array_map('trim', explode(',', $input));
        return array_filter($arr);
    }
    /**
     * Helper to safely decode JSON fields.
     */
    protected function parseJsonField(?string $json)
    {
        if (!$json) return null;
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    }

    
    /**
     * Store Step 3 (Pricing, Slots, Campaigns, Services)
     */
    // public function storeStep3($screen, $data)
    // {
    //     return DB::transaction(function () use ($screen, $data) {
    //         try {
    //             // 1. Update the Screen (Hoarding) basic pricing info
    //             $graphicsIncluded = (int) ($data['graphics_included'] ?? 0);
    //             $graphicsPrice = ($graphicsIncluded === 1) ? 0 : ($data['graphics_price'] ?? 0);
    //             $update = [
    //                 'display_price_per_30s' => $data['display_price_per_30s'] ?? null,
    //                 'video_length'          => $data['video_length'] ?? null,
    //                 'base_monthly_price'    => $data['base_monthly_price'] ?? null,
    //                 'offer_discount'        => isset($data['has_offer_discount']) ? 1 : 0,
    //                 'graphics_included'     => $graphicsIncluded,
    //                 'graphics_price'        => $graphicsPrice,
    //                 'survey_charge'         => $data['survey_charge'] ?? null,
    //             ];

    //             // Use repo to update step 3 fields
    //             $screen = $this->repo->updateStep3($screen, $update);

    //             // 2. IMPORTANT: Store relational Packages (Using the model we built)
    //             // This replaces the 'formatCampaignPackages' JSON logic
    //             if (!empty($data['offer_name'])) {
    //                 $this->repo->storePackages($screen->id, $data);
    //             }

    //             // 3. Store relational Slots (If your UI has time-slot checkboxes)
    //             if (!empty($data['slots'])) {
    //                 $this->repo->storeSlots($screen->id, $data['slots']);
    //             }

    //             $screen->current_step = 3;
    //             $screen->status = 'active';
    //             $screen->save();

    //             return ['success' => true, 'screen' => $screen->fresh(['packages', 'slots'])];
    //         } catch (\Throwable $e) {
    //             \Log::error('DOOH step3 failed', ['error' => $e->getMessage()]);
    //             throw $e;
    //         }
    //     });
    // }
    public function storeStep3($screen, array $data)
    {
        return DB::transaction(function () use ($screen, $data) {
            // ---- DOOHScreen fields ----
            // $screenUpdate = [
            //     'price_per_slot' => $data['price_per_slot'] ?? null,
            //     'video_length'          => $data['video_length'] ?? null,
            // ];
            // $screen = $this->repo->updateStep3($screen, $screenUpdate);

            // ---- Hoarding fields ----
            $parentHoarding = $screen->hoarding;
            $parentHoarding->base_monthly_price = $data['base_monthly_price'] ?? 0;
            $parentHoarding->monthly_price = $data['monthly_offered_price'] ?? 0;
            // $parentHoarding->enable_weekly_booking = isset($data['enable_weekly_booking']) ? 1 : 0;
            // $parentHoarding->weekly_price_1 = $data['weekly_price_1'] ?? null;
            // $parentHoarding->weekly_price_2 = $data['weekly_price_2'] ?? null;
            // $parentHoarding->weekly_price_3 = $data['weekly_price_3'] ?? null;
            $parentHoarding->graphics_included = isset($data['graphics_included']) ? 1 : 0;
            $parentHoarding->graphics_charge = $data['graphics_charge'] ?? null;
            $parentHoarding->survey_charge = $data['survey_charge'] ?? null;
            $parentHoarding->hoarding_visibility = $data['hoarding_visibility'] ?? null;
            $parentHoarding->current_step = 3;
            $parentHoarding->status = 'pending_approval';
            $parentHoarding->save();
            // 🔔 Notify all admins (DOOH pending approval)
            try {
                $admins = User::role(['admin'])->get();

                foreach ($admins as $admin) {
                    $admin->notify(
                        new NewHoardingPendingApprovalNotification($parentHoarding)
                    );
                }

                Log::info('DOOH pending approval notification sent to admins', [
                    'hoarding_id' => $parentHoarding->id,
                    'screen_id'   => $screen->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('DOOH pending approval notification failed', [
                    'hoarding_id' => $parentHoarding->id,
                    'screen_id'   => $screen->id,
                    'error'       => $e->getMessage(),
                ]);
            }

            // ---- Slots ----
            if (!empty($data['slots'])) {
                $this->repo->storeSlots($screen->id, $data['slots']);
            }
            // ---- Campaign Packages ----
            // Support offers_json (JSON string) from web or API
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
                        'duration' => $data['offer_duration'][$i] ?? $data['duration'][$i] ?? '',
                        'unit' => $data['offer_unit'][$i] ?? '',
                        'discount' => $data['offer_discount'][$i] ?? '',
                        'end_date' => $data['offer_end_date'][$i] ?? '',
                        'services' => $data['offer_services'][$i] ?? [],
                    ];
                }
            }
            if (!empty($offers)) {
                $data['offers'] = $offers;
                $this->repo->storePackages($screen->id, $data);
            }

            return [
                'success' => true,
                'screen'  => $screen->fresh(['slots', 'packages'])
            ];
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


    public function getListing(array $filters = [])
    {
        $query = \Modules\DOOH\Models\DOOHScreen::with([
            'hoarding',
            'packages',
            'slots',
            'brandLogos',
            'media',
        ])->where('status', \Modules\DOOH\Models\DOOHScreen::STATUS_ACTIVE);

        if (!empty($filters['vendor_id'])) {
            $query->whereHas('hoarding', function($q) use ($filters) {
                $q->where('vendor_id', $filters['vendor_id']);
            });
        }
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $screens = $query->orderByDesc('created_at')->paginate(20);

        $data = $screens->map(function ($screen) {
            $hoarding = $screen->hoarding;
            return [
                'id' => $screen->id,
                'screen_type' => $screen->screen_type,
                'width' => $screen->width,
                'height' => $screen->height,
                'measurement_unit' => $screen->measurement_unit,
                'resolution_width' => $screen->resolution_width,
                'resolution_height' => $screen->resolution_height,
                'price_per_slot' => $screen->price_per_slot,
                'display_price_per_30s' => $screen->display_price_per_30s,
                'status' => $screen->status,
                'hoarding' => $hoarding ? [
                    'id' => $hoarding->id,
                    'title' => $hoarding->title,
                    'address' => $hoarding->address,
                    'city' => $hoarding->city,
                    'state' => $hoarding->state,
                    'pincode' => $hoarding->pincode,
                    'latitude' => $hoarding->latitude,
                    'longitude' => $hoarding->longitude,
                    'nagar_nigam_approved' => $hoarding->nagar_nigam_approved,
                    'permit_number' => $hoarding->permit_number,
                    'permit_valid_till' => $hoarding->permit_valid_till,
                    'block_dates' => $hoarding->block_dates,
                    'audience_types' => $hoarding->audience_types,
                    'hoarding_visibility' => $hoarding->hoarding_visibility,
                    'visibility_details' => $hoarding->visibility_details,
                ] : null,
                'packages' => $screen->packages->map(function ($pkg) {
                    return [
                        'id' => $pkg->id,
                        'package_name' => $pkg->package_name,
                        // 'price_per_month' => $pkg->price_per_month,
                        'discount_percent' => $pkg->discount_percent,
                        'services_included' => $pkg->services_included,
                    ];
                }),
                'slots' => $screen->slots->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'name' => $slot->name,
                        'from_time' => $slot->from_time,
                        'to_time' => $slot->to_time,
                        'active' => $slot->active,
                    ];
                }),
                'brand_logos' => $screen->brandLogos->map(function ($logo) {
                    return [
                        'id' => $logo->id,
                        'url' => asset('storage/' . $logo->file_path),
                        'sort_order' => $logo->sort_order,
                    ];
                }),
                'media' => $screen->media->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'url' => asset('storage/' . $m->file_path),
                        'type' => $m->media_type,
                    ];
                }),
            ];
        });

        return [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $screens->currentPage(),
                'last_page' => $screens->lastPage(),
                'per_page' => $screens->perPage(),
                'total' => $screens->total(),
            ],
        ];
    }
    /**
     * Update Step 1 (Basic Info & Media) - Preserves existing media
     */
    public function updateStep1($screen, $data, $mediaFiles)
    {
        $errors = [];

        // Media validation (only if new files provided)
        if (!empty($mediaFiles)) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            foreach ($mediaFiles as $index => $file) {
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    $errors['media'][] = "File #{$index}: Invalid format.";
                }
                if ($file->getSize() > $maxSize) {
                    $errors['media'][] = "File #{$index}: Exceeds 5MB.";
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($screen, $data, $mediaFiles) {
            $hoarding = $screen->hoarding;

            // Update parent hoarding
            $hoarding->update([
                'title' => $data['title'] ?? $hoarding->title,
                'category' => $data['category'] ?? $hoarding->category,
                'address' => $data['address'] ?? $hoarding->address,
                'locality' => $data['locality'] ?? $hoarding->locality,
                'city' => $data['city'] ?? $hoarding->city,
                'state' => $data['state'] ?? $hoarding->state,
                'pincode' => $data['pincode'] ?? $hoarding->pincode,
                'lat' => $data['lat'] ?? $hoarding->lat,
                'lng' => $data['lng'] ?? $hoarding->lng,
                'enable_weekly_booking' => isset($data['enable_weekly_booking']) ? $data['enable_weekly_booking'] : 0,
                'weekly_price_1' => $data['weekly_price_1'],
                'weekly_price_2' => $data['weekly_price_2'],
                'weekly_price_3' => $data['weekly_price_3'],
            ]);

            // Normalize resolution
            $normalized = $this->normalizeResolution($data);

            // Update DOOH screen
            $screen->update([
                'screen_type' => $data['screen_type'] ?? $screen->screen_type,
                'width' => $data['width'] ?? $screen->width,
                'height' => $data['height'] ?? $screen->height,
                'measurement_unit' => $data['measurement_unit'] ?? $screen->measurement_unit,
                'resolution_width' => $normalized['resolution_width'],
                'resolution_height' => $normalized['resolution_height'],
                'price_per_slot' => $data['price_per_slot'] ?? $screen->price_per_slot,
             
            ]);

            // Handle media - only add new
            if (!empty($mediaFiles)) {
                $this->repo->storeMedia($screen->id, $mediaFiles);
            }

            return ['success' => true, 'screen' => $screen->fresh('media')];
        });
    }

    /**
     * Update Step 3 - Package and pricing updates
     */
    public function updateStep3($screen, $data)
    {
        // dd($data);

        return DB::transaction(function () use ($screen, $data) {
            $hoarding = $screen->hoarding;

            // Update screen-level pricing
            // $screen->update([
            //     'price_per_slot' => $data['price_per_slot'] ?? $screen->price_per_slot,
            //     'video_length' => $data['video_length'] ?? $screen->video_length,
            //     // 'minimum_booking_amount' => $data['minimum_booking_amount'] ?? $screen->minimum_booking_amount,
          
            // ]);
            $hoarding->update([
                'graphics_included' => isset($data['graphics_included']),
                'graphics_price' => $data['graphics_price'] ?? 0,
            ]);

// dd($data);

            // Update or recreate packages if provided
            // if (!empty($data['offers_json'])) {
            //     $offers = json_decode($data['offers_json'], true);
            //     if (is_array($offers)) {
            //         // Delete existing packages
            //         $screen->packages()->delete();
            //         // Create new packages
            //         foreach ($offers as $offer) {

            //             $screen->packages()->create([
            //                 'package_name' => $offer['name'],
            //                 'duration' => $offer['duration'],
            //                 'duration_unit' => $offer['duration_unit'] ?? 'months',
            //                 'discount_percent' => $offer['discount'] ?? 0,
            //                 'slots_per_day'        => 1,
            //                 // 'price_per_month' => $offer['price'] ?? 0,
            //                'end_date'         => !empty($offer['end_date']) ? $offer['end_date'] : null,
            //                 'is_active' => 1,
            //             ]);
            //         }
            //     }
            // }
// dd($data);
            if (!empty($data['offers_json'])) {

                $offers = json_decode($data['offers_json'], true);

                if (is_array($offers)) {

                    foreach ($offers as $offer) {

                        // Safety check
                        if (empty($offer['name']) || empty($offer['duration'])) {
                            continue;
                        }

                        $payload = [
                            'package_name'     => $offer['name'],
                            'min_booking_duration'=> (int) $offer['duration'],   
                            'duration_unit'    => $offer['duration_unit'] ?? $offer['unit'] ?? 'months',
                            'discount_percent' => $offer['discount'] ?? 0,
                            'slots_per_day'    => 1,
                            'end_date'         => !empty($offer['end_date']) ? $offer['end_date'] : null,
                            'services_included' => isset($offer['services']) ? json_encode($offer['services']) : null,
                            'is_active'        => 1,
                        ];

                        // 🔁 UPDATE
                        if (!empty($offer['package_id'])) {

                            $screen->packages()
                                ->where('id', $offer['package_id'])
                                ->update($payload);

                        } 
                        // ➕ CREATE
                        else {

                            $screen->packages()->create($payload);

                        }
                    }
                }
            }


            // Update slots if provided
            if (!empty($data['slots'])) {
                // Implementation similar to storeSlots
                $this->repo->storeSlots($screen->id, $data['slots']);
            }

            return ['success' => true, 'screen' => $screen->fresh(['packages', 'slots'])];
        });
    }
}