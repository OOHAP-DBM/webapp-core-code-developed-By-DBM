<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DOOH\Models\DOOHScreen;

class SearchController extends Controller
{
    /**
     * Display search page with filters
     *
     * @param Request $request
     * @return View
     */
    // public function index(Request $request): View
    // {
    //     $query = Hoarding::where('status', 'active')
    //         ->with(['vendor']);

    //     // Apply filters
    //     if ($request->filled('type')) {
    //         $query->where('type', $request->type);
    //     }

    //     if ($request->filled('min_price')) {
    //         $query->where('monthly_price', '>=', $request->min_price);
    //     }

    //     if ($request->filled('max_price')) {
    //         $query->where('monthly_price', '<=', $request->max_price);
    //     }

    //     if ($request->filled('search')) {
    //         $query->where(function ($q) use ($request) {
    //             $q->where('title', 'like', '%' . $request->search . '%')
    //               ->orWhere('description', 'like', '%' . $request->search . '%')
    //               ->orWhere('address', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     // Location-based search
    //     if ($request->filled('lat') && $request->filled('lng')) {
    //         $lat = $request->lat;
    //         $lng = $request->lng;
    //         $radius = $request->radius ?? 10; // Default 10km

    //         $query->selectRaw("
    //             *, ( 6371 * acos( cos( radians(?) ) *
    //             cos( radians( lat ) ) *
    //             cos( radians( lng ) - radians(?) ) +
    //             sin( radians(?) ) *
    //             sin( radians( lat ) ) ) ) AS distance
    //         ", [$lat, $lng, $lat])
    //         ->having('distance', '<', $radius)
    //         ->orderBy('distance');
    //     } else {
    //         $query->latest();
    //     }

    //     $hoardings = $query->paginate(12);

    //     // Get filter options
    //     $types = Hoarding::where('status', 'active')
    //         ->distinct()
    //         ->pluck('type')
    //         ->filter()
    //         ->sort()
    //         ->values();

    //     return view('search.index', compact('hoardings', 'types'));
    // }
    public function index(Request $request): View
    {
        /* ===============================
        | HOARDINGS
        =============================== */
        $hoardingsQuery = Hoarding::query()
            ->where('status', 'active')
            ->select([
                'id',
                \DB::raw('NULL as title'),
                'address',
                'city',
                'latitude as lat',
                'longitude as lng',
                'monthly_price as price',
                \DB::raw('NULL as available_from'),
                'created_at',
                \DB::raw("'hoarding' as media_type"),
            ]);

        /* ===============================
        | DOOH SCREENS
        =============================== */
        $doohQuery = DoohScreen::query()
            ->where('status', 'active')
            ->select([
                'id',
                'name as title',
                'address',
                'city',
                'lat',
                'lng',
                'price_per_month as price',
                \DB::raw('NULL as available_from'),
                'created_at',
                \DB::raw("'dooh' as media_type"),
            ]);

        /* ===============================
        | LOCATION SEARCH
        =============================== */
        if ($request->filled('location')) {
            $location = $request->location;

            $hoardingsQuery->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                ->orWhere('address', 'like', "%{$location}%");
            });

            $doohQuery->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                ->orWhere('address', 'like', "%{$location}%");
            });
        }

        /* ===============================
        | UNION (SAFE WAY)
        =============================== */
        $unionQuery = $hoardingsQuery->unionAll($doohQuery);

        /* ===============================
        | PAGINATION (SUBQUERY)
        =============================== */
        $results = \DB::query()
            ->fromSub($unionQuery, 'media')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('search.index', compact('results'));
    }
}
