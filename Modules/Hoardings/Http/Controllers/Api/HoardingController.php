<?php

namespace Modules\Hoardings\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HoardingResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Hoardings\Services\HoardingService;
use App\Models\Hoarding;

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
     * @OA\Get(
     *     path="/hoardings",
     *     operationId="getActiveHoardings",
     *     tags={"Hoardings"},
     *     summary="Get active hoardings for homepage display",
     *     description="Returns a paginated list of active and approved hoardings to display on the homepage. Supports filtering by location, type, and other criteria.",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, example=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="hoarding_type",
     *         in="query",
     *         description="Filter by hoarding type (ooh or dooh)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ooh", "dooh"}, example="ooh")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by hoarding category",
     *         required=false,
     *         @OA\Schema(type="string", example="billboard")
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filter by city",
     *         required=false,
     *         @OA\Schema(type="string", example="Mumbai")
     *     ),
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         description="Filter by state",
     *         required=false,
     *         @OA\Schema(type="string", example="Maharashtra")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price per day",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=1000)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price per day",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=50000)
     *     ),
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         description="Latitude for location-based search",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=19.0760)
     *     ),
     *     @OA\Parameter(
     *         name="lng",
     *         in="query",
     *         description="Longitude for location-based search",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=72.8777)
     *     ),
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Search radius in kilometers (used with lat/lng)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", default=10, example=5)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keyword for title or description",
     *         required=false,
     *         @OA\Schema(type="string", example="Premium Billboard")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"price", "created_at", "title", "rating"}, default="created_at", example="price")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc", example="asc")
     *     ),
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Filter only featured hoardings",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Premium Billboard - Mumbai Central"),
     *                     @OA\Property(property="description", type="string", example="High-traffic location near railway station"),
     *                     @OA\Property(property="hoarding_type", type="string", example="ooh"),
     *                     @OA\Property(property="category", type="string", example="billboard"),
     *                     @OA\Property(property="width", type="number", format="float", example=20),
     *                     @OA\Property(property="height", type="number", format="float", example=10),
     *                     @OA\Property(property="monthly_price", type="number", format="float", example=5000),
     *                     @OA\Property(property="city", type="string", example="Mumbai"),
     *                     @OA\Property(property="state", type="string", example="Maharashtra"),
     *                     @OA\Property(property="latitude", type="number", format="float", example=19.0760),
     *                     @OA\Property(property="longitude", type="number", format="float", example=72.8777),
     *                     @OA\Property(property="address", type="string", example="Near Mumbai Central Railway Station"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="is_featured", type="boolean", example=true),
     *                     @OA\Property(
     *                         property="media",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="url", type="string", example="https://example.com/storage/hoardings/image.jpg"),
     *                             @OA\Property(property="type", type="string", example="image")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="vendor",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="ABC Media Solutions"),
     *                         @OA\Property(property="company_name", type="string", example="ABC Media Pvt Ltd")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid parameters"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="lat", type="array", @OA\Items(type="string", example="The latitude must be a valid number"))
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'vendor_id' => $request->input('vendor_id'),
            'hoarding_type' => $request->input('hoarding_type'), // ooh or dooh
            'category' => $request->input('category'), // billboard, digital, transit, etc.
            'status' => 'active', // Only show active (approved) hoardings
            'search' => $request->input('search'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'radius' => $request->input('radius', 10),
            'featured' => $request->input('featured'),
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
     * @OA\Get(
     *     path="/hoardings/{id}",
     *     operationId="getHoardingById",
     *     tags={"Hoardings"},
     *     summary="Get hoarding details by ID",
     *     description="Returns detailed information about a specific hoarding",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Hoarding ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Premium Billboard - Mumbai Central"),
     *                 @OA\Property(property="description", type="string", example="High-traffic location near railway station"),
     *                 @OA\Property(property="hoarding_type", type="string", example="ooh"),
     *                 @OA\Property(property="category", type="string", example="billboard"),
     *                 @OA\Property(property="width", type="number", format="float", example=20),
     *                 @OA\Property(property="height", type="number", format="float", example=10),
     *                 @OA\Property(property="monthly_price", type="number", format="float", example=5000),
     *                 @OA\Property(property="city", type="string", example="Mumbai"),
     *                 @OA\Property(property="state", type="string", example="Maharashtra"),
     *                 @OA\Property(property="address", type="string", example="Near Mumbai Central Railway Station"),
     *                 @OA\Property(property="status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hoarding not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hoarding not found")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $hoarding = $this->hoardingService->getById($id);

        if (!$hoarding) {
            return response()->json([
                'success' => false,
                'message' => 'Hoarding not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new HoardingResource($hoarding),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/hoardings/live-categories",
     *     operationId="getHoardingCategories",
     *     tags={"Hoardings"},
     *     summary="Get all hoarding categories/types",
     *     description="Returns a list of available hoarding categories and types for filtering",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(type="string", example="billboard")
     *                 ),
     *                 @OA\Property(
     *                     property="types",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="value", type="string", example="billboard"),
     *                         @OA\Property(property="label", type="string", example="Billboard"),
     *                         @OA\Property(property="count", type="integer", example=45)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getLiveCategories(): JsonResponse
    {
        $categories = Hoarding::where('status', 'active')
            // ->where('approval_status', 'approved')
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->toArray();

        $typesWithCount = Hoarding::where('status', 'active')
            ->where('approval_status', 'approved')
            ->select('type')
            ->selectRaw('count(*) as count')
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->type,
                    'label' => ucfirst($item->type),
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'types' => $typesWithCount,
            ],
        ]);
    }
}
