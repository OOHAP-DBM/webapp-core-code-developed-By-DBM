<?php

namespace Modules\Hoardings\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HoardingResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Hoardings\Services\HoardingService;
use Illuminate\Support\Facades\Validator;

class HoardingController extends Controller
{
    /**
     * @var HoardingService
     */
    protected $hoardingService;

    /**
     * HoardingController constructor.
     *
     * @param HoardingService $hoardingService
     */
    public function __construct(HoardingService $hoardingService)
    {
        $this->hoardingService = $hoardingService;
    }

    /**
     * Display a listing of hoardings with filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'vendor_id' => $request->input('vendor_id'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'radius' => $request->input('radius', 10),
            'bbox' => $request->input('bbox'), // Format: minLat,minLng,maxLat,maxLng
            'near' => $request->input('near'), // Format: lat,lng
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        $perPage = $request->input('per_page', 15);
        $hoardings = $this->hoardingService->getAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => HoardingResource::collection($hoardings),
            'meta' => [
                'current_page' => $hoardings->currentPage(),
                'from' => $hoardings->firstItem(),
                'last_page' => $hoardings->lastPage(),
                'per_page' => $hoardings->perPage(),
                'to' => $hoardings->lastItem(),
                'total' => $hoardings->total(),
            ],
        ]);
    }

    /**
     * Get map pins (compact data for map markers).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function mapPins(Request $request): JsonResponse
    {
        $filters = [
            'bbox' => $request->input('bbox'), // Format: minLat,minLng,maxLat,maxLng
            'near' => $request->input('near'), // Format: lat,lng
            'radius' => $request->input('radius', 10),
            'type' => $request->input('type'),
            'vendor_id' => $request->input('vendor_id'),
        ];

        $pins = $this->hoardingService->getMapPins($filters);

        return response()->json([
            'success' => true,
            'data' => $pins,
            'total' => $pins->count(),
        ]);
    }

    /**
     * Display the specified hoarding.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding) {
            return response()->json([
                'success' => false,
                'message' => 'Hoarding not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new HoardingResource($hoarding),
        ]);
    }

    /**
     * Store a newly created hoarding.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'required|numeric|min:0',
            'enable_weekly_booking' => 'boolean',
            'type' => 'required|in:billboard,digital,transit,street_furniture,wallscape,mobile',
            'status' => 'nullable|in:draft,pending_approval,active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['vendor_id'] = auth()->id();

            $hoarding = $this->hoardingService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Hoarding created successfully.',
                'data' => new HoardingResource($hoarding),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update the specified hoarding.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding) {
            return response()->json([
                'success' => false,
                'message' => 'Hoarding not found.',
            ], 404);
        }

        // Check ownership
        if ($hoarding->vendor_id !== auth()->id() && !auth()->user()->hasRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|required|string',
            'lat' => 'sometimes|required|numeric|between:-90,90',
            'lng' => 'sometimes|required|numeric|between:-180,180',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'sometimes|required|numeric|min:0',
            'enable_weekly_booking' => 'boolean',
            'type' => 'sometimes|required|in:billboard,digital,transit,street_furniture,wallscape,mobile',
            'status' => 'sometimes|required|in:draft,pending_approval,active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $this->hoardingService->update($id, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Hoarding updated successfully.',
                'data' => new HoardingResource($this->hoardingService->getById($id)),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified hoarding.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding) {
            return response()->json([
                'success' => false,
                'message' => 'Hoarding not found.',
            ], 404);
        }

        // Check ownership
        if ($hoarding->vendor_id !== auth()->id() && !auth()->user()->hasRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $this->hoardingService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Hoarding deleted successfully.',
        ]);
    }
}

