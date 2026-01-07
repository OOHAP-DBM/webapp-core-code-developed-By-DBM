<?php

namespace Modules\Hoardings\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Hoardings\Services\HoardingListService;
use Modules\Hoardings\Models\OOHHoarding;
use Modules\Hoardings\Http\Requests\StoreOOHHoardingStep1Request;
use Modules\Hoardings\Http\Requests\StoreOOHHoardingStep2Request;
use Modules\Hoardings\Http\Requests\StoreOOHHoardingStep3Request;


class OOHListingController extends Controller
{
    protected HoardingListService $service;

    public function __construct(HoardingListService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ooh/step-1",
     *     tags={"OOH Hoardings"},
     *     summary="Create OOH Hoarding - Step 1",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OOHStep1Request")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function storeStep1(StoreOOHHoardingStep1Request $request): JsonResponse
    {
        $vendor = Auth::guard('vendor')->user();
        $data = $request->validated();
        $mediaFiles = $request->file('media', []);
        $result = $this->service->storeStep1($vendor, $data, $mediaFiles);

        return response()->json([
            'success' => true,
            'message' => 'Step 1 completed.',
            'data' => $result,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ooh/step-2/{ooh_id}",
     *     tags={"OOH Hoardings"},
     *     summary="Update OOH Hoarding - Step 2",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="ooh_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OOHStep2Request")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function storeStep2(StoreOOHHoardingStep2Request $request, $ooh_id): JsonResponse
    {
        $ooh = OOHHoarding::findOrFail($ooh_id);
        $data = $request->validated();
        $brandLogoFiles = $request->file('brand_logos', []);
        $result = $this->service->storeStep2($ooh, $data, $brandLogoFiles);

        return response()->json([
            'success' => true,
            'message' => 'Step 2 completed.',
            'data' => $result,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ooh/step-3/{ooh_id}",
     *     tags={"OOH Hoardings"},
     *     summary="Update OOH Hoarding - Step 3",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="ooh_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OOHStep3Request")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function storeStep3(StoreOOHHoardingStep3Request $request, $ooh_id): JsonResponse
    {
        $ooh = OOHHoarding::findOrFail($ooh_id);
        $data = $request->validated();
        $result = $this->service->storeStep3($ooh, $data);

        return response()->json([
            'success' => true,
            'message' => 'Step 3 completed.',
            'data' => $result,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ooh/draft",
     *     tags={"OOH Hoardings"},
     *     summary="Get vendor's draft hoardings",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Drafts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getDrafts(Request $request): JsonResponse
    {
        $vendor = Auth::guard('vendor')->user();
        $drafts = OOHHoarding::whereHas('hoarding', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->where('status', 'draft');
        })->get();

        return response()->json([
            'success' => true,
            'message' => 'Draft hoardings fetched.',
            'data' => $drafts,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ooh/{id}",
     *     tags={"OOH Hoardings"},
     *     summary="Get OOH Hoarding details",
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
    public function show($id): JsonResponse
    {
        $ooh = OOHHoarding::with(['hoarding', 'packages', 'brandLogos'])->find($id);

        if (!$ooh) {
            return response()->json([
                'success' => false,
                'message' => 'Hoarding not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hoarding details fetched.',
            'data' => $ooh,
        ]);
    }
}
