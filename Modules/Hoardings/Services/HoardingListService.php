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



        // Backend safety check for offer price
        $errors = [];
        if (isset($data['monthly_offer_price']) && isset($data['base_monthly_price'])) {
            if ($data['monthly_offer_price'] !== null && $data['monthly_offer_price'] >= $data['base_monthly_price']) {
                $errors['monthly_offer_price'][] = 'Offer price must be less than the base monthly price.';
            }
        }

        // Media validation (images only, no videos, max 5MB)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (empty($mediaFiles)) {
            $errors['media'][] = 'At least one image is required.';
        } else {
            foreach ($mediaFiles as $file) {
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    $errors['media'][] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
                    break;
                }
                if ($file->getSize() > $maxSize) {
                    $errors['media'][] = 'Each image must not exceed 5MB.';
                    break;
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
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
    // public function storeStep2($hoarding, $data, $brandLogoFiles = [])
    // {
    //     // 1. Map Form Fields to Database Columns
    //     $data['nagar_nigam_approved'] = $data['nagar_nigam_approved'] ?? 0;
    //     // Handle blocked_dates_json from form (JSON array of dates)
    //     if (!empty($data['blocked_dates_json'])) {
    //         $decoded = json_decode($data['blocked_dates_json'], true);
    //         if (is_array($decoded)) {
    //             $data['block_dates'] = $decoded;
    //         }
    //         unset($data['blocked_dates_json']);
    //     }
    //     if (isset($data['audience_types'])) {
    //         $data['audience_types'] = $data['audience_types']; // Ensure Model has array cast
    //     }
    //     return \DB::transaction(function () use ($hoarding, $data, $brandLogoFiles) {
    //         $hoarding = $this->repo->updateStep2($hoarding, $data);
    //         if (!empty($brandLogoFiles)) {
    //             $this->repo->storeBrandLogos($hoarding->id, $brandLogoFiles);
    //         }
    //         return ['success' => true,   'hoarding' => $hoarding->hoarding->fresh('brandLogos')];
    //     });
    // }

    /**
     * Process and store data for Step 2 of the Hoarding creation.
     * * @param  Hoarding  $hoarding
     * @param  array  $data
     * @param  array  $brandLogoFiles
     * @return array
     */
    public function storeStep2($hoarding, array $data, array $brandLogoFiles = []): array
    {

        // dd($data);
        // Always update the parent Hoarding model
        $parentHoarding = method_exists($hoarding, 'hoarding') && $hoarding->hoarding ? $hoarding->hoarding : $hoarding;
       \Log::info('Step2 parentHoarding', ['id' => $parentHoarding->id]);
        $childHoarding = $hoarding;
        \Log::info('Step2 childHoarding', ['id' => $childHoarding->id]);
        // 1. Data Transformation (Mapping form inputs to DB columns)
        $formattedData = $this->mapHoardingStep2Data($data);

        try {
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
        } catch (\Throwable $e) {
            throw new \Exception('Step 2 failed: ' . $e->getMessage(), 0, $e);
        }
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
            'visibility_start'    => is_array($data['visibility_start'] ?? null) ? ($data['visibility_start'][0] ?? null) : ($data['visibility_start'] ?? null),
            'visibility_end'      => is_array($data['visibility_end'] ?? null) ? ($data['visibility_end'][0] ?? null) : ($data['visibility_end'] ?? null),
            'facing_direction'    => $data['facing_direction'] ?? null,
            'road_type'           => $data['road_type'] ?? null,
            'traffic_type'        => $data['traffic_type'] ?? null,
            'visibility_details'   => $data['visible_from'] ?? null,
            'located_at'   => $data['located_at'] ?? null,

            // Step Management
            'current_step'         => 2,
        ];
        // \Log::info('Step2 mapped data', $mapped);
        return $mapped;
    }

    /**
     * Parse block dates from JSON or comma-separated string.
     */
    protected function parseBlockDates($input)
    {
        if (!$input) return null;
        // If already array, return as is
        if (is_array($input)) {
            return $input;
        }
        // Remove extra quotes if present
        if (is_string($input)) {
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
        return null;
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
     * Store Step 3 (Packages)
     */
    public function storeStep3($hoarding, $data)
    {
        // dd($data);
        return \DB::transaction(function () use ($hoarding, $data) {
            $hoarding = $this->repo->updateStep3($hoarding, $data);

            // Support offers_json (JSON string) from web or API, like DOOH
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
                $this->repo->storePackages($hoarding->id, $data);
            }

            // Set parent hoarding status to pending_approval
            $parent = $hoarding->hoarding;
            if ($parent && $parent->status !== \App\Models\Hoarding::STATUS_PENDING_APPROVAL) {
                $parent->status = \App\Models\Hoarding::STATUS_PENDING_APPROVAL;
                $parent->save();
                // Notify all admins
                $admins = \App\Models\User::role(['admin'])->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\NewHoardingPendingApprovalNotification($parent));
                }
            }
            return ['success' => true, 'hoarding' => $hoarding->fresh(['packages'])];
        });
    }
}
