<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Display search page with filters
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Hoarding::where('status', 'active')
            ->with(['vendor']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('min_price')) {
            $query->where('monthly_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('monthly_price', '<=', $request->max_price);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        // Location-based search
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->radius ?? 10; // Default 10km

            $query->selectRaw("
                *, ( 6371 * acos( cos( radians(?) ) *
                cos( radians( lat ) ) *
                cos( radians( lng ) - radians(?) ) +
                sin( radians(?) ) *
                sin( radians( lat ) ) ) ) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->orderBy('distance');
        } else {
            $query->latest();
        }

        $hoardings = $query->paginate(12);

        // Get filter options
        $types = Hoarding::where('status', 'active')
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort()
            ->values();

        return view('search.index', compact('hoardings', 'types'));
    }
}
