<?php

namespace Modules\Cart\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Cart\Services\CartService;

class CartController extends Controller
{
    /**
     * @OA\Post(
     *     path="/cart/add",
     *     tags={"Cart"},
     *     summary="Add hoarding to cart",
     *     description="Add a hoarding to the authenticated user's cart.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"hoarding_id"},
     *             @OA\Property(property="hoarding_id", type="integer", example=1),
     *             @OA\Property(property="package_id", type="integer", example=2),
     *             @OA\Property(property="package_name", type="string", example="package_name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Added to cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="added"),
     *             @OA\Property(property="in_cart", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Added to cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="login_required"),
     *             @OA\Property(property="message", type="string", example="Please login to add item to cart")
     *         )
     *     )
     * )
     */
    public function add(Request $request, CartService $cartService): JsonResponse
    {
        $data = $request->validate([
            'hoarding_id'    => 'required|integer',
            'package_id'     => 'nullable|integer',
            'package_name' => 'nullable|string',
        ]);
        $result = $cartService->add(
            $data['hoarding_id'],
            $data['package_id'] ?? null,
            $data['package_name'] ?? null
        );
        $status = ($result['status'] === 'login_required') ? 401 : 200;
        return response()->json($result, $status);
    }
}
