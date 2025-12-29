<?php

namespace Modules\DOOH\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\DOOH\Models\DOOHScreen;
use App\Http\Controllers\Controller;
use Modules\DOOH\Services\DOOHScreenService;
use Illuminate\Support\Facades\Auth;

class DOOHScreenController extends Controller
{
    protected $service;

    public function __construct(DOOHScreenService $service)
    {
        $this->service = $service;
    }

    /**
     * POST /api/v1/dooh/store
     * Handles Step 1, 2, and 3
     */
    public function store(Request $request)
    {
        $vendor = Auth::user();
        $step = (int) $request->input('step', 1);
        $screenId = $request->input('screen_id'); // Required for Step 2 and 3

        try {
            switch ($step) {
                case 1:
                    $result = $this->service->storeStep1($vendor, $request->all(), $request->file('media', []));
                    break;

                case 2:
                    $screen = DOOHScreen::whereHas('hoarding', function ($q) use ($vendor) {
                        $q->where('vendor_id', $vendor->id);
                    })->findOrFail($screenId);

                    $result = $this->service->storeStep2($screen, $request->all(), $request->file('brand_logos', []));
                    break;

                case 3:
                    $screen = DOOHScreen::where('vendor_id', $vendor->id)->findOrFail($screenId);
                    $result = $this->service->storeStep3($screen, $request->all());
                    break;

                default:
                    return response()->json(['message' => 'Invalid step provided'], 400);
            }

            return response()->json([
                'success' => true,
                'message' => "Step {$step} saved successfully",
                'data' => $result['screen']
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/dooh/draft
     * Resumes existing draft for the vendor
     */
    public function getDraft()
    {
        $draft = DOOHScreen::with(['media', 'brandLogos', 'slots', 'packages'])
            ->whereHas('hoarding', function ($q) {
                $q->where('vendor_id', Auth::id());
            })
            ->where('status', DOOHScreen::STATUS_DRAFT)
            ->latest()
            ->first();


        if (!$draft) {
            return response()->json(['message' => 'No active draft found'], 404);
        }

        return response()->json(['data' => $draft]);
    }

    /**
     * List DOOH hoardings for API (web-style, paginated)
     */
    public function index(Request $request): JsonResponse
    {
        $query = DOOHScreen::query()->where('status', DOOHScreen::STATUS_ACTIVE);

        // Optional filters (city, category, etc.)
        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        // Add more filters as needed

        $screens = $query->orderByDesc('created_at')->paginate(20);

        // Format response to match web listing
        $data = $screens->map(function ($screen) {
            return [
                'id' => $screen->id,
                'name' => $screen->name,
                'category' => $screen->category,
                'screen_type' => $screen->screen_type,
                'address' => $screen->address,
                'city' => $screen->city,
                'state' => $screen->state,
                'country' => $screen->country,
                'lat' => $screen->lat,
                'lng' => $screen->lng,
                'resolution' => $screen->resolution,
                'screen_size' => $screen->screen_size,
                'width' => $screen->width,
                'height' => $screen->height,
                'measurement_unit' => $screen->measurement_unit,
                'price_per_slot' => $screen->price_per_slot,
                'price_per_month' => $screen->price_per_month,
                'available_slots_per_day' => $screen->available_slots_per_day,
                'media' => $screen->media()->get()->map(function($m) {
                    return [
                        'url' => asset('storage/' . $m->file_path),
                        'type' => $m->media_type,
                        'is_primary' => $m->is_primary,
                    ];
                }),
                'slots' => $screen->slots()->get()->map(function($slot) {
                    return [
                        'name' => $slot->name,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'active' => $slot->active,
                    ];
                }),
                'packages' => $screen->packages()->get()->map(function($pkg) {
                    return [
                        'name' => $pkg->package_name,
                        'price_per_month' => $pkg->price_per_month,
                        'discount_percent' => $pkg->discount_percent,
                        'min_booking_months' => $pkg->min_booking_months,
                        'max_booking_months' => $pkg->max_booking_months,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $screens->currentPage(),
                'last_page' => $screens->lastPage(),
                'per_page' => $screens->perPage(),
                'total' => $screens->total(),
            ],
        ]);
    }
}
