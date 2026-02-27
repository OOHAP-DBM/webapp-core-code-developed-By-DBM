<?php

namespace Modules\DOOH\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\DOOH\Models\DOOHScreen;
use Modules\DOOH\Models\DOOHScreenMedia;
use Modules\DOOH\Services\DOOHScreenService;

class DOOHUpdateController extends Controller
{
    protected DOOHScreenService $service;

    public function __construct(DOOHScreenService $service)
    {
        $this->service = $service;
    }

    // =========================================================================
    // GET /api/v1/vendor/dooh/{id}
    // Returns all data for a DOOH screen (used to pre-fill mobile edit form)
    // =========================================================================
    public function show(int $id): JsonResponse
    {
        $vendor = Auth::user();
        $screen = DOOHScreen::with([
            'hoarding',
            'brandLogos',
            'media',
            'slots',
            'packages',
        ])
        ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $vendor->id))
        ->where('hoarding_id', $id)  // filter by hoarding_id instead of primary key
        ->first();

        if (!$screen) {
            return $this->errorResponse('DOOH screen not found.', 404);
        }

        $hoarding = $screen->hoarding;

        return $this->successResponse('DOOH screen fetched successfully.', [
            'screen' => [
                // ── Step 1 fields ──────────────────────────────────────────
                'id'                     => $screen->id,
                'screen_type'            => $screen->screen_type,
                'width'                  => $screen->width,
                'height'                 => $screen->height,
                'measurement_unit'       => $screen->measurement_unit,
                'price_per_slot'         => $screen->price_per_slot,
                'slot_duration_seconds'  => $screen->slot_duration_seconds,
                'total_slots_per_day'    => $screen->total_slots_per_day,
                'screen_run_time'        => $screen->screen_run_time,

                // ── Hoarding / Step 1 fields ───────────────────────────────
                'hoarding_id'            => $hoarding->id,
                'name'                   => $hoarding->name,
                'title'                  => $hoarding->title,
                'description'            => $hoarding->description,
                'category'               => $hoarding->category,
                'address'                => $hoarding->address,
                'locality'               => $hoarding->locality,
                'city'                   => $hoarding->city,
                'state'                  => $hoarding->state,
                'pincode'                => $hoarding->pincode,
                'latitude'               => $hoarding->latitude,
                'longitude'              => $hoarding->longitude,
                'base_monthly_price'     => $hoarding->base_monthly_price,
                'monthly_price'          => $hoarding->monthly_price,
                'discount_type'          => $hoarding->discount_type,
                'discount_value'         => $hoarding->discount_value,
                'status'                 => $hoarding->status,
                'current_step'           => $hoarding->current_step,

                // ── Step 2 fields ──────────────────────────────────────────
                'nagar_nigam_approved'   => (bool) $hoarding->nagar_nigam_approved,
                'permit_number'          => $hoarding->permit_number,
                'permit_valid_till'      => $hoarding->permit_valid_till,
                'expected_footfall'      => $hoarding->expected_footfall,
                'expected_eyeball'       => $hoarding->expected_eyeball,
                'audience_types'         => $hoarding->audience_types ?? [],
                'block_dates'            => $hoarding->block_dates ?? [],
                'needs_grace_period'     => ($hoarding->grace_period_days > 0),
                'grace_period_days'      => $hoarding->grace_period_days,
                'hoarding_visibility'    => $hoarding->hoarding_visibility,
                'visibility_start'       => $hoarding->visibility_start,
                'visibility_end'         => $hoarding->visibility_end,
                'facing_direction'       => $hoarding->facing_direction,
                'road_type'              => $hoarding->road_type,
                'traffic_type'           => $hoarding->traffic_type,
                'visibility_details'     => $hoarding->visibility_details,
                'located_at'             => $hoarding->located_at,

                // ── Step 3 fields ──────────────────────────────────────────
                'graphics_included'      => (bool) $hoarding->graphics_included,
                'graphics_charge'        => $hoarding->graphics_charge,
                'survey_charge'          => $hoarding->survey_charge,

                // ── Media ──────────────────────────────────────────────────
                'media' => $screen->media->map(fn($m) => [
                    'id'         => $m->id,
                    'url'        => asset('storage/' . $m->file_path),
                    'media_type' => $m->media_type,
                    'is_primary' => (bool) $m->is_primary,
                    'sort_order' => $m->sort_order,
                ])->values(),

                // ── Brand Logos ────────────────────────────────────────────
                'brand_logos' => $hoarding->brandLogos->map(fn($logo) => [
                    'id'         => $logo->id,
                    'url'        => asset('storage/' . $logo->file_path),
                    'sort_order' => $logo->sort_order,
                ])->values(),

                // ── Slots ──────────────────────────────────────────────────
                'slots' => $screen->slots->map(fn($slot) => [
                    'id'         => $slot->id,
                    'slot_name'  => $slot->slot_name,
                    'start_time' => $slot->start_time,
                    'end_time'   => $slot->end_time,
                    'is_active'  => (bool) $slot->is_active,
                    'status'     => $slot->status,
                ])->values(),

                // ── Packages ───────────────────────────────────────────────
                'packages' => $screen->packages->map(fn($pkg) => [
                    'id'                   => $pkg->id,
                    'package_name'         => $pkg->package_name,
                    'min_booking_duration' => $pkg->min_booking_duration,
                    'duration_unit'        => $pkg->duration_unit,
                    'discount_percent'     => $pkg->discount_percent,
                    'services_included'    => $pkg->services_included,
                    'end_date'             => $pkg->end_date,
                    'is_active'            => (bool) $pkg->is_active,
                ])->values(),
            ],
        ]);
    }

    // =========================================================================
    // PUT /api/v1/vendor/dooh/{id}/step1
    //
    // Updates: hoarding (category, location, pricing) + screen (dimensions,
    //          slot config) + media (add new files / delete by IDs)
    //
    // Content-Type: multipart/form-data  (required for file uploads)
    //
    // Body fields:
    //   category, screen_type, width, height, measurement_unit,
    //   address, locality, city, state, pincode, lat, lng,
    //   base_monthly_price, monthly_price, price_per_slot,
    //   spot_duration, spots_per_day, daily_runtime,
    //   discount_type, discount_value,
    //   deleted_media_ids   (comma-separated string e.g. "12,15,20")
    //   media[]             (new image/video files)
    // =========================================================================
    public function updateStep1(Request $request, int $id): JsonResponse
    {
        $vendor = Auth::user();

        $screen = DOOHScreen::whereHas('hoarding', fn($q) => $q->where('vendor_id', $vendor->id))
            ->find($id);

        if (!$screen) {
            return $this->errorResponse('DOOH screen not found.', 404);
        }

        // Validate hoarding type
        if ($screen->hoarding->hoarding_type !== 'dooh') {
            return $this->errorResponse('This hoarding is not of DOOH type.', 422);
        }

        try {
            $request->validate([
                'category'          => 'required|string|max:100',
                'screen_type'       => 'required|string|max:50',
                'width'             => 'required|numeric|min:0.1|max:4000',
                'height'            => 'required|numeric|min:0.1|max:4000',
                'measurement_unit'  => 'required|in:sqft,sqm',
                'address'           => 'required|string|max:255',
                'locality'          => 'required|string|max:100',
                'pincode'           => 'required|string|max:20',
                'price_per_slot'    => 'required|numeric|min:1',
                'base_monthly_price'=> 'required|numeric|min:1',
                'monthly_price'     => 'required|numeric|min:1',
                'spot_duration'     => 'required|numeric|min:1',
                'spots_per_day'     => 'required|numeric|min:1',
                'daily_runtime'     => 'nullable|numeric|min:0|max:24',
                'lat'               => 'nullable|numeric',
                'lng'               => 'nullable|numeric',
                'city'              => 'nullable|string|max:100',
                'state'             => 'nullable|string|max:100',
                'discount_type'     => 'nullable|string|max:50',
                'discount_value'    => 'nullable|numeric|min:0',
                'deleted_media_ids' => 'nullable|string',  // "12,15,20"
                'media'             => 'nullable|array',
                'media.*'           => 'file|mimes:jpeg,jpg,png,webp,mp4,webm,mov|max:10240',
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }

        // Normalize uploaded files (same as service logic)
        $mediaFiles = $request->file('media', []);
        if (!is_array($mediaFiles)) {
            $mediaFiles = $mediaFiles ? [$mediaFiles] : [];
        }

        try {
            $result = $this->service->updateStep1($screen, $request->all(), $mediaFiles);

            if (!$result['success']) {
                return $this->errorResponse('Step 1 update failed.', 422, $result['errors'] ?? []);
            }

            $updatedScreen = $result['screen'];

            return $this->successResponse('Step 1 updated successfully.', [
                'screen_id' => $updatedScreen->id,
                'media'     => $updatedScreen->media->map(fn($m) => [
                    'id'         => $m->id,
                    'url'        => asset('storage/' . $m->file_path),
                    'media_type' => $m->media_type,
                    'is_primary' => (bool) $m->is_primary,
                ])->values(),
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Throwable $e) {
            Log::error('DOOH API Step 1 Update Failed', [
                'screen_id' => $id,
                'vendor_id' => $vendor->id,
                'error'     => $e->getMessage(),
            ]);
            return $this->errorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // =========================================================================
    // PUT /api/v1/vendor/dooh/{id}/step2
    //
    // Updates: hoarding visibility, legal info, audience, blocked dates,
    //          grace period + brand logos (add/delete)
    //
    // Content-Type: multipart/form-data  (required for brand logo uploads)
    //
    // Body fields:
    //   nagar_nigam_approved  (0 or 1)
    //   permit_number, permit_valid_till
    //   expected_footfall, expected_eyeball
    //   audience_types[]      (array of strings e.g. ["youth","family"])
    //   blocked_dates_json    (JSON string e.g. '["2025-01-10","2025-02-14"]')
    //   needs_grace_period    (0 or 1)
    //   grace_period_days     (integer, only if needs_grace_period=1)
    //   visibility_type       (one_way | both_side)
    //   visibility_start, visibility_end
    //   facing_direction, road_type, traffic_type
    //   visible_from, located_at
    //   delete_brand_logos    (comma-separated string e.g. "3,7")
    //   brand_logos[]         (new logo image files)
    // =========================================================================
    public function updateStep2(Request $request, int $id): JsonResponse
    {
        $vendor = Auth::user();

        $screen = DOOHScreen::with('hoarding')
            ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $vendor->id))
            ->find($id);

        if (!$screen) {
            return $this->errorResponse('DOOH screen not found.', 404);
        }

        if ($screen->hoarding->hoarding_type !== 'dooh') {
            return $this->errorResponse('This hoarding is not of DOOH type.', 422);
        }

        try {
            $request->validate([
                'nagar_nigam_approved' => 'required|in:0,1',
                'permit_number'        => 'nullable|string|max:255',
                'permit_valid_till'    => 'nullable|date',
                'expected_footfall'    => 'nullable|integer|min:0',
                'expected_eyeball'     => 'nullable|integer|min:0',
                'audience_types'       => 'nullable|array',
                'audience_types.*'     => 'nullable|string',
                'blocked_dates_json'   => 'nullable|string', // JSON string of date array
                'needs_grace_period'   => 'nullable|in:0,1',
                'grace_period_days'    => 'nullable|integer|min:0|max:365',
                'visibility_type'      => 'nullable|in:one_way,both_side',
                'visibility_start'     => 'nullable|string',
                'visibility_end'       => 'nullable|string',
                'facing_direction'     => 'nullable|string|max:100',
                'road_type'            => 'nullable|string|max:100',
                'traffic_type'         => 'nullable|string|max:100',
                'visible_from'         => 'nullable|string',
                'located_at'           => 'nullable|string',
                'delete_brand_logos'   => 'nullable|string', // "3,7,9"
                'brand_logos'          => 'nullable|array',
                'brand_logos.*'        => 'file|mimes:jpeg,jpg,png,webp|max:2048',
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }

        // Normalize brand logo files
        $brandLogoFiles = $request->file('brand_logos', []);
        if (!is_array($brandLogoFiles)) {
            $brandLogoFiles = $brandLogoFiles ? [$brandLogoFiles] : [];
        }

        try {
            $result = $this->service->storeStep2($screen, $request->all(), $brandLogoFiles);

            if (!$result['success']) {
                return $this->errorResponse('Step 2 update failed.', 422, $result['errors'] ?? []);
            }

            $updatedHoarding = $result['hoarding'];

            return $this->successResponse('Step 2 updated successfully.', [
                'screen_id'   => $screen->id,
                'hoarding_id' => $updatedHoarding->id,
                'brand_logos' => $updatedHoarding->brandLogos->map(fn($logo) => [
                    'id'         => $logo->id,
                    'url'        => asset('storage/' . $logo->file_path),
                    'sort_order' => $logo->sort_order,
                ])->values(),
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Throwable $e) {
            Log::error('DOOH API Step 2 Update Failed', [
                'screen_id' => $id,
                'vendor_id' => $vendor->id,
                'error'     => $e->getMessage(),
            ]);
            return $this->errorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // =========================================================================
    // PUT /api/v1/vendor/dooh/{id}/step3
    //
    // Updates: graphics charge, survey charge, slots, campaign packages
    // Triggers admin/vendor notifications (same as web flow)
    //
    // Content-Type: application/json  OR  multipart/form-data
    //
    // Body fields:
    //   graphics_included  (0 or 1)
    //   graphics_charge    (numeric)
    //   survey_charge      (numeric)
    //   hoarding_visibility (string)
    //
    //   slots[] — array of slot objects:
    //     [
    //       { "slot_name": "Morning", "start_time": "06:00", "end_time": "12:00", "is_active": "1" },
    //       { "slot_name": "Evening", "start_time": "17:00", "end_time": "22:00", "is_active": "1" }
    //     ]
    //
    //   offers_json — JSON string of package array:
    //     '[
    //       { "name": "3 Month Deal", "duration": 3, "unit": "months", "discount": 10, "end_date": null, "services": [] },
    //       { "name": "6 Month Deal", "duration": 6, "unit": "months", "discount": 20, "end_date": "2025-12-31", "services": ["printing"] }
    //     ]'
    // =========================================================================
    public function updateStep3(Request $request, int $id): JsonResponse
    {
        $vendor = Auth::user();

        $screen = DOOHScreen::with('hoarding')
            ->whereHas('hoarding', fn($q) => $q->where('vendor_id', $vendor->id))
            ->find($id);

        if (!$screen) {
            return $this->errorResponse('DOOH screen not found.', 404);
        }

        if ($screen->hoarding->hoarding_type !== 'dooh') {
            return $this->errorResponse('This hoarding is not of DOOH type.', 422);
        }

        try {
            $request->validate([
                'graphics_included'   => 'nullable|in:0,1',
                'graphics_charge'     => 'nullable|numeric|min:0',
                'survey_charge'       => 'nullable|numeric|min:0',
                'hoarding_visibility' => 'nullable|string|max:100',

                // Slots — optional; if provided must be well-formed
                'slots'               => 'nullable|array',
                'slots.*.slot_name'   => 'required_with:slots|string|max:100',
                'slots.*.start_time'  => 'required_with:slots|date_format:H:i',
                'slots.*.end_time'    => 'required_with:slots|date_format:H:i|after:slots.*.start_time',
                'slots.*.is_active'   => 'nullable|in:0,1',

                // Packages as JSON string (same as web)
                'offers_json'         => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }

        // Validate offers_json is valid JSON if present
        if ($request->filled('offers_json')) {
            $decoded = json_decode($request->input('offers_json'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->validationErrorResponse(['offers_json' => ['offers_json must be a valid JSON string.']]);
            }
        }

        try {
            // NOTE: uses storeStep3 (same as web "create step 3") which handles
            // notifications + auto-approval logic exactly like the web wizard.
            $result = $this->service->storeStep3($screen, $request->all());

            if (!$result['success']) {
                return $this->errorResponse('Step 3 update failed.', 422, $result['errors'] ?? []);
            }

            $updatedScreen = $result['screen'];
            $hoarding      = $screen->hoarding->fresh();

            return $this->successResponse('DOOH screen updated successfully.', [
                'screen_id'    => $updatedScreen->id,
                'hoarding_id'  => $hoarding->id,
                'status'       => $hoarding->status,
                'current_step' => $hoarding->current_step,
                'message'      => $hoarding->status === 'active'
                    ? 'Your DOOH screen is now live.'
                    : 'Your DOOH screen is under review and will be published once approved.',
                'slots'    => $updatedScreen->slots->map(fn($slot) => [
                    'id'         => $slot->id,
                    'slot_name'  => $slot->slot_name,
                    'start_time' => $slot->start_time,
                    'end_time'   => $slot->end_time,
                    'is_active'  => (bool) $slot->is_active,
                ])->values(),
                'packages' => $updatedScreen->packages->map(fn($pkg) => [
                    'id'                   => $pkg->id,
                    'package_name'         => $pkg->package_name,
                    'min_booking_duration' => $pkg->min_booking_duration,
                    'duration_unit'        => $pkg->duration_unit,
                    'discount_percent'     => $pkg->discount_percent,
                    'end_date'             => $pkg->end_date,
                    'is_active'            => (bool) $pkg->is_active,
                ])->values(),
            ]);

        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Throwable $e) {
            Log::error('DOOH API Step 3 Update Failed', [
                'screen_id' => $id,
                'vendor_id' => $vendor->id,
                'error'     => $e->getMessage(),
            ]);
            return $this->errorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // =========================================================================
    // DELETE /api/v1/vendor/dooh/{id}/media
    //
    // Deletes one or more media files from a DOOH screen.
    // Useful for mobile media management outside of a step save.
    //
    // Body: { "media_ids": "12,15,20" }  OR  { "media_ids": [12, 15, 20] }
    // =========================================================================
    public function deleteMedia(Request $request, int $id): JsonResponse
    {
        $vendor = Auth::user();

        $screen = DOOHScreen::whereHas('hoarding', fn($q) => $q->where('vendor_id', $vendor->id))
            ->find($id);

        if (!$screen) {
            return $this->errorResponse('DOOH screen not found.', 404);
        }

        try {
            $request->validate([
                'media_ids' => 'required',
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }

        // Accept both comma-string and array
        $rawIds = $request->input('media_ids');
        if (is_array($rawIds)) {
            $mediaIds = array_filter(array_map('intval', $rawIds));
        } else {
            $mediaIds = array_filter(array_map('intval', explode(',', (string) $rawIds)));
        }

        if (empty($mediaIds)) {
            return $this->errorResponse('No valid media IDs provided.', 422);
        }

        try {
            $this->service->deleteMediaOnly($screen, implode(',', $mediaIds));

            return $this->successResponse('Media deleted successfully.', [
                'deleted_ids' => array_values($mediaIds),
                'screen_id'   => $screen->id,
            ]);

        } catch (\Throwable $e) {
            Log::error('DOOH API Media Delete Failed', [
                'screen_id' => $id,
                'error'     => $e->getMessage(),
            ]);
            return $this->errorResponse('Failed to delete media: ' . $e->getMessage(), 500);
        }
    }

    // =========================================================================
    // Private JSON response helpers
    // =========================================================================

    private function successResponse(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    private function errorResponse(string $message, int $statusCode = 400, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }

    private function validationErrorResponse(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $errors,
        ], 422);
    }
}