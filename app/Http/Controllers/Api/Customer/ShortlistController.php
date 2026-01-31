<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Modules\Cart\Services\CartService;

class ShortlistController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:customer']);
    }

    /**
     * Get wishlist
     */
    public function index(CartService $cartService)
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'wishlist' => $user->wishlist()
                    ->with('hoarding')
                    ->latest()
                    ->paginate(12),
                'wishlistCount' => $user->wishlist()->count(),
                'cartIds' => $cartService->getCartHoardingIds(),
            ]
        ]);
    }

    /**
     * Add to wishlist
     */
    public function store(int $hoardingId)
    {
        auth()->user()->wishlist()->firstOrCreate([
            'hoarding_id' => $hoardingId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'count' => auth()->user()->wishlist()->count(),
        ]);
    }

    /**
     * Remove from wishlist
     */
    public function destroy(int $hoardingId)
    {
        auth()->user()->wishlist()
            ->where('hoarding_id', $hoardingId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from Wishlist',
            'count' => auth()->user()->wishlist()->count(),
        ]);
    }

    /**
     * Clear wishlist
     */
    public function clear()
    {
        auth()->user()->wishlist()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared',
            'count' => 0,
        ]);
    }

    /**
     * Toggle wishlist
     */
    public function toggle(int $hoardingId)
    {
        try {
            $result = Wishlist::toggle(auth()->id(), $hoardingId);

            return response()->json([
                'success' => true,
                'action' => $result['action'],
                'message' => $result['action'] === 'added'
                    ? 'Added to wishlist'
                    : 'Removed from wishlist',
                'count' => $result['count'],
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
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Check wishlist
     */
    public function check(int $hoardingId)
    {
        return response()->json([
            'success' => true,
            'isWishlisted' => Wishlist::isInWishlist(auth()->id(), $hoardingId),
        ]);
    }

    /**
     * Wishlist count
     */
    public function count()
    {
        return response()->json([
            'success' => true,
            'count' => Wishlist::getCount(auth()->id()),
        ]);
    }
}