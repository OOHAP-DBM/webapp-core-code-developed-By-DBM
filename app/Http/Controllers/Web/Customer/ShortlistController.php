<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Cart\Services\CartService;

class ShortlistController extends Controller
{
    /**
     * Ensure only customers can access shortlist
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer']);
    }

    /**
     * Display customer's shortlist/wishlist.
     *
     * @return View
     */
    public function index(CartService $cartService): View
    {
        $cartIds = app(CartService::class)
        ->getCartHoardingIds();
        $wishlistCount = auth()->user()->wishlist()->count();
        $wishlist = auth()->user()
            ->wishlist()
            ->with('hoarding')
            ->latest()
            ->paginate(12);

        return view('customer.shortlist', compact('wishlist','cartIds','wishlistCount'));
    }

    /**
     * Add hoarding to wishlist.
     *
     * @param int $hoardingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(int $hoardingId)
    {
        auth()->user()->wishlist()->firstOrCreate([
            'hoarding_id' => $hoardingId
        ]);

        $count = auth()->user()->wishlist()->count();

        return response()->json([
            'success' => true,
            'message' => 'Added to shortlist',
            'count' => $count,
        ]);
    }

    /**
     * Remove hoarding from wishlist.
     *
     * @param int $hoardingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $hoardingId)
    {
        auth()->user()->wishlist()
            ->where('hoarding_id', $hoardingId)
            ->delete();

        $count = auth()->user()->wishlist()->count();

        return response()->json([
            'success' => true,
            'message' => 'Removed from shortlist',
            'count' => $count,
        ]);
    }

    /**
     * Clear all wishlist items.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        auth()->user()->wishlist()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shortlist cleared',
            'count' => 0,
        ]);
    }

    /**
     * Toggle wishlist status for a hoarding (PROMPT 50).
     *
     * @param int $hoardingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(int $hoardingId)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        if (auth()->user()->active_role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], 403);
        }
        try {
            $result = Wishlist::toggle(auth()->id(), $hoardingId);

            return response()->json([
                'success'      => true,
                'action'       => $result['action'], 
                'message'      => $result['action'] === 'added'
                                    ? 'Added to shortlist'
                                    : 'Removed from shortlist',
                'count'        => $result['count'],
                'isWishlisted' => $result['action'] === 'added',
            ]);

        } catch (\Throwable $e) {
            \Log::error('Wishlist toggle failed', [
                'user_id' => auth()->id(),
                'hoarding_id' => $hoardingId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if hoarding is in wishlist (PROMPT 50).
     *
     * @param int $hoardingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(int $hoardingId)
    {
        $isWishlisted = Wishlist::isInWishlist(auth()->id(), $hoardingId);

        return response()->json([
            'success' => true,
            'isWishlisted' => $isWishlisted,
        ]);
    }

    /**
     * Get wishlist count (PROMPT 50).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        $count = Wishlist::getCount(auth()->id());

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
}
