<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = Hoarding::query()
            ->where('status', 'active')   
            ->select([
                'id',
                'title',
                'address',
                'city',
                'latitude as lat',
                'longitude as lng',
                'monthly_price as price',
                'available_from',
                'hoarding_type',
                'created_at',
            ]);
        if ($request->filled('location')) {
            $location = $request->location;
            $query->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                  ->orWhere('address', 'like', "%{$location}%");
            });
        }
        if ($request->filled('type')) {
            $query->where('hoarding_type', $request->type);
        }
        if ($request->filled('min_price')) {
            $query->where('monthly_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('monthly_price', '<=', $request->max_price);
        }
        $query->orderByDesc('created_at');
        $results = $query
            ->paginate(10)
            ->withQueryString();
        return view('search.index', compact('results'));
    }
}
