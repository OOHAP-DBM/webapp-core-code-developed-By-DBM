<?php

namespace Modules\Hoardings\Repositories;

use App\Models\Hoarding;
use Modules\Hoardings\Models\HoardingMedia;
use Modules\Hoardings\Models\HoardingPackage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class HoardingListRepository
{
    /**
     * Image sizes to generate for each uploaded image.
     * Key = size label, Value = max width in pixels.
     *
     * Usage:
     *   100  → thumbnail (admin lists, tiny previews)
     *   300  → grid view (search results, cards)
     *   600  → list view (wider cards)
     *   1000 → detail page
     *   1500 → full / zoom view
     */
    protected array $imageSizes = [100, 300, 600, 1000, 1500];

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPER — generate & store all sizes, return paths
    // ─────────────────────────────────────────────────────────────

    /**
     * Process one image file → generate 5 WebP sizes → save to disk.
     *
     * Folder structure:
     *   hoardings/media/{year}/{month}/{hoardingId}/{size}/{uuid}.webp
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  int  $hoardingId
     * @return array  ['path_100' => '...', 'path_300' => '...', ...]
     */
    private function processImage($file, int $hoardingId): array
    {
        $uuid  = Str::uuid()->toString();
        $year  = now()->format('Y');
        $month = now()->format('m');
        $base  = "hoardings/media/{$year}/{$month}/{$hoardingId}";

        $paths = [];

        foreach ($this->imageSizes as $size) {
            $image = Image::read($file)
                ->scaleDown(width: $size)   // aspect ratio maintain hoga, kabhi stretch nahi
                ->toWebp(quality: 82);      // WebP — best compression + quality

            $directory = "{$base}/{$size}";
            $filename  = "{$uuid}.webp";
            $path      = "{$directory}/{$filename}";

            Storage::disk('public')->put($path, $image);

            $paths["path_{$size}"] = $path;
        }

        return $paths;
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 1 — Create hoarding
    // ─────────────────────────────────────────────────────────────

    public function createStep1($vendor, $data)
    {
        $width            = floatval($data['width']);
        $height           = floatval($data['height']);
        $measurement_unit = $data['measurement_unit'] ?? $data['unit'] ?? null;
        $areaSqft         = $measurement_unit === 'sqm'
            ? round($width * $height * 10.7639, 2)
            : round($width * $height, 2);

        $hoarding = Hoarding::create([
            'vendor_id'          => $vendor->id,
            'category'           => $data['category'],
            'width'              => $width,
            'height'             => $height,
            'measurement_unit'   => $measurement_unit,
            'area_sqft'          => $areaSqft,
            'address'            => $data['address'],
            'pincode'            => $data['pincode'],
            'locality'           => $data['locality'],
            'city'               => $data['city']              ?? null,
            'state'              => $data['state']             ?? null,
            'latitude'           => $data['lat']               ?? null,
            'longitude'          => $data['lng']               ?? null,
            'base_monthly_price' => $data['base_monthly_price'] ?? 0,
            'monthly_price'      => $data['monthly_price']      ?? 0,
            'status'             => Hoarding::STATUS_DRAFT,
            'current_step'       => 1,
        ]);

        // Also create OOHHoarding record
        \Modules\Hoardings\Models\OOHHoarding::create([
            'hoarding_id'      => $hoarding->id,
            'width'            => $width,
            'height'           => $height,
            'measurement_unit' => $measurement_unit,
        ]);

        return $hoarding;
    }

    // ─────────────────────────────────────────────────────────────
    // STORE MEDIA — images get 5 sizes, videos stored as-is
    // ─────────────────────────────────────────────────────────────

    /**
     * Store media files for a hoarding.
     *
     * Images → 5 WebP sizes generated automatically.
     * Videos → stored as-is (no processing).
     *
     * @param  int    $hoardingId
     * @param  array  $mediaFiles   Array of UploadedFile instances
     * @return array  Saved HoardingMedia models
     */
    public function storeMedia(int $hoardingId, array $mediaFiles): array
    {
        Hoarding::findOrFail($hoardingId); // safety check

        $savedMedia = [];

        foreach ($mediaFiles as $index => $file) {
            $mimeType = $file->getMimeType();
            $isImage  = str_starts_with($mimeType, 'image/');
            $isVideo  = str_starts_with($mimeType, 'video/');

            if ($isImage) {
                // ── Generate 5 optimised WebP sizes ──────────────────
                $paths = $this->processImage($file, $hoardingId);

                $savedMedia[] = HoardingMedia::create([
                    'hoarding_id' => $hoardingId,
                    'file_path'   => $paths['path_1500'], // original / largest
                    'path_100'    => $paths['path_100'],  // thumbnail
                    'path_300'    => $paths['path_300'],  // grid view
                    'path_600'    => $paths['path_600'],  // list view
                    'path_1000'   => $paths['path_1000'], // detail page
                    'path_1500'   => $paths['path_1500'], // full / zoom
                    'mime_type'   => 'image/webp',
                    'media_type'  => 'image',
                    'is_primary'  => $index === 0,
                    'sort_order'  => $index,
                ]);

            } elseif ($isVideo) {
                // ── Store video as-is, no resizing ───────────────────
                $uuid      = Str::uuid()->toString();
                $ext       = strtolower($file->getClientOriginalExtension());
                $year      = now()->format('Y');
                $month     = now()->format('m');
                $directory = "hoardings/media/{$year}/{$month}/{$hoardingId}/videos";
                $path      = $file->storeAs($directory, "{$uuid}.{$ext}", 'public');

                $savedMedia[] = HoardingMedia::create([
                    'hoarding_id' => $hoardingId,
                    'file_path'   => $path,
                    'mime_type'   => $mimeType,
                    'media_type'  => 'video',
                    'is_primary'  => $index === 0,
                    'sort_order'  => $index,
                ]);
            }
            // unsupported types are silently skipped (frontend already validates)
        }

        return $savedMedia;
    }

    // ─────────────────────────────────────────────────────────────
    // DELETE MEDIA — removes all 5 sizes + video from disk + DB row
    // ─────────────────────────────────────────────────────────────

    /**
     * Delete media files by comma-separated ID string, scoped to a hoarding.
     * For images: deletes all 5 size variants from disk.
     * For videos: deletes the single file from disk.
     */
    public function deleteMedia(int $hoardingId, string $deletedIdsString): void
    {
        $deleteIds = array_filter(
            array_map('intval', explode(',', $deletedIdsString))
        );

        if (empty($deleteIds)) {
            return;
        }

        $mediaToDelete = HoardingMedia::whereIn('id', $deleteIds)
            ->where('hoarding_id', $hoardingId)
            ->get();

        foreach ($mediaToDelete as $media) {
            if ($media->media_type === 'image') {
                // ── Delete all 5 size variants ────────────────────
                $sizePaths = [
                    $media->path_100,
                    $media->path_300,
                    $media->path_600,
                    $media->path_1000,
                    $media->path_1500,
                    $media->file_path, // fallback / original
                ];

                foreach (array_unique(array_filter($sizePaths)) as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            } else {
                // ── Delete single video file ──────────────────────
                if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
                    Storage::disk('public')->delete($media->file_path);
                }
            }

            $media->delete();
        }

        \Log::info('Hoarding media deleted', [
            'hoarding_id' => $hoardingId,
            'deleted_ids' => $deleteIds,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 2 UPDATE
    // ─────────────────────────────────────────────────────────────

    /**
     * Update the hoarding instance with Step 2 data.
     */
    public function updateStep2($hoarding, array $data)
    {
        $hoarding->fill($data);
        try {
            $hoarding->save();
        } catch (\Throwable $e) {
            \Log::error('Step2 updateStep2: save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Failed to update hoarding: ' . $e->getMessage(), 0, $e);
        }
        return $hoarding;
    }

    // ─────────────────────────────────────────────────────────────
    // BRAND LOGOS
    // ─────────────────────────────────────────────────────────────

    public function storeBrandLogos($parentId, array $logoFiles): array
    {
        $hoarding = Hoarding::findOrFail($parentId);
        $saved    = [];

        foreach ($logoFiles as $index => $file) {
            $uuid      = Str::uuid()->toString();
            $ext       = strtolower($file->getClientOriginalExtension());
            $directory = "oohHoardings/brand_logos/{$parentId}";
            $filename  = "{$uuid}.{$ext}";
            $path      = $file->storeAs($directory, $filename, 'public');

            $saved[] = $hoarding->brandLogos()->create([
                'file_path'  => $path,
                'sort_order' => $index,
            ]);
        }

        return $saved;
    }

    /**
     * Delete brand logos by comma-separated ID string — manual file + DB deletion.
     */
    public function deleteBrandLogos(Hoarding $hoarding, string $deletedIdsString): void
    {
        $deleteIds = array_filter(
            array_map('intval', explode(',', $deletedIdsString))
        );

        if (empty($deleteIds)) {
            return;
        }

        $logos = $hoarding->brandLogos()
            ->whereIn('id', $deleteIds)
            ->get();

        foreach ($logos as $logo) {
            if (!empty($logo->file_path) && Storage::disk('public')->exists($logo->file_path)) {
                Storage::disk('public')->delete($logo->file_path);
            }
            $logo->delete();
        }

        \Log::info('Brand logos manually deleted', [
            'hoarding_id' => $hoarding->id,
            'deleted_ids' => $deleteIds,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3 UPDATE
    // ─────────────────────────────────────────────────────────────

    public function updateStep3($oohHoarding, array $data)
    {
        $offers = [];

        if (!empty($data['offers_json'])) {
            $offers = json_decode($data['offers_json'], true);
        }

        // fallback to old array fields
        if (empty($offers) && !empty($data['offer_name'])) {
            $count = is_array($data['offer_name']) ? count($data['offer_name']) : 0;
            for ($i = 0; $i < $count; $i++) {
                $offers[] = [
                    'name'                 => $data['offer_name'][$i]       ?? '',
                    'min_booking_duration' => $data['offer_duration'][$i]   ?? 1,
                    'duration_unit'        => $data['offer_unit'][$i]       ?? 'months',
                    'discount'             => $data['offer_discount'][$i]   ?? 0,
                    'start_date'           => $data['offer_start_date'][$i] ?? null,
                    'end_date'             => $data['offer_end_date'][$i]   ?? null,
                    'services'             => $data['offer_services'][$i]   ?? [],
                ];
            }
        }

        if (!empty($offers)) {
            $data['offers'] = $offers;
            $this->storePackages($oohHoarding->id, $data);
        }

        // Move parent-level fields to parent hoarding
        $parent        = $oohHoarding->hoarding;
        $parentChanged = false;

        $parentFields = [
            'survey_charge',
            'graphics_included',
            'graphics_charge',
            'enable_weekly_booking',
            'weekly_price_1',
            'weekly_price_2',
            'weekly_price_3',
        ];

        foreach ($parentFields as $field) {
            if (isset($data[$field])) {
                $parent->$field = $data[$field];
                unset($data[$field]);
                $parentChanged = true;
            }
        }

        if ($parentChanged) {
            $parent->save();
        }

        // Update child OOHHoarding fields
        $oohHoarding->fill($data);
        $oohHoarding->save();

        return $oohHoarding;
    }

    // ─────────────────────────────────────────────────────────────
    // PACKAGES
    // ─────────────────────────────────────────────────────────────

    public function storePackages($hoardingId, array $data)
    {
        $oohHoarding    = \Modules\Hoardings\Models\OOHHoarding::where('id', $hoardingId)->first();
        $actualParentId = $oohHoarding->hoarding_id;
        $parent         = \App\Models\Hoarding::findOrFail($actualParentId);

        $existingIds = [];

        if (isset($data['offers']) && is_array($data['offers'])) {
            foreach ($data['offers'] as $offer) {
                if (!empty($offer['name'])) {
                    if (!empty($offer['package_id'])) {
                        $pkg = HoardingPackage::where('id', $offer['package_id'])
                            ->where('hoarding_id', $actualParentId)
                            ->first();
                        if ($pkg) {
                            $pkg->update([
                                'package_name'         => $offer['name'],
                                'min_booking_duration' => $offer['duration']      ?? $offer['min_booking_duration'] ?? 1,
                                'duration_unit'        => $offer['unit']          ?? $offer['duration_unit']        ?? 'months',
                                'discount_percent'     => $offer['discount']      ?? $offer['discount_value']       ?? 0,
                                'start_date'           => $offer['start_date']    ?? null,
                                'end_date'             => $offer['end_date']      ?? null,
                                'is_active'            => $offer['is_active']     ?? true,
                                'services_included'    => $offer['services']      ?? [],
                            ]);
                            $existingIds[] = $pkg->id;
                        }
                    } else {
                        $pkg = HoardingPackage::create([
                            'hoarding_id'          => $actualParentId,
                            'vendor_id'            => $parent->vendor_id,
                            'package_name'         => $offer['name'],
                            'min_booking_duration' => $offer['duration']      ?? $offer['min_booking_duration'] ?? 1,
                            'duration_unit'        => $offer['unit']          ?? $offer['duration_unit']        ?? 'months',
                            'discount_percent'     => $offer['discount']      ?? $offer['discount_value']       ?? 0,
                            'start_date'           => $offer['start_date']    ?? null,
                            'end_date'             => $offer['end_date']      ?? null,
                            'is_active'            => $offer['is_active']     ?? true,
                            'services_included'    => $offer['services']      ?? [],
                        ]);
                        $existingIds[] = $pkg->id;
                    }
                }
            }

            HoardingPackage::where('hoarding_id', $actualParentId)
                ->whereNotIn('id', $existingIds)
                ->delete();

        } elseif (isset($data['offer_name']) && is_array($data['offer_name'])) {
            // Legacy array fields
            HoardingPackage::where('hoarding_id', $actualParentId)->delete();

            foreach ($data['offer_name'] as $index => $name) {
                if (!empty($name)) {
                    HoardingPackage::create([
                        'hoarding_id'          => $actualParentId,
                        'vendor_id'            => $parent->vendor_id,
                        'package_name'         => $name,
                        'min_booking_duration' => $data['offer_duration'][$index]   ?? 1,
                        'duration_unit'        => $data['offer_unit'][$index]       ?? 'months',
                        'discount_percent'     => $data['offer_discount'][$index]   ?? 0,
                        'start_date'           => $data['offer_start_date'][$index] ?? null,
                        'end_date'             => $data['offer_end_date'][$index]   ?? null,
                        'is_active'            => true,
                        'services_included'    => isset($data['offer_services'][$index])
                            ? (array) $data['offer_services'][$index]
                            : [],
                    ]);
                }
            }
        }
    }
}