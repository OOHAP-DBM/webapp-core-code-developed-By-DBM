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
        $this->middleware(['auth:sanctum'])->except(['index']);
    }

    public function index(): JsonResponse
    {
        // Manually Sanctum token parse karo
        $user = null;
        try {
            auth()->shouldUse('sanctum');
            $user = auth('sanctum')->user();
        } catch (\Exception $e) {
            $user = null;
        }

        if ($user) {
            // ── LOGGED IN USER ──────────────────────────────────────
            $wishlist = $user->wishlist()
                ->whereHas('hoarding', function ($q) {
                    $q->where('status', \App\Models\Hoarding::STATUS_ACTIVE)
                    ->whereNull('deleted_at');
                })
                ->with([
                    'hoarding:id,title,city,hoarding_type,category,monthly_price,base_monthly_price',
                    'hoarding.ooh:id,hoarding_id,width,height,measurement_unit',
                    'hoarding.doohScreen:id,hoarding_id,width,height,measurement_unit,price_per_slot',
                    'hoarding.hoardingMedia:id,hoarding_id,file_path,is_primary',
                    'hoarding.packages:id,hoarding_id,package_name,discount_percent,min_booking_duration,duration_unit,is_active',
                    'hoarding.doohScreen.media:id,dooh_screen_id,file_path',
                    'hoarding.doohScreen.packages:id,dooh_screen_id,package_name,discount_percent,min_booking_duration,duration_unit,is_active',
                ])
                ->latest()
                ->paginate(12);

        } else {
            // ── GUEST USER ──────────────────────────────────────────
            $ids = array_filter(array_map('intval', explode(',', request()->query('ids', ''))));
            $wishlist = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);

            if (!empty($ids)) {
                $allItems = \App\Models\Hoarding::whereIn('id', $ids)
                    ->where('status', \App\Models\Hoarding::STATUS_ACTIVE)
                    ->whereNull('deleted_at')
                    ->with([
                        'ooh:id,hoarding_id,width,height,measurement_unit',
                        'doohScreen:id,hoarding_id,width,height,measurement_unit,price_per_slot',
                        'hoardingMedia:id,hoarding_id,file_path,is_primary',
                        'packages:id,hoarding_id,package_name,discount_percent,min_booking_duration,duration_unit,is_active',
                        'doohScreen.media:id,dooh_screen_id,file_path',
                        'doohScreen.packages:id,dooh_screen_id,package_name,discount_percent,min_booking_duration,duration_unit,is_active',
                    ])
                    ->get()
                    ->map(fn($hoarding) => (object)[
                        'id'         => $hoarding->id,
                        'hoarding'   => $hoarding,
                        'created_at' => now(),
                    ]);

                $page    = request()->query('page', 1);
                $perPage = 12;
                $offset  = ($page - 1) * $perPage;

                $wishlist = new \Illuminate\Pagination\LengthAwarePaginator(
                    $allItems->slice($offset, $perPage)->values(),
                    $allItems->count(),
                    $perPage,
                    $page,
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            }
        }

        return response()->json([
            'success' => true,
            'data'    => WishlistResource::collection($wishlist),
            'meta'    => ['total_count' => $wishlist->total()],
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
