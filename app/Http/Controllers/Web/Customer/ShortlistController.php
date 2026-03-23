<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Cart\Services\CartService;

class ShortlistController extends Controller
{
    public function __construct()
    {
        // index — guest bhi dekh sakta hai (JS LocalStorage IDs query string mein bhejega)
        // toggle, check, count — sirf logged in (guest JS handle karega)
        $this->middleware(['auth'])->only(['store', 'destroy', 'clear', 'toggle', 'check', 'count']);
    }

    /**
     * Shortlist page.
     * Logged in  → DB se wishlist
     * Guest      → JS LocalStorage IDs query string mein bhejega (?ids=1,2,3)
     */
    public function index(Request $request, CartService $cartService): View
    {
        $cartIds = app(CartService::class)->getCartHoardingIds();

        // Availability service
        $availabilityService = app(\Modules\Hoardings\Services\HoardingAvailabilityService::class);
        $today = now()->toDateString();

        if (auth()->check()) {
            // ─── Logged in user ───────────────────────────────────
            $wishlistCount = auth()->user()->wishlist()->count();
            $wishlist      = auth()->user()
                ->wishlist()
                ->whereHas('hoarding', function ($q) {
                    $q->where('status', \App\Models\Hoarding::STATUS_ACTIVE)
                      ->whereNull('deleted_at');
                })
                ->with('hoarding')
                ->latest()
                ->paginate(12);

            // Add availability info to each hoarding
            $wishlist->getCollection()->transform(function ($item) use ($availabilityService, $today) {
                $hoarding = $item->hoarding;
                if ($hoarding) {
                    $calendar = $availabilityService->getAvailabilityCalendar($hoarding->id, $today, $today);
                    $todayStatus = $calendar['calendar'][0]['status'] ?? 'unknown';
                    $hoarding->today_availability_status = $todayStatus;
                    if ($todayStatus !== 'available') {
                        $next = $availabilityService->getNextAvailableDates($hoarding->id, 1, $today);
                        $hoarding->next_available_date = $next['dates'][0]['date'] ?? null;
                    } else {
                        $hoarding->next_available_date = null;
                    }
                }
                return $item;
            });

        } else {
            $ids           = array_filter(array_map('intval', explode(',', $request->query('ids', ''))));
            $wishlistCount = 0;
            $wishlist      = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);

            if (!empty($ids)) {
                $query = \App\Models\Hoarding::whereIn('id', $ids)
                    ->where('status', \App\Models\Hoarding::STATUS_ACTIVE)
                    ->whereNull('deleted_at');

                $wishlistCount = $query->count();

                // Blade $item->hoarding expect karta hai
                // Isliye fake wrapper objects banao
                $allItems = $query->get()->map(function ($hoarding) use ($availabilityService, $today) {
                    // Add availability info
                    $calendar = $availabilityService->getAvailabilityCalendar($hoarding->id, $today, $today);
                    $todayStatus = $calendar['calendar'][0]['status'] ?? 'unknown';
                    $hoarding->today_availability_status = $todayStatus;
                    if ($todayStatus !== 'available') {
                        $next = $availabilityService->getNextAvailableDates($hoarding->id, 1, $today);
                        $hoarding->next_available_date = $next['dates'][0]['date'] ?? null;
                    } else {
                        $hoarding->next_available_date = null;
                    }
                    return (object) ['hoarding' => $hoarding];
                });

                $page    = $request->query('page', 1);
                $perPage = 12;
                $offset  = ($page - 1) * $perPage;

                $wishlist = new \Illuminate\Pagination\LengthAwarePaginator(
                    $allItems->slice($offset, $perPage)->values(),
                    $wishlistCount,
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            }
        }

        return view('customer.shortlist', compact('wishlist', 'cartIds', 'wishlistCount'));
    }

    public function store(int $hoardingId)
    {
        $hoarding = \App\Models\Hoarding::where('id', $hoardingId)->whereNull('deleted_at')->first();
        if (!$hoarding) {
            return response()->json(['success' => false, 'message' => 'Hoarding does not exist or has been deleted'], 404);
        }

        auth()->user()->wishlist()->firstOrCreate(['hoarding_id' => $hoardingId]);
        $count = auth()->user()->wishlist()->count();

        return response()->json(['success' => true, 'message' => 'Added to shortlist', 'count' => $count]);
    }

    public function destroy(int $hoardingId)
    {
        auth()->user()->wishlist()->where('hoarding_id', $hoardingId)->delete();
        $count = auth()->user()->wishlist()->count();

        return response()->json(['success' => true, 'message' => 'Removed from shortlist', 'count' => $count]);
    }

    public function clear()
    {
        auth()->user()->wishlist()->delete();

        return response()->json(['success' => true, 'message' => 'Shortlist cleared', 'count' => 0]);
    }

    public function toggle(int $hoardingId)
    {
        // Guest JS LocalStorage handle karta hai — yahan sirf logged in
        $hoarding = \App\Models\Hoarding::where('id', $hoardingId)->whereNull('deleted_at')->first();
        if (!$hoarding) {
            return response()->json(['success' => false, 'message' => 'Hoarding not found'], 404);
        }

        try {
            $result = Wishlist::toggle(auth()->id(), $hoardingId);

            return response()->json([
                'success'      => true,
                'action'       => $result['action'],
                'message'      => $result['action'] === 'added' ? 'Added to shortlist' : 'Removed from shortlist',
                'count'        => $result['count'],
                'isWishlisted' => $result['action'] === 'added',
            ]);

        } catch (\Throwable $e) {
            \Log::error('Wishlist toggle failed', [
                'user_id'     => auth()->id(),
                'hoarding_id' => $hoardingId,
                'error'       => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    public function check(int $hoardingId)
    {
        $isWishlisted = Wishlist::isInWishlist(auth()->id(), $hoardingId);

        return response()->json(['success' => true, 'isWishlisted' => $isWishlisted]);
    }

    public function count()
    {
        $count = Wishlist::getCount(auth()->id());

        return response()->json(['success' => true, 'count' => $count]);
    }
}