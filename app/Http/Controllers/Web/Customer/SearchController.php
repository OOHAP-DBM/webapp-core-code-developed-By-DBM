<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Display search results.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = \App\Models\Hoarding::where('status', 'approved');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        // City filter
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // State filter
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Type filter
        if ($request->filled('type')) {
            $types = is_array($request->type) ? $request->type : [$request->type];
            $query->whereIn('type', $types);
        }

        // Illumination filter
        if ($request->filled('illumination')) {
            $query->where('illumination_type', $request->illumination);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price_per_month', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_month', '<=', $request->max_price);
        }

        // Size filters
        if ($request->filled('min_width')) {
            $query->where('width', '>=', $request->min_width);
        }
        if ($request->filled('min_height')) {
            $query->where('height', '>=', $request->min_height);
        }

        // Nearby filter (geofencing)
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->radius ?? 10; // km
            
            $query->selectRaw("
                *, ( 6371 * acos( cos( radians(?) ) *
                cos( radians( latitude ) ) *
                cos( radians( longitude ) - radians(?) ) +
                sin( radians(?) ) *
                sin( radians( latitude ) ) ) ) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<', $radius);
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price_per_month', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price_per_month', 'desc');
                break;
            case 'popular':
                $query->withCount('bookings')->orderBy('bookings_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $hoardings = $query->paginate(12)->withQueryString();

        // Get unique cities and states for filters
        $cities = \App\Models\Hoarding::distinct()->pluck('city')->sort()->values();
        $states = \App\Models\Hoarding::distinct()->pluck('state')->sort()->values();

        return view('customer.search', compact('hoardings', 'cities', 'states'));
    }
}
