<?php

namespace Modules\Cart\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Services\CartService;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /* =====================================================
     | ADD / UPDATE CART
     ===================================================== */
      /**
     * @OA\Post(
     *   path="/cart/add",
     *   tags={"Cart"},
     *   summary="Add or update cart item",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"hoarding_id"},
     *       @OA\Property(property="hoarding_id", type="integer", example=10),
     *       @OA\Property(property="package_id", type="integer", nullable=true, example=2),
     *       @OA\Property(property="package_source", type="string", nullable=true, example="ooh_package")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Item added or updated",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/CartResponse"),
     *         @OA\Schema(
     *           @OA\Property(
     *             property="data",
     *             type="object",
     *             @OA\Property(property="in_cart", type="boolean", example=true),
     *             @OA\Property(property="final_price", type="number", example=3450),
     *             @OA\Property(property="cart_count", type="integer", example=2)
     *           )
     *         )
     *       }
     *     )
     *   )
     * )
     */
    public function add(Request $request)
    {
        $request->validate([
            'hoarding_id'    => 'required|integer',
            'package_id'     => 'nullable|integer',
            'package_source' => 'nullable|string',
        ]);

        $result = $this->cartService->add(
            $request->hoarding_id,
            $request->package_id,
            $request->package_source
        );

        return $this->apiResponse(
            success: true,
            status: $result['status'],
            message: $result['message'],
            data: [
                'in_cart'     => $result['in_cart'],
                'final_price' => $result['final_price'] ?? null,
                'cart_count'  => $this->cartCount(),
            ]
        );
    }

     /**
     * @OA\Delete(
     *   path="/cart/remove/{hoardingId}",
     *   tags={"Cart"},
     *   summary="Remove item from cart",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="hoardingId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Item removed",
     *     @OA\JsonContent(ref="#/components/schemas/CartResponse")
     *   )
     * )
     */
    public function remove(int $hoardingId)
    {
        $result = $this->cartService->remove($hoardingId);

        return $this->apiResponse(
            success: true,
            status: $result['status'],
            message: $result['message'],
            data: [
                'in_cart'    => false,
                'cart_count'=> $this->cartCount(),
            ]
        );
    }

    /**
     * @OA\Get(
     *   path="/cart/count",
     *   tags={"Cart"},
     *   summary="Get cart item count",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Cart count",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/CartResponse"),
     *         @OA\Schema(
     *           @OA\Property(
     *             property="data",
     *             @OA\Property(property="cart_count", type="integer", example=3)
     *           )
     *         )
     *       }
     *     )
     *   )
     * )
     */
    public function count()
    {
        return $this->apiResponse(
            success: true,
            status: 'ok',
            data: [
                'cart_count' => $this->cartCount(),
            ]
        );
    }

    /**
     * @OA\Get(
     *   path="/cart/list",
     *   tags={"Cart"},
     *   summary="Get cart items (UI)",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Cart item list",
     *     @OA\JsonContent(ref="#/components/schemas/CartResponse")
     *   )
     * )
     */
    public function list()
    {
        $items = $this->cartService->getCartForUI();

        return $this->apiResponse(
            success: true,
            status: 'ok',
            data: [
                'items'      => $items,
                'cart_count'=> count($items),
            ]
        );
    }

    /* ================= HELPERS ================= */

    private function cartCount(): int
    {
        return Auth::check()
            ? count($this->cartService->getCartHoardingIds())
            : 0;
    }

    private function apiResponse(
        bool $success,
        string $status,
        string $message = '',
        array $data = []
    ) {
        return response()->json([
            'success' => $success,
            'status'  => $status,
            'data'    => (object) $data,
            'message' => $message,
        ]);
    }
}
