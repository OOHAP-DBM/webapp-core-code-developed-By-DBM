<?php

namespace Modules\Hoardings\Services;

use Modules\Hoardings\Repositories\HoardingListRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
        // Backend safety check for offer price
        $errors = [];
        if (isset($data['monthly_offer_price']) && isset($data['base_monthly_price'])) {
            if ($data['monthly_offer_price'] !== null && $data['monthly_offer_price'] > $data['base_monthly_price']) {
                $errors['monthly_offer_price'][] = 'Monthly discounted price must be less than or equal to the base monthly price.';
            }
        }

        // Media validation (images + 1 video, max 10MB)
        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $allowedVideoMimes = ['video/mp4', 'video/webm'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        $videoCount = 0;

        if (empty($mediaFiles)) {
            $errors['media'][] = 'At least one image or video is required.';
        } else {
            foreach ($mediaFiles as $file) {
                $mime = $file->getMimeType();

                if (in_array($mime, $allowedVideoMimes)) {
                    $videoCount++;
                    if ($videoCount > 1) {
                        $errors['media'][] = 'Only 1 video is allowed.';
                        break;
                    }
                } elseif (!in_array($mime, $allowedImageMimes)) {
                    $errors['media'][] = 'Only JPG, PNG, WEBP images and MP4, WEBM videos are allowed.';
                    break;
                }

                if ($file->getSize() > $maxSize) {
                    $errors['media'][] = 'Each file must not exceed 10MB.';
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
        $parentHoarding = method_exists($hoarding, 'hoarding') && $hoarding->hoarding
            ? $hoarding->hoarding
            : $hoarding;

        $formattedData = $this->mapHoardingStep2Data($data);

        // ✅ Extract delete IDs BEFORE passing to repo — don't let fill() see it
        $deleteBrandLogosRaw = $data['delete_brand_logos'] ?? null;
        unset($formattedData['deleted_brand_logos']); // ✅ remove from fill() data

        try {
            return \DB::transaction(function () use ($parentHoarding, $formattedData, $brandLogoFiles, $deleteBrandLogosRaw) {

                // 1. Persist main hoarding data
                $updatedHoarding = $this->repo->updateStep2($parentHoarding, $formattedData);

                // 2. Delete removed brand logos via repo
                if (!empty($deleteBrandLogosRaw)) {
                    $this->repo->deleteBrandLogos($parentHoarding, $deleteBrandLogosRaw);
                }

                // 3. Store new brand logos
                if (!empty($brandLogoFiles)) {
                    $this->repo->storeBrandLogos($parentHoarding->id, $brandLogoFiles);
                }

                return [
                    'success'  => true,
                    'message'  => 'Hoarding details updated successfully.',
                    'hoarding' => $updatedHoarding->fresh('brandLogos'),
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
        // dd($data);
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
            'deleted_brand_logos' => $data['delete_brand_logos'] ?? null,

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
    // public function storeStep3($hoarding, $data)
    // {
    //     return \DB::transaction(function () use ($hoarding, $data) {
    //         $hoarding = $this->repo->updateStep3($hoarding, $data);
    //         // Support offers_json (JSON string) from web or API, like DOOH
    //         // $offers = [];
    //         // if (!empty($data['offers_json'])) {
    //         //     $offers = json_decode($data['offers_json'], true);
    //         // }
    //         // // If not using offers_json, fallback to old array fields
    //         // if (empty($offers) && !empty($data['offer_name'])) {
    //         //     $count = is_array($data['offer_name']) ? count($data['offer_name']) : 0;
    //         //     for ($i = 0; $i < $count; $i++) {
    //         //         $offers[] = [
    //         //             'name' => $data['offer_name'][$i] ?? '',
    //         //             'min_booking_duration' => $data['offer_duration'][$i] ?? 1,
    //         //             'duration_unit' => $data['offer_unit'][$i] ?? 'months',
    //         //             'discount' => $data['offer_discount'][$i] ?? 0,
    //         //             'start_date' => $data['offer_start_date'][$i] ?? null,
    //         //             'end_date' => $data['offer_end_date'][$i] ?? null,
    //         //             'services' => $data['offer_services'][$i] ?? [],
    //         //         ];
    //         //     }
    //         // }

    //         // if (!empty($offers)) {
    //         //     $data['offers'] = $offers;
    //         //     $this->repo->storePackages($hoarding->id, $data);
    //         // }

    //         // Set parent hoarding status based on env
    //         $parent = $hoarding->hoarding;
    //         $autoApproval = \App\Models\Setting::get('auto_hoarding_approval', false);
    //         $newStatus = $autoApproval ? \App\Models\Hoarding::STATUS_ACTIVE : \App\Models\Hoarding::STATUS_PENDING_APPROVAL;
    //         if ($parent && $parent->status !== $newStatus) {
    //             $parent->status = $newStatus;
    //             $parent->save();
    //             // Notify all admins
    //             $admins = \App\Models\User::role(['admin'])->get();
    //             foreach ($admins as $admin) {
    //                 $admin->notify(new \App\Notifications\NewHoardingPendingApprovalNotification($parent));
    //             }
    //             // Notify vendor (in-app and email)
    //             $vendor = $parent->vendor;
    //             if ($vendor) {
    //                 $statusText = $newStatus === \App\Models\Hoarding::STATUS_ACTIVE ? 'Your OOH hoarding is now active and published.' : 'Your OOH hoarding is pending approval.';
    //                 $vendor->notify(new \App\Notifications\NewHoardingPendingApprovalNotification($parent));
    //                 // $vendor->sendVendorEmails(new \Modules\Mail\HoardingStatusMail($parent, $statusText));
    //                 if ($autoApproval) {
    //                     $vendor->sendVendorEmails(new \Modules\Mail\HoardingPublishedMail($parent));
    //                 }
    //                 if ($vendor->fcm_token) {
    //                     $sent = send(
    //                         $vendor->fcm_token,
    //                         'OOH Hoarding Status',
    //                         $statusText,
    //                         [
    //                             'hoarding_id' => $parent->id,
    //                             'status' => $newStatus,
    //                             'type' => 'vendor_hoarding'
    //                         ]
    //                     );

    //                     if (!$sent) {
    //                         \Log::warning("FCM push notification failed for vendor ID {$vendor->id}");
    //                     }
    //                 }
    //             }
    //         }
    //         return ['success' => true, 'hoarding' => $hoarding->fresh(['packages'])];
    //     });
    // }
    public function storeStep3($hoarding, $data)
    {
        return \DB::transaction(function () use ($hoarding, $data) {
            $hoarding = $this->repo->updateStep3($hoarding, $data);

            // Set parent hoarding status based on env
            $parent = $hoarding->hoarding;
            $autoApproval = \App\Models\Setting::get('auto_hoarding_approval', false);
            $newStatus = $autoApproval ? \App\Models\Hoarding::STATUS_ACTIVE : \App\Models\Hoarding::STATUS_PENDING_APPROVAL;

            $parentStatusChanged = false;
            if ($parent && $parent->status !== $newStatus) {
                $parent->status = $newStatus;
                $parent->save();
                $parentStatusChanged = true;

                // Notify all admins
                $admins = \App\Models\User::role(['admin'])->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\NewHoardingPendingApprovalNotification($parent));
                }
            }

            // Notify vendor (in-app, email, and push)
            $vendor = $parent->vendor ?? null;
            if ($vendor && $vendor->fcm_token) {

                // Text depends on auto-approval
                $statusText = $newStatus === \App\Models\Hoarding::STATUS_ACTIVE
                    ? 'Your OOH hoarding is now active and published.'
                    : 'Your OOH hoarding is pending approval.';

                // In-app notification
                $vendor->notify(new \App\Notifications\NewHoardingPendingApprovalNotification($parent));

                // Email if auto-approved
                if ($autoApproval) {
                    $vendor->sendVendorEmails(new \Modules\Mail\HoardingPublishedMail($parent));
                }

                // ✅ Push notification for creation/update or auto-approve
                $sent = send(
                    $vendor->fcm_token,
                    'OOH Hoarding Status',
                    $statusText,
                    [
                        'hoarding_id' => $parent->id,
                        'status' => $newStatus,
                        'type' => 'vendor_hoarding'
                    ]
                );

                if (!$sent) {
                    \Log::warning("FCM push notification failed for vendor ID {$vendor->id}");
                }
            }

            return ['success' => true, 'hoarding' => $hoarding->fresh(['packages'])];
        });
    }



    public function updateStep1($hoarding, $oohHoarding, $data, $mediaFiles)
    {
        $errors = [];
        if (isset($data['monthly_price']) && isset($data['base_monthly_price'])) {
            if ($data['monthly_price'] > $data['base_monthly_price']) {
                $errors['monthly_price'][] = 'Monthly discounted price must be less than or equal to the base monthly price.';
            }
        }

        if (!empty($mediaFiles)) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'video/mp4', 'video/webm'];
            $maxSize = 10 * 1024 * 1024;

            foreach ($mediaFiles as $index => $file) {
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    $errors['media'][] = "File #{$index}: Invalid format. Only JPEG, PNG, WEBP, MP4, WEBM allowed.";
                }
                if ($file->getSize() > $maxSize) {
                    $errors['media'][] = "File #{$index}: Exceeds 10MB size limit.";
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($hoarding, $oohHoarding, $data, $mediaFiles) {
            // Update parent hoarding
            $hoarding->update([
                'category' => $data['category'] ?? $hoarding->category,
                'address' => $data['address'] ?? $hoarding->address,
                'locality' => $data['locality'] ?? $hoarding->locality,
                'city' => $data['city'] ?? $hoarding->city,
                'state' => $data['state'] ?? $hoarding->state,
                'pincode' => $data['pincode'] ?? $hoarding->pincode,
                'lat' => $data['lat'] ?? $hoarding->lat,
                'lng' => $data['lng'] ?? $hoarding->lng,
                'landmark' => $data['landmark'] ?? $hoarding->landmark,
                'monthly_price' => $data['monthly_price'] ?? $hoarding->monthly_price,
                'base_monthly_price' => $data['base_monthly_price'] ?? $hoarding->base_monthly_price,
            ]);

            // Update OOH-specific fields
            $width = floatval($data['width']);
            $height = floatval($data['height']);
            $measurement_unit = $data['measurement_unit'] ?? 'sqft';
            $areaSqft = $measurement_unit === 'sqm'
                ? round($width * $height * 10.7639, 2)
                : round($width * $height, 2);

            $oohHoarding->update([
                'width' => $width,
                'height' => $height,
                'measurement_unit' => $measurement_unit,
                // 'calculated_area_sqft' => $areaSqft,
            ]);


            if (!empty($data['deleted_media_ids'])) {
                $this->repo->deleteMedia($hoarding->id, $data['deleted_media_ids']);
            }

            // ✅ Store new media via repo
            if (!empty($mediaFiles)) {
                $this->repo->storeMedia($hoarding->id, $mediaFiles);
            }

            return ['success' => true, 'hoarding' => $hoarding->fresh('media')];
        });
    }
}
