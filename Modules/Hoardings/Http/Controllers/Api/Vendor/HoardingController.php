<?php

namespace Modules\Hoardings\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Modules\Hoardings\Http\Resources\HoardingResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Hoardings\Services\HoardingService;
use Illuminate\Support\Facades\Validator;
use Modules\Hoardings\Models\HoardingAttribute;
use App\Models\Hoarding;
use Illuminate\Container\Attributes\Log;

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
     * @OA\Get(
     *     path="/hoardings/vendor",
     *     tags={"Vendor Hoardings"},
     *     summary="Get all hoardings for the authenticated vendor on search/filter",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of hoardings for the authenticated vendor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Hoarding")),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="next_page_url", type="string"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="prev_page_url", type="string"),
     *             @OA\Property(property="to", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
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
     * @OA\Get(
     *     path="/hoardings/vendor/{id}",
     *     tags={" Vendor Hoardings"},
     *     summary="Get  Vendor  Hoardingdetails by hoarding id",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/hoardings/categories",
     *     summary="Get all hoarding categories set by admin",
     *     tags={"Vendor Hoardings"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(
     *                         property="type",
     *                         type="string",
     *                         example="category"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="label",
     *                         type="string",
     *                         example="Unipole"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="value",
     *                         type="string",
     *                         example="unipole"
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getCategories()
    {
        $categories = HoardingAttribute::where('type', 'category')
            ->where('is_active', '1')
            ->get(['type', 'label', 'value']);

        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/hoardings/vendor/draft",
     *     tags={"Vendor Hoardings"},
     *     summary="Get vendor's draft hoardings",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Drafts fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Draft hoardings fetched."
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     )
     * )
     */

    public function getDrafts(Request $request): JsonResponse
    {
        \Log::info('Fetching draft hoardings for vendor', ['user_id' => $request->user()->id]);
        $user = $request->user(); // authenticated via sanctum

        // Safety check (role-based access)
        if (! $user->hasRole('vendor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $drafts = Hoarding::where('vendor_id', $user->id)
            ->where('status', 'draft')
            ->get();

        \Log::info('Draft Hoardings fetched', ['count' => $drafts->count()]);
        return response()->json([
            'success' => true,
            'message' => 'Draft hoardings fetched.',
            'data' => $drafts,
        ]);
    }


    /**
     * @OA\Get(
     *     path="/hoardings/vendor/all/{vendor_id}",
     *     tags={"Vendor Hoardings"},
     *     summary="Get all hoardings for a vendor",
     *     description="Returns all hoardings (OOH/DOOH) for a vendor with packages and brand logos.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor hoardings fetched",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor hoardings fetched."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="hoarding_type", type="string"),
     *                     @OA\Property(property="ooh", type="object", nullable=true),
     *                     @OA\Property(property="doohScreen", type="object", nullable=true),
     *                     @OA\Property(property="packages", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="brandLogos", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="vendor", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="No hoardings found")
     * )
     */

    public function showAllHoarding(int $vendorId): JsonResponse
    {
        $hoardings = Hoarding::with([
            'ooh.oohPackages',
            'ooh.oohBrandLogos',
            'doohScreen.doohPackages',
            'doohScreen.doohBrandLogos',
            'vendor',
        ])
            ->where('vendor_id', $vendorId)
            ->get();

        if ($hoardings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hoardings found for this vendor.',
                'data' => [],
            ], 404);
        }

        $data = $hoardings->map(function ($hoarding) {

            $packages = [];
            $brandLogos = [];

            if ($hoarding->hoarding_type === 'ooh' && $hoarding->ooh) {
                $packages = $hoarding->ooh->oohPackages;
                $brandLogos = $hoarding->ooh->oohBrandLogos;
            }

            if ($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen) {
                $packages = $hoarding->doohScreen->doohPackages;
                $brandLogos = $hoarding->doohScreen->doohBrandLogos;
            }

            return [
                'id' => $hoarding->id,
                'title' => $hoarding->title,
                'hoarding_type' => $hoarding->hoarding_type,
                'ooh' => $hoarding->ooh,
                'doohScreen' => $hoarding->doohScreen,
                'packages' => $packages,
                'brandLogos' => $brandLogos,
                'vendor' => $hoarding->vendor,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Vendor hoardings fetched.',
            'data' => $data,
        ]);
    }
}
