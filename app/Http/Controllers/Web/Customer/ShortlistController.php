<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
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

        return response()->json([
            'success' => true,
            'message' => 'Added to shortlist'
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

        return response()->json([
            'success' => true,
            'message' => 'Removed from shortlist'
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
            'message' => 'Shortlist cleared'
        ]);
    }
}
