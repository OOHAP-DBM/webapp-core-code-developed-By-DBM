<?php

namespace Modules\Hoardings\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Hoardings\Http\Resources\HoardingResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Hoardings\Services\HoardingService;
use App\Models\Hoarding;
use Modules\Hoardings\Models\HoardingAttribute;
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
     *     summary="Get all hoarding categories/types(of live hoarding)",
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
    $categories = HoardingAttribute::query() // change table name if needed
        ->where('type', 'category')
        ->where('is_active', 1)
        ->orderBy('label')
        ->get([
            'label',
            'value',
        ]);

    return response()->json([
        'success' => true,
        'data' => $categories,
    ]);
}


       /**
     * @OA\Get(
     *     path="/hoardings/cities",
     *     operationId="getHoardingCities",
     *     tags={"Hoardings"},
     *     summary="Get cities with active hoardings ordered by count",
     *     description="Returns a list of cities with the number of active hoardings in each city, ordered by count descending.",
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
     *                     @OA\Property(property="city", type="string", example="Mumbai"),
     *                     @OA\Property(property="count", type="integer", example=42)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No cities found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No cities found")
     *         )
     *     )
     * )
     */
    public function getCitiesWithActiveHoardings(): JsonResponse
    {
        $cities = Hoarding::where('status', 'active')
            ->whereNotNull('city')
            ->select('city')
            ->selectRaw('count(*) as count')
            ->groupBy('city')
            ->orderByDesc('count')
            ->get();

        if ($cities->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No cities found',
            ], 404);
        }

        $result = $cities->map(function ($cityItem) {
            $hoardings = Hoarding::where('status', 'active')
                ->where('city', $cityItem->city)
                ->get();
            return [
                'city' => $cityItem->city,
                'count' => $cityItem->count,
                'hoardings' => HoardingResource::collection($hoardings),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }


    /**
     * @OA\Get(
     *     path="/hoardings/active",
     *     operationId="getOnlyActiveOOHAndDOOH",
     *     tags={"Hoardings"},
     *     summary="Get only active OOH & DOOH hoardings",
     *     description="Returns paginated list of active hoardings of type OOH and DOOH only",
     *     @OA\Parameter(
     *         name="hoarding_type",
     *         in="query",
     *         description="Filter by hoarding type (ooh or dooh)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ooh","dooh"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
     public function activeOOHAndDOOH(Request $request): JsonResponse
    {
        $hoardings = $this->hoardingService->getActiveOOHAndDOOH(
            $request->only(['hoarding_type', 'category']),
            (int) $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data'    => HoardingResource::collection($hoardings),
            'meta'    => [
                'current_page' => $hoardings->currentPage(),
                'per_page'     => $hoardings->perPage(),
                'total'        => $hoardings->total(),
                'last_page'    => $hoardings->lastPage(),
            ],
        ]);
    }

   /**
     * @OA\Delete(
     *     path="/hoardings/{id}",
     *     operationId="deleteHoarding",
     *     tags={"Hoardings"},
     *     summary="Delete a hoarding by ID (soft delete)",
     *     description="Soft deletes a hoarding and cascades to related child record (OOH hoarding or DOOH screen) along with their packages and media. Physical media files are deleted from storage.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Hoarding ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful deletion",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Hoarding deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hoarding not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hoarding not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error during deletion",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete hoarding")
     *         )
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $hoarding = Hoarding::find($id);
            
            if (!$hoarding) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hoarding not found',
                ], 404);
            }

            // Delete OOH hoarding and its related data (if exists)
            if ($hoarding->oohHoarding) {
                // Delete OOH brand logos from storage
                $hoarding->oohHoarding->brandLogos()->each(function ($logo) {
                    $this->deleteFileFromStorage($logo->logo_path);
                });
                $hoarding->oohHoarding->brandLogos()->delete();

                // Delete OOH packages
                $hoarding->oohHoarding->packages()->delete();

                // Soft delete OOH hoarding
                $hoarding->oohHoarding->delete();
            }

            // Delete DOOH screen and its related data (if exists)
            if ($hoarding->doohScreen) {
                // Delete DOOH media files from storage
                $hoarding->doohScreen->media()->each(function ($media) {
                    $this->deleteFileFromStorage($media->file_path);
                });
                $hoarding->doohScreen->media()->delete();

                // Delete DOOH brand logos from storage
                $hoarding->doohScreen->brandLogos()->each(function ($logo) {
                    $this->deleteFileFromStorage($logo->logo_path);
                });
                $hoarding->doohScreen->brandLogos()->delete();

                // Delete DOOH packages
                $hoarding->doohScreen->packages()->delete();

                // Delete DOOH slots
                $hoarding->doohScreen->slots()->delete();

                // Soft delete DOOH screen
                $hoarding->doohScreen->delete();
            }

            // Delete parent hoarding media files from storage (HoardingMedia)
            $hoarding->hoardingMedia()->each(function ($media) {
                $this->deleteFileFromStorage($media->file_path);
            });
            $hoarding->hoardingMedia()->delete();

            // Delete Spatie Media Library files and records
            $hoarding->clearMediaCollection('hero_image');
            $hoarding->clearMediaCollection('night_image');
            $hoarding->clearMediaCollection('gallery');
            $hoarding->clearMediaCollection('size_overlay');

            // Soft delete the parent hoarding
            $hoarding->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hoarding deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete hoarding', [
                'hoarding_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hoarding. Please try again.',
            ], 500);
        }
    }

    /**
     * Helper method to delete file from storage
     *
     * @param string|null $filePath
     * @return void
     */
    protected function deleteFileFromStorage(?string $filePath): void
    {
        if (!$filePath) {
            return;
        }

        // Clean the file path
        $cleanPath = ltrim($filePath, '/');
        
        // Remove 'storage/' prefix if present
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        // Check if file exists and delete
        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
            Log::info('Deleted file from storage', ['path' => $cleanPath]);
        }
    }

    /**
     * Optional: Force delete endpoint for admin use
     * This permanently deletes the hoarding and all related data
     *
     * @param int $id
     * @return JsonResponse
     */
    public function forceDestroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $hoarding = Hoarding::withTrashed()->find($id);
            
            if (!$hoarding) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hoarding not found',
                ], 404);
            }

            // Force delete OOH hoarding and related data
            if ($hoarding->oohHoarding()->withTrashed()->exists()) {
                $oohHoarding = $hoarding->oohHoarding()->withTrashed()->first();
                
                // Delete brand logos
                $oohHoarding->brandLogos()->withTrashed()->each(function ($logo) {
                    $this->deleteFileFromStorage($logo->logo_path);
                    $logo->forceDelete();
                });

                // Delete packages
                $oohHoarding->packages()->withTrashed()->forceDelete();

                // Force delete OOH hoarding
                $oohHoarding->forceDelete();
            }

            // Force delete DOOH screen and related data
            if ($hoarding->doohScreen()->withTrashed()->exists()) {
                $doohScreen = $hoarding->doohScreen()->withTrashed()->first();
                
                // Delete media files
                $doohScreen->media()->withTrashed()->each(function ($media) {
                    $this->deleteFileFromStorage($media->file_path);
                    $media->forceDelete();
                });

                // Delete brand logos
                $doohScreen->brandLogos()->withTrashed()->each(function ($logo) {
                    $this->deleteFileFromStorage($logo->logo_path);
                    $logo->forceDelete();
                });

                // Delete packages
                $doohScreen->packages()->withTrashed()->forceDelete();

                // Delete slots
                $doohScreen->slots()->withTrashed()->forceDelete();

                // Force delete DOOH screen
                $doohScreen->forceDelete();
            }

            // Delete hoarding media files
            $hoarding->hoardingMedia()->withTrashed()->each(function ($media) {
                $this->deleteFileFromStorage($media->file_path);
                $media->forceDelete();
            });

            // Delete Spatie media
            $hoarding->clearMediaCollection('hero_image');
            $hoarding->clearMediaCollection('night_image');
            $hoarding->clearMediaCollection('gallery');
            $hoarding->clearMediaCollection('size_overlay');

            // Force delete the parent hoarding
            $hoarding->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hoarding permanently deleted',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to force delete hoarding', [
                'hoarding_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete hoarding. Please try again.',
            ], 500);
        }
    }

}
