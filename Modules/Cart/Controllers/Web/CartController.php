<?php

namespace Modules\Cart\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Services\CartService;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    /**
     * Add to cart (AJAX / Web)
     */
    public function add(Request $request)
    {
        $request->validate([
            'hoarding_id' => 'required|integer',
            'package_type' => 'nullable|string', // 👈 NEW (safe)
        ]);

        $result = $this->cartService->add(
            $request->hoarding_id,
            $request->package_type ?? 'monthly' // 👈 DEFAULT SAFE
        );

        if (($result['status'] ?? '') === 'login_required') {
            return response()->json([
                'success'  => false,
                'message'  => $result['message'],
                'redirect' => route('login'),
            ], 401);
        }

        return response()->json([
            'success' => in_array(($result['status'] ?? ''), ['success', 'added', true, 1], true),
            'message' => $result['message'] ?? 'Action completed',
            'price'   => $result['price'] ?? null, // 👈 OPTIONAL (useful for UI)
        ]);
    }

    /**
     * Cart count
     */
    public function index(CartService $cartService)
    {
        $items = $cartService->getUserCart();
        $summary = $cartService->getCartSummary();

        return view('cart.index', compact('items', 'summary'));
    }
    public function remove(Request $request)
    {
        $request->validate([
            'hoarding_id' => 'required|integer'
        ]);

        $this->cartService->remove($request->hoarding_id);

        return response()->json([
            'success' => true,
            'message' => 'Removed from cart'
        ]);
    }

}
