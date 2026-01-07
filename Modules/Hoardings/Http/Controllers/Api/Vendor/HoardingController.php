<?php

namespace Modules\Hoardings\Http\Controllers\Api\Vendor;

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
    // public function index(Request $request): JsonResponse
    // {
    //     $filters = [
    //         'vendor_id' => $request->input('vendor_id'),
    //         'type' => $request->input('type'),
    //         'status' => $request->input('status'),
    //         'search' => $request->input('search'),
    //         'lat' => $request->input('lat'),
    //         'lng' => $request->input('lng'),
    //         'radius' => $request->input('radius', 10),
    //         'bbox' => $request->input('bbox'), // Format: minLat,minLng,maxLat,maxLng
    //         'near' => $request->input('near'), // Format: lat,lng
    //         'sort_by' => $request->input('sort_by', 'created_at'),
    //         'sort_order' => $request->input('sort_order', 'desc'),
    //     ];

    //     $perPage = $request->input('per_page', 15);
    //     $hoardings = $this->hoardingService->getAll($filters, $perPage);

    //     return response()->json([
    //         'success' => true,
    //         'data' => HoardingResource::collection($hoardings),
    //         'meta' => [
    //             'current_page' => $hoardings->currentPage(),
    //             'from' => $hoardings->firstItem(),
    //             'last_page' => $hoardings->lastPage(),
    //             'per_page' => $hoardings->perPage(),
    //             'to' => $hoardings->lastItem(),
    //             'total' => $hoardings->total(),
    //         ],
    //     ]);
    // }

    /**
     * Get all hoardings for the authenticated vendor (API)
     */
    public function index(Request $request)
    {
        $vendor = Auth::user();
        $filters = $request->only(['type', 'status', 'search']);
        $filters['vendor_id'] = $vendor->id;
        $perPage = $request->get('per_page', 20);
        $hoardings = $this->hoardingService->getAll($filters, $perPage);
        return response()->json($hoardings);
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
