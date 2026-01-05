<?php

namespace Modules\Search\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\Hoarding;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $duration = $request->get('duration', 'monthly');
        $isWeekly = $duration === 'weekly';

        $query = DB::table('hoardings')
            ->join('vendor_profiles', function ($join) {
                $join->on('vendor_profiles.user_id', '=', 'hoardings.vendor_id')
                    ->where('vendor_profiles.onboarding_status', 'approved')
                    ->whereNull('vendor_profiles.deleted_at');
            })
            ->leftJoin('ooh_hoardings', function ($join) {
                $join->on('ooh_hoardings.hoarding_id', '=', 'hoardings.id')
                    ->whereNull('ooh_hoardings.deleted_at');
            })
            ->leftJoin('dooh_screens', function ($join) {
                $join->on('dooh_screens.hoarding_id', '=', 'hoardings.id')
                    ->whereNull('dooh_screens.deleted_at');
            })
            ->where('hoardings.status', 'active')
            ->whereNull('hoardings.deleted_at');

        if ($isWeekly) {
            // Weekly works only for OOH
            $query->where('hoardings.hoarding_type', 'ooh')
                ->where('hoardings.enable_weekly_booking', 1)
                ->whereNotNull('hoardings.weekly_price')
                ->selectRaw('hoardings.weekly_price AS price');
        } else {
            // Monthly or default
            $query->selectRaw("
                CASE
                    WHEN hoardings.hoarding_type = 'dooh'
                        THEN COALESCE(dooh_screens.price_per_slot, 0)
                    ELSE hoardings.monthly_price
                END AS price
            ");
        }

        $query->addSelect([
            'hoardings.id',
            'hoardings.title',
            'hoardings.hoarding_type',
            'hoardings.category',
            'hoardings.address',
            'hoardings.city',
            'hoardings.state',
            'hoardings.country',
            'hoardings.locality',
            'hoardings.pincode',
            'hoardings.latitude as lat',
            'hoardings.longitude as lng',
            'hoardings.is_featured',
            'hoardings.currency',
            'hoardings.expected_eyeball',
            'hoardings.visibility_details',
            'hoardings.audience_types',

            // OOH size
            'ooh_hoardings.width',
            'ooh_hoardings.height',

            // DOOH resolution
            'dooh_screens.resolution_width',
            'dooh_screens.resolution_height',
        ]);


        if ($request->filled('location')) {
            $loc = strtolower($request->location);
            $query->where(function ($q) use ($loc) {
                $q->whereRaw('LOWER(hoardings.city) LIKE ?', ["%{$loc}%"])
                  ->orWhereRaw('LOWER(hoardings.address) LIKE ?', ["%{$loc}%"]);
            });
        }

        if ($request->filled('type')) {
            $query->where('hoardings.hoarding_type', $request->type);
        }

        if ($request->filled('category')) {
            $query->whereIn('hoardings.category', (array) $request->category);
        }

        if ($request->filled('min_price')) {
            $query->having('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->having('price', '<=', $request->max_price);
        }


        switch ($request->sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'recommended':
                $query->orderByDesc('hoardings.is_featured');
                break;
            default:
                $query->orderByDesc('hoardings.created_at');
        }

        $results = $query->paginate(10);


        $data = collect($results->items())->map(function ($item) use ($isWeekly) {

            $priceType = $item->hoarding_type === 'dooh'
                ? 'per_10_sec_slot'
                : ($isWeekly ? 'weekly' : 'monthly');

            $size = $item->hoarding_type === 'dooh'
                ? [
                    'width'  => (int) $item->resolution_width,
                    'height' => (int) $item->resolution_height,
                    'unit'   => 'px',
                ]
                : [
                    'width'  => (int) $item->width,
                    'height' => (int) $item->height,
                    'unit'   => 'ft',
                ];

            return [
                'id' => $item->id,
                'title' => $item->title,
                'hoarding_type' => $item->hoarding_type,
                'category' => $item->category,

                'price' => (float) $item->price,
                'price_type' => $priceType,
                'currency' => $item->currency,

                'is_featured' => (bool) $item->is_featured,
                'expected_eyeball' => (int) $item->expected_eyeball,

                'address' => [
                    'full' => trim("{$item->address}, {$item->city}, {$item->state}, {$item->country}"),
                    'city' => $item->city,
                    'state' => $item->state,
                    'country' => $item->country,
                    'locality' => $item->locality,
                    'pincode' => $item->pincode,
                ],

                'location' => [
                    'lat' => (float) $item->lat,
                    'lng' => (float) $item->lng,
                ],

                'size' => $size,

                'visibility' => $item->visibility_details,
                'audience_types' => $item->audience_types,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ],
        ]);
    }
    public function availableFilters(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'campaign_duration' => ['weekly', 'monthly'],

                'categories' => [
                    'ooh' => [
                        'Unipoles',
                        'Billboard',
                        'Bus Shelter',
                        'Metro Pillars',
                        'ACP Boards',
                    ],
                    'dooh' => [
                        'LED Screens',
                        'Digital Standee',
                        'Metro Panels',
                    ],
                ],

                'price' => [
                    'min' => (int) Hoarding::min('monthly_price') ?? 0,
                    'max' => (int) Hoarding::max('monthly_price') ?? 100000,
                ],

                'dimensions' => [
                    '260x80',
                    '480x160',
                    '640x240',
                    '1280x720',
                ],

                'ratings' => [5, 4, 3, 2, 1],
            ],
        ]);
    }
    public function locations(Request $request): JsonResponse
    {
        $q = $request->input('q');

        $locations = Hoarding::query()
            ->when($q, function ($query) use ($q) {
                $query->where('city', 'LIKE', "%{$q}%");
            })
            ->select('city')
            ->distinct()
            ->limit(10)
            ->pluck('city')
            ->map(fn ($city) => [
                'name' => $city,
                'type' => 'city',
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }
    public function suggestions(Request $request): JsonResponse
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');

        /* ---------------- POPULAR CITIES ---------------- */
        $popularCities = Hoarding::query()
            ->select('city')
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(6)
            ->pluck('city')
            ->map(fn ($city) => [
                'label' => $city,
                'type'  => 'city',
            ])
            ->values();

        /* ---------------- POPULAR CATEGORIES ---------------- */
        $popularCategories = Hoarding::query()
            ->select('category')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(6)
            ->pluck('category')
            ->map(fn ($cat) => [
                'label' => $cat,
                'type'  => 'category',
            ])
            ->values();

        /* ---------------- NEARBY (STATIC CHIP) ---------------- */
        $nearby = collect([
            [
                'label' => 'Nearby',
                'type'  => 'near_me',
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'nearby'     => $nearby,
                'popular'    => $popularCities,
                'categories' => $popularCategories,
            ],
        ]);
    }
}
