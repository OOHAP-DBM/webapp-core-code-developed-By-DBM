<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Modules\Cart\Services\CartService;
use App\Http\Resources\WishlistResource;

class ShortlistController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:customer']);
    }

    public function index(): JsonResponse
    {
        $wishlist = auth()->user()->wishlist()
            ->with('hoarding')
            ->latest()
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => WishlistResource::collection($wishlist),
            'meta'    => [
                'total_count' => auth()->user()->wishlist()->count(),
            ]
        ]);
    }

    /**
     * Standard Store/Destroy/Toggle (JSON Optimized)
     */
    public function store(int $hoardingId): JsonResponse
    {
        auth()->user()->wishlist()->firstOrCreate(['hoarding_id' => $hoardingId]);
        return response()->json([
            'success' => true,
            'message' => 'Added to shortlist',
            'count'   => auth()->user()->wishlist()->count()
        ]);
    }

    public function destroy(int $hoardingId): JsonResponse
    {
        auth()->user()->wishlist()->where('hoarding_id', $hoardingId)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Removed from shortlist',
            'count'   => auth()->user()->wishlist()->count()
        ]);
    }

    public function toggle(int $hoardingId): JsonResponse
    {
        try {
            $result = Wishlist::toggle(auth()->id(), $hoardingId);
            return response()->json([
                'success'      => true,
                'action'       => $result['action'], 
                'isWishlisted' => $result['action'] === 'added',
                'count'        => $result['count'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }

    public function clear(): JsonResponse
    {
        auth()->user()->wishlist()->delete();
        return response()->json(['success' => true, 'message' => 'Cleared', 'count' => 0]);
    }

    public function count(): JsonResponse
    {
        return response()->json(['success' => true, 'count' => Wishlist::getCount(auth()->id())]);
    }
    
    public function check(int $hoardingId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'isWishlisted' => Wishlist::isInWishlist(auth()->id(), $hoardingId)
        ]);
    }
}
