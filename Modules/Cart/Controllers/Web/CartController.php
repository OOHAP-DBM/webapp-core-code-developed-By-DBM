<?php

namespace Modules\Cart\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Services\CartService;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /* =====================================================
     | CART PAGE
     ===================================================== */
    public function index(CartService $cartService)
    {
        // dd($cartService->getCartForUI());
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
        if (!auth()->check()) {
            return response()->json([
                'status'  => 'login_required',
                'message' => 'Please login to add items to cart',
            ], 401);
        }

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
        if (!auth()->check()) {
            return response()->json([
                'status'  => 'login_required',
                'message' => 'Please login to remove items from cart',
            ], 401);
        }

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
    public function selectPackage(Request $request)
    {
        $request->validate([
            'hoarding_id'   => 'required|integer',
            'package_id'    => 'nullable|integer',
            'package_label' => 'nullable|string',
        ]);

        DB::table('carts')
        ->where('user_id', auth()->id())
        ->where('hoarding_id', $request->hoarding_id)
        ->update([
            'package_id'    => $request->package_id,
            'package_label' => $request->package_label,
            'updated_at'    => now(), 
        ]);

        return response()->json([
            'success' => true
        ]);
    }

}
