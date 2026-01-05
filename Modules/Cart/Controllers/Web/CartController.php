<?php

namespace Modules\Cart\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Services\CartService;

class CartController extends Controller
{
    /* =====================================================
     | CART PAGE
     ===================================================== */
    public function index(CartService $cartService)
    {
        return view('cart.index', [
            'items'   => $cartService->getCartForUI(),
            'summary' => $cartService->getCartSummary(),
        ]);
    }

    /* =====================================================
     | ADD TO CART
     ===================================================== */
    public function add(Request $request, CartService $cartService)
    {
        $data = $request->validate([
            'hoarding_id'    => 'required|integer',
            'package_id'     => 'nullable|integer',
            'package_source' => 'nullable|string|in:ooh_package,dooh_package,slot',
        ]);

        return response()->json(
            $cartService->add(
                $data['hoarding_id'],
                $data['package_id'] ?? null,
                $data['package_source'] ?? null
            )
        );
    }

    /* =====================================================
     | REMOVE FROM CART
     ===================================================== */
    public function remove(Request $request, CartService $cartService)
    {
        $data = $request->validate([
            'hoarding_id' => 'required|integer',
        ]);

        // direct remove (no exists logic)
        $cartService->remove($data['hoarding_id']);

        return response()->json([
            'status'  => 'removed',
            'in_cart' => false,          
            'message' => 'Item removed from cart',
        ]);
    }

}
