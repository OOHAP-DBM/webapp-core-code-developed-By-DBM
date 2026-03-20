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
    public function index(Request $request, CartService $cartService)
{
    // Guest user ke liye
    if (!auth()->check()) {
        $ids = $request->query('ids');

        if ($ids) {
            $idsArray = explode(',', $ids);
            $hoardings = \App\Models\Hoarding::whereIn('id', $idsArray)->get();
            // Map to cart row structure for buildCartItem
            $items = $hoardings->map(function($h) use ($cartService) {
                $item = (object) [
                    'hoarding_id'   => $h->id,
                    'title'         => $h->title,
                    'slug'          => $h->slug,
                    'city'          => $h->city,
                    'state'         => $h->state,
                    'locality'      => $h->locality,
                    'category'      => $h->category,
                    'hoarding_type' => $h->hoarding_type,
                    'monthly_price' => $h->monthly_price,
                    'base_monthly_price' => $h->base_monthly_price,
                    'grace_period_days'  => $h->grace_period_days,
                    'package_id'    => null,
                ];
                return $cartService->buildCartItem($item);
            });

            return view('cart.index', [
                'items'   => $items,
                'summary' => [
                    'count' => $items->count(),
                ],
            ]);
        }

        return view('cart.index', [
            'items'   => collect(),
            'summary' => ['count' => 0],
        ]);
    }

    // Logged in user
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
