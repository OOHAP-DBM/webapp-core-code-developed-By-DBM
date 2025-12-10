<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShortlistController extends Controller
{
    /**
     * Display customer's shortlist/wishlist.
     *
     * @return View
     */
    public function index(): View
    {
        $wishlist = auth()->user()
            ->wishlist()
            ->with('hoarding')
            ->latest()
            ->paginate(12);

        return view('customer.shortlist', compact('wishlist'));
    }

    /**
     * Add hoarding to wishlist.
     *
     * @param int $hoardingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(int $hoardingId)
    {
        auth()->user()->wishlist()->create([
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
        $result = Wishlist::toggle(auth()->id(), $hoardingId);

        return response()->json([
            'success' => true,
            'action' => $result['action'],
            'message' => $result['action'] === 'added' 
                ? 'Added to shortlist' 
                : 'Removed from shortlist',
            'count' => $result['count'],
            'isWishlisted' => $result['action'] === 'added',
        ]);
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
