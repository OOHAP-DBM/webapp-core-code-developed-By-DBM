<?php

namespace Modules\Hoardings\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Hoardings\Models\OOHHoarding;
use Modules\Hoardings\Services\HoardingListService;
use Modules\Hoardings\Repositories\HoardingListRepository;

class OOHUpdateController extends Controller
{
    public function __construct(
        protected HoardingListService    $hoardingService,
        protected HoardingListRepository $repo,
    ) {}

    // =========================================================================
    // GET /api/v1/vendor/ooh/{id}
    // {id} = ooh_hoardings.id
    // Returns full OOH detail for the authenticated vendor
    // =========================================================================
    public function show(int $id): JsonResponse
    {
        $ooh = $this->findOwnedOoh($id);
        if (!$ooh) {
            return $this->notFound();
        }

        $ooh->load([
            'hoarding',
            'hoarding.hoardingMedia',   // hoarding_media.hoarding_id   = parent hoardings.id
            'hoarding.brandLogos',      // hoarding_brand_logos.hoarding_id = parent hoardings.id
            'hoarding.oohPackages',     // hoarding_packages.hoarding_id    = parent hoardings.id
        ]);

        return response()->json(['data' => $this->formatResponse($ooh)]);
    }

    // =========================================================================
    // PUT /api/v1/vendor/ooh/{id}/step1
    // Updates: basic info on parent Hoarding + dimensions on OOHHoarding (child)
    //          + media files stored with hoarding_media.hoarding_id = parent hoardings.id
    // {id} = ooh_hoardings.id
    // =========================================================================
    public function updateStep1(Request $request, int $id): JsonResponse
    {
        $ooh = $this->findOwnedOoh($id);
        if (!$ooh) {
            return $this->notFound();
        }

        $validated = $request->validate([
            'category'           => 'required|string',
            'width'              => 'required|numeric|min:1',
            'height'             => 'required|numeric|min:1',
            'measurement_unit'   => 'required|in:sqft,sqm',
            'address'            => 'required|string',
            'locality'           => 'nullable|string',
            'city'               => 'nullable|string',
            'state'              => 'required|string',
            'pincode'            => 'required|string|max:10',
            'lat'                => 'required|numeric',
            'lng'                => 'required|numeric',
            'base_monthly_price' => 'required|numeric|min:0',
            'monthly_price'      => 'nullable|numeric|min:0',

            // Comma-separated IDs from hoarding_media table to delete
            // e.g. "3,7,12"
            // Scoped inside repo->deleteMedia() to hoarding_media.hoarding_id = parent hoardings.id
            'deleted_media_ids'  => 'nullable|string',

            // New uploads — stored with hoarding_media.hoarding_id = parent hoardings.id
            'media'              => 'nullable|array',
            'media.*'            => 'file|mimes:jpeg,jpg,png,webp,mp4,webm|max:10240',
        ]);

        // Price guard
        if (
            isset($validated['monthly_price']) &&
            $validated['monthly_price'] > $validated['base_monthly_price']
        ) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => ['monthly_price' => ['Discounted price must be ≤ base price.']],
            ], 422);
        }

        try {
            // service->updateStep1(parentHoarding, oohHoarding, data, files)
            // ├─ Updates parent Hoarding: category, address, location, prices
            // ├─ Updates OOHHoarding: width, height, measurement_unit
            // ├─ repo->deleteMedia(parent_hoarding_id, deleted_ids)
            // │    WHERE hoarding_media.hoarding_id = parent hoardings.id
            // └─ repo->storeMedia(parent_hoarding_id, files)
            //      INSERT hoarding_media.hoarding_id = parent hoardings.id
            $this->hoardingService->updateStep1(
                hoarding:    $ooh->hoarding,
                oohHoarding: $ooh,
                data:        $validated,
                mediaFiles:  $request->file('media', []),
            );

            $ooh->load(['hoarding', 'hoarding.hoardingMedia']);

            return response()->json([
                'message' => 'Step 1 updated successfully.',
                'data'    => $this->formatResponse($ooh),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PUT /api/v1/vendor/ooh/{id}/step2
    // Updates: visibility, legal, audience on parent Hoarding
    //          + brand logos stored with hoarding_brand_logos.hoarding_id = parent hoardings.id
    // {id} = ooh_hoardings.id
    // =========================================================================
    public function updateStep2(Request $request, int $id): JsonResponse
    {
        $ooh = $this->findOwnedOoh($id);
        if (!$ooh) {
            return $this->notFound();
        }

        // All brand logo operations are against the parent Hoarding
        $parentHoarding = $ooh->hoarding;

        $request->validate([
            'nagar_nigam_approved' => 'required|boolean',
            'grace_period_days'    => 'nullable|integer|min:0|max:30',
            'blocked_dates_json'   => 'nullable|json',
            'permit_number'        => 'nullable|string|max:255',
            'permit_valid_till'    => 'nullable|date|after:today',
            'audience_type'        => 'nullable|array',
            'audience_type.*'      => 'nullable|string',
            'visible_from'         => 'nullable|array',
            'visible_from.*'       => 'nullable|string',
            'located_at'           => 'nullable|array',
            'located_at.*'         => 'nullable|string',
            'visibility_type'      => 'nullable|in:one_way,both_side',
            'visibility_start'     => 'nullable|string',
            'visibility_end'       => 'nullable|string',
            'expected_footfall'    => 'nullable|integer|min:0',
            'expected_eyeball'     => 'nullable|integer|min:0',

            // New logo uploads
            // Stored with hoarding_brand_logos.hoarding_id = parent hoardings.id
            'brand_logos'          => 'nullable|array',
            'brand_logos.*'        => 'file|mimes:jpeg,jpg,png,webp|max:2048',

            // Comma-separated IDs from hoarding_brand_logos to delete
            // e.g. "2,5"
            // Scoped inside repo->deleteBrandLogos() to hoarding_brand_logos.hoarding_id = parent id
            'delete_brand_logos'   => 'nullable|string',
        ]);

        // Guard: max 10 logos total
        $deletedIds    = $this->parseIds($request->input('delete_brand_logos', ''));
        $existingCount = $parentHoarding->brandLogos()->count() - count($deletedIds);
        $newCount      = count($request->file('brand_logos', []));

        if ($existingCount + $newCount > 10) {
            return response()->json([
                'message' => 'Maximum 10 brand logos allowed.',
                'errors'  => [
                    'brand_logos' => [
                        "You have {$existingCount} existing logo(s) and are adding {$newCount} more. Maximum is 10.",
                    ],
                ],
            ], 422);
        }

        try {
            // service->storeStep2(parentHoarding, data, brandLogoFiles)
            // ├─ repo->updateStep2(parentHoarding, mappedData)
            // │    Saves visibility/legal/audience fields to parent Hoarding
            // ├─ repo->deleteBrandLogos(parentHoarding, ids)
            // │    WHERE hoarding_brand_logos.hoarding_id = parent hoardings.id
            // └─ repo->storeBrandLogos(parent_hoarding_id, files)
            //      INSERT hoarding_brand_logos.hoarding_id = parent hoardings.id
            $this->hoardingService->storeStep2(
                hoarding:       $parentHoarding,
                data:           $request->all(),
                brandLogoFiles: $request->file('brand_logos', []),
            );

            $ooh->load(['hoarding', 'hoarding.brandLogos']);

            return response()->json([
                'message' => 'Step 2 updated successfully.',
                'data'    => $this->formatResponse($ooh),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PUT /api/v1/vendor/ooh/{id}/step3
    // Updates: add-on charges on OOHHoarding (child)
    //          + weekly prices / graphics / survey_charge on parent Hoarding
    //          + packages with hoarding_packages.hoarding_id = parent hoardings.id
    // {id} = ooh_hoardings.id
    // =========================================================================
    public function updateStep3(Request $request, int $id): JsonResponse
    {
        $ooh = $this->findOwnedOoh($id);
        if (!$ooh) {
            return $this->notFound();
        }

        $validated = $request->validate([
            // ── Saved on parent Hoarding ──────────────────────────────────
            'enable_weekly_booking' => 'nullable|boolean',
            'weekly_price_1'        => 'nullable|numeric|min:0',
            'weekly_price_2'        => 'nullable|numeric|min:0',
            'weekly_price_3'        => 'nullable|numeric|min:0',
            'survey_charge'         => 'nullable|numeric|min:0',
            'graphics_included'     => 'nullable|boolean',
            'graphics_charge'       => 'nullable|numeric|min:0',

            // ── Saved on OOHHoarding (child) ──────────────────────────────
            'mounting_included'     => 'nullable|boolean',
            'mounting_charge'       => 'nullable|numeric|min:0',
            'printing_included'     => 'nullable|boolean',
            'printing_charge'       => 'nullable|numeric|min:0',
            'material_type'         => 'nullable|in:flex,vinyl,canvas',
            'lighting_included'     => 'nullable|boolean',
            'lighting_charge'       => 'nullable|numeric|min:0',
            'lighting_type'         => 'nullable|in:front-lit,back-lit,led,none',
            'remounting_charge'     => 'nullable|numeric|min:0',

            // ── Packages / Offers ─────────────────────────────────────────
            // JSON array of offer objects:
            // [
            //   {
            //     "package_id": 5,            // optional — omit to create new
            //     "name": "3-Month Deal",
            //     "min_booking_duration": 3,
            //     "duration_unit": "months",
            //     "discount": 15,
            //     "start_date": "2025-01-01",  // optional
            //     "end_date": "2025-12-31",    // optional
            //     "services": ["printing"],    // optional
            //     "is_active": true
            //   }
            // ]
            // Stored with hoarding_packages.hoarding_id = parent hoardings.id
            // (repo->storePackages resolves parent id from oohHoarding->hoarding_id)
            'offers_json' => 'nullable|json',
        ]);

        try {
            // service->storeStep3(oohHoarding, data)
            // └─ repo->updateStep3(oohHoarding, data)
            //      ├─ Extracts parent fields (weekly_price_*, survey_charge,
            //      │   graphics_*, enable_weekly_booking) → saves to parent Hoarding
            //      ├─ Saves remaining child fields → OOHHoarding->fill()->save()
            //      │   (printing_*, mounting_*, lighting_*, remounting_*, material_type)
            //      └─ repo->storePackages(oohHoarding->id, data)
            //           Resolves actualParentId = ooh->hoarding_id
            //           INSERT/UPDATE hoarding_packages.hoarding_id = parent hoardings.id
            $this->hoardingService->storeStep3($ooh, $validated);

            $ooh->load(['hoarding', 'hoarding.oohPackages']);

            return response()->json([
                'message' => 'Step 3 updated successfully.',
                'data'    => $this->formatResponse($ooh),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // DELETE /api/v1/vendor/ooh/{id}/media/{mediaId}
    // Deletes one hoarding_media row scoped to parent hoardings.id
    // =========================================================================
    public function deleteMedia(int $id, int $mediaId): JsonResponse
    {
        $ooh = $this->findOwnedOoh($id);
        if (!$ooh) {
            return $this->notFound();
        }

        // repo->deleteMedia(parent_hoarding_id, comma-separated-ids)
        // WHERE hoarding_media.hoarding_id = parent hoardings.id
        $this->repo->deleteMedia($ooh->hoarding->id, (string) $mediaId);

        return response()->json(['message' => 'Media deleted successfully.']);
    }

    // =========================================================================
    // DELETE /api/v1/vendor/ooh/{id}/brand-logos/{logoId}
    // Deletes one hoarding_brand_logos row scoped to parent hoardings.id
    // =========================================================================
    public function deleteBrandLogo(int $id, int $logoId): JsonResponse
    {
        $ooh = $this->findOwnedOoh($id);
        if (!$ooh) {
            return $this->notFound();
        }

        // repo->deleteBrandLogos(parentHoarding, comma-separated-ids)
        // WHERE hoarding_brand_logos.hoarding_id = parent hoardings.id
        $this->repo->deleteBrandLogos($ooh->hoarding, (string) $logoId);

        return response()->json(['message' => 'Brand logo deleted successfully.']);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Find an OOHHoarding owned by the authenticated vendor.
     * Always eager-loads the parent Hoarding.
     */
    private function findOwnedOoh(int $parent_id): ?OOHHoarding
    {
        return OOHHoarding::with('hoarding')
            ->whereHas('hoarding', fn($q) => $q->where('vendor_id', Auth::id()))
            ->where('hoarding_id', $parent_id)  // ← lookup by parent hoardings.id
            ->first();
    }

    private function notFound(): JsonResponse
    {
        return response()->json(['message' => 'OOH hoarding not found.'], 404);
    }

    private function parseIds(string $raw): array
    {
        return array_values(array_filter(array_map('intval', explode(',', $raw))));
    }

    /**
     * Consistent API response shape.
     *
     * Storage ownership (all three tables use parent hoardings.id as FK):
     *   hoarding_media.hoarding_id       → hoardings.id
     *   hoarding_brand_logos.hoarding_id → hoardings.id
     *   hoarding_packages.hoarding_id    → hoardings.id
     *
     * OOH child stores physical dimensions & add-on charges:
     *   ooh_hoardings.hoarding_id → hoardings.id
     */
    private function formatResponse(OOHHoarding $ooh): array
    {
        $h = $ooh->hoarding; // parent Hoarding

        return [
            // ── identifiers ───────────────────────────────────────────────
            'ooh_id'      => $ooh->id,
            'hoarding_id' => $h->id,
            'status'      => $h->status,

            // ── step 1: basic info (parent Hoarding) ──────────────────────
            'category'           => $h->category,
            'address'            => $h->address,
            'locality'           => $h->locality,
            'city'               => $h->city,
            'state'              => $h->state,
            'pincode'            => $h->pincode,
            'lat'                => $h->latitude,
            'lng'                => $h->longitude,
            'base_monthly_price' => $h->base_monthly_price,
            'monthly_price'      => $h->monthly_price,

            // ── OOH child: physical dimensions ────────────────────────────
            'width'            => $ooh->width,
            'height'           => $ooh->height,
            'measurement_unit' => $ooh->measurement_unit,

            // ── media (hoarding_media.hoarding_id = parent hoardings.id) ──
            'media' => $h->hoardingMedia
                ? $h->hoardingMedia->map(fn($m) => [
                    'id'         => $m->id,
                    'url'        => asset('storage/' . ltrim($m->file_path, '/')),
                    'type'       => $m->media_type,
                    'is_primary' => (bool) $m->is_primary,
                    'sort_order' => $m->sort_order,
                ])->values()
                : [],

            // ── step 2: visibility / legal (parent Hoarding) ──────────────
            'nagar_nigam_approved' => $h->nagar_nigam_approved,
            'permit_number'        => $h->permit_number,
            'permit_valid_till'    => $h->permit_valid_till?->toDateString(),
            'grace_period_days'    => $h->grace_period_days,
            'hoarding_visibility'  => $h->hoarding_visibility,
            'visibility_start'     => $h->visibility_start,
            'visibility_end'       => $h->visibility_end,
            'expected_footfall'    => $h->expected_footfall,
            'expected_eyeball'     => $h->expected_eyeball,
            'audience_types'       => $h->audience_types,
            'located_at'           => $h->located_at,
            'block_dates'          => $h->block_dates,

            // ── brand logos (hoarding_brand_logos.hoarding_id = parent id) ─
            'brand_logos' => $h->brandLogos
                ? $h->brandLogos->map(fn($logo) => [
                    'id'         => $logo->id,
                    'url'        => asset('storage/' . ltrim($logo->file_path, '/')),
                    'sort_order' => $logo->sort_order,
                ])->values()
                : [],

            // ── step 3: OOH child add-on charges ──────────────────────────
            'printing_included' => $ooh->printing_included,
            'printing_charge'   => $ooh->printing_charge,
            'mounting_included' => $ooh->mounting_included,
            'mounting_charge'   => $ooh->mounting_charge,
            'remounting_charge' => $ooh->remounting_charge,
            'lighting_included' => $ooh->lighting_included,
            'lighting_charge'   => $ooh->lighting_charge,
            'lighting_type'     => $ooh->lighting_type,
            'material_type'     => $ooh->material_type,

            // ── step 3: pricing on parent Hoarding ────────────────────────
            'enable_weekly_booking' => $h->enable_weekly_booking,
            'weekly_price_1'        => $h->weekly_price_1,
            'weekly_price_2'        => $h->weekly_price_2,
            'weekly_price_3'        => $h->weekly_price_3,
            'survey_charge'         => $h->survey_charge,
            'graphics_included'     => $h->graphics_included,
            'graphics_charge'       => $h->graphics_charge,

            // ── packages (hoarding_packages.hoarding_id = parent hoardings.id) ─
            'packages' => $h->oohPackages
                ? $h->oohPackages->map(fn($pkg) => [
                    'id'                   => $pkg->id,
                    'name'                 => $pkg->package_name,
                    'min_booking_duration' => $pkg->min_booking_duration,
                    'duration_unit'        => $pkg->duration_unit,
                    'discount_percent'     => $pkg->discount_percent,
                    'start_date'           => $pkg->start_date?->toDateString(),
                    'end_date'             => $pkg->end_date?->toDateString(),
                    'is_active'            => $pkg->is_active,
                    'services_included'    => $pkg->services_included,
                ])->values()
                : [],
        ];
    }
}