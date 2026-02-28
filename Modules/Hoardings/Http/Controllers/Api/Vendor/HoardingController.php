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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
    // public function destroy(int $id): JsonResponse
    // {
    //     $hoarding = $this->hoardingService->getById($id);

    //     if (!$hoarding) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Hoarding not found.',
    //         ], 404);
    //     }

    //     // Check ownership
    //     if ($hoarding->vendor_id !== auth()->id() && !auth()->user()->hasRole(['super_admin', 'admin'])) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized action.',
    //         ], 403);
    //     }

    //     $this->hoardingService->delete($id);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Hoarding deleted successfully.',
    //     ]);
    // }


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
            'media', // Spatie MediaLibrary for hero_image
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
            $imageUrl = null;

            // Try primaryMediaItem() (OOH: HoardingMedia, DOOH: DOOHScreenMedia)
            $primaryMedia = method_exists($hoarding, 'primaryMediaItem') ? $hoarding->primaryMediaItem() : null;
            if ($primaryMedia && isset($primaryMedia->file_path)) {
                $imageUrl = asset('storage/' . ltrim($primaryMedia->file_path, '/'));
            }

            // If still null, try Spatie hero_image
            if (!$imageUrl && method_exists($hoarding, 'getFirstMediaUrl')) {
                $hero = $hoarding->getFirstMediaUrl('hero_image');
                if ($hero) {
                    $imageUrl = $hero;
                }
            }

            // If still null, try brand logo (OOH or DOOH)
            if (!$imageUrl) {
                if ($hoarding->hoarding_type === 'ooh' && $hoarding->ooh) {
                    $brandLogos = $hoarding->ooh->oohBrandLogos;
                } elseif ($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen) {
                    $brandLogos = $hoarding->doohScreen->doohBrandLogos;
                }
                if (!empty($brandLogos) && isset($brandLogos[0]['file_path'])) {
                    $imageUrl = asset('storage/' . ltrim($brandLogos[0]['file_path'], '/'));
                }
            }

            // Always set packages/brandLogos for response
            if ($hoarding->hoarding_type === 'ooh' && $hoarding->ooh) {
                $packages = $hoarding->ooh->oohPackages;
                $brandLogos = $hoarding->ooh->oohBrandLogos;
            } elseif ($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen) {
                $packages = $hoarding->doohScreen->doohPackages;
                $brandLogos = $hoarding->doohScreen->doohBrandLogos;
            }

            return [
                'id' => $hoarding->id,
                'status' => $hoarding->status,
                'title' => $hoarding->title,
                'status' => $hoarding->status,
                'hoarding_type' => $hoarding->hoarding_type,
                'ooh' => $hoarding->ooh,
                'doohScreen' => $hoarding->doohScreen,
                'packages' => $packages,
                'brandLogos' => $brandLogos,
                'vendor' => $hoarding->vendor,
                'image_url' => $imageUrl,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Vendor hoardings fetched.',
            'data' => $data,
        ]);
    }


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
            if ($hoarding->vendor_id !== auth()->id() && !auth()->user()->hasRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
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


        /**
     * @OA\Post(
     *     path="/hoardings/{id}/activate",
     *     operationId="activateHoarding",
     *     tags={"Hoardings"},
     *     summary="Activate a hoarding",
     *     description="Set hoarding status to active. Not allowed if status is pending_approval or suspended.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Hoarding ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Hoarding activated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Not allowed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot activate hoarding in this status.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hoarding not found")
     *         )
     *     )
     * )
     */
    public function activate($id): JsonResponse
    {
        $hoarding = Hoarding::find($id);
        if (!$hoarding) {
            return response()->json([
                'success' => false,
                'message' => 'Hoarding not found',
            ], 404);
        }
        if ($hoarding->vendor_id !== auth()->id() && !auth()->user()->hasRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
    }

        if (in_array($hoarding->status, ['pending_approval', 'suspended', 'active', 'draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot activate hoarding while it is in ' . $hoarding->status . ' status.',
            ], 400);
        }
        $hoarding->status = 'active';
        $hoarding->save();
        return response()->json([
            'success' => true,
            'message' => 'Hoarding activated successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/hoardings/{id}/deactivate",
     *     operationId="deactivateHoarding",
     *     tags={"Hoardings"},
     *     summary="Deactivate a hoarding",
     *     description="Set hoarding status to inactive. Not allowed if status is pending_approval or suspended.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Hoarding ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deactivated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Hoarding deactivated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Not allowed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot deactivate hoarding in this status.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Hoarding not found")
     *         )
     *     )
     * )
     */
    public function deactivate($id): JsonResponse
    {
        $hoarding = Hoarding::find($id);
        if (!$hoarding) {
            return response()->json([
                'success' => false,
                'message' => 'Hoarding not found',
            ], 404);
        }
        if ($hoarding->vendor_id !== auth()->id() && !auth()->user()->hasRole(['super_admin', 'admin'])) {
                return response()->json([
                    'success' => false,
                    '$hoarding->vendor_id' => $hoarding->vendor_id,
                    'auth_id' => auth()->id(),
                    'message' => 'Unauthorized action.',
                ], 403);
            }
            
        if (in_array($hoarding->status, ['pending_approval', 'suspended','draft', 'inactive'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate hoarding while it is in ' . $hoarding->status . ' status.',
            ], 400);
        }
        $hoarding->status = 'inactive';
        $hoarding->save();
        return response()->json([
            'success' => true,
            'message' => 'Hoarding deactivated successfully',
        ]);
    }
}
