<?php

namespace Modules\Search\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Cart\Services\CartService;

class SearchController extends Controller
{
    public function index(Request $request, CartService $cartService): View
    {
        $isWeekly = $request->get('duration') === 'weekly';
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
            ->whereNull('hoardings.deleted_at')
            ->select([
                'hoardings.id',
                'hoardings.title',
                'hoardings.address',
                'hoardings.city',
                'hoardings.hoarding_type',
                'hoardings.available_from',
                'hoardings.is_featured',
                'hoardings.expected_eyeball',
                'hoardings.latitude as lat',
                'hoardings.longitude as lng',
                'ooh_hoardings.width',
                'ooh_hoardings.height',
                'hoardings.base_monthly_price',
                'hoardings.monthly_price',
                'hoardings.enable_weekly_booking',
                'hoardings.weekly_price_1',
                DB::raw("
                    CASE
                        WHEN hoardings.hoarding_type = 'dooh'
                            THEN dooh_screens.resolution_width
                        ELSE ooh_hoardings.width
                    END AS display_width
                "),
                DB::raw("
                    CASE
                        WHEN hoardings.hoarding_type = 'dooh'
                            THEN dooh_screens.resolution_height
                        ELSE ooh_hoardings.height
                    END AS display_height
                "),
                DB::raw("
                    CASE
                        WHEN hoardings.base_monthly_price IS NOT NULL
                        AND hoardings.base_monthly_price > 0

                        /* 🔒 PRICE MUST BE > 0 (OOH + DOOH) */
                        AND (
                            CASE
                                WHEN hoardings.hoarding_type = 'dooh'
                                    THEN COALESCE(dooh_screens.price_per_slot, 0)
                                ELSE hoardings.monthly_price
                            END
                        ) > 0

                        /* 🔒 REAL DISCOUNT ONLY */
                        AND (
                            CASE
                                WHEN hoardings.hoarding_type = 'dooh'
                                    THEN COALESCE(dooh_screens.price_per_slot, 0)
                                ELSE hoardings.monthly_price
                            END
                        ) < hoardings.base_monthly_price

                        THEN ROUND(
                            (
                                hoardings.base_monthly_price -
                                (
                                    CASE
                                        WHEN hoardings.hoarding_type = 'dooh'
                                            THEN COALESCE(dooh_screens.price_per_slot, 0)
                                        ELSE hoardings.monthly_price
                                    END
                                )
                            ) / hoardings.base_monthly_price * 100
                        )

                        ELSE NULL
                    END AS discount_percent
                "),

                DB::raw("
                    CASE
                        WHEN hoardings.hoarding_type = 'dooh'
                            THEN 'px'
                        ELSE 'ft'
                    END AS display_unit
                "),
                DB::raw("
                    CASE
                        WHEN hoardings.hoarding_type = 'dooh'
                            THEN COALESCE(dooh_screens.price_per_slot, 0)
                        ELSE hoardings.monthly_price
                    END AS price
                "),

            ]);
        if ($isWeekly) {
            $query->where('hoardings.hoarding_type', 'ooh')
                  ->where('hoardings.enable_weekly_booking', 1)
                  ->whereNotNull('hoardings.weekly_price_1')
                  ->addSelect(DB::raw('hoardings.weekly_price_1 AS price'));
        }
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
            $query->whereIn('hoardings.category', $request->category);
        }
        if ($request->filled('vendor')) {
            $query->whereIn('vendor_profiles.company_name', $request->vendor);
        }
        if ($request->filled('visibility')) {
            $query->where(function ($q) use ($request) {
                foreach ($request->visibility as $vis) {
                    $q->orWhereJsonContains('hoardings.visibility_details', $vis);
                }
            });
        }
        if ($request->filled('audience')) {
            $query->where(function ($q) use ($request) {
                foreach ($request->audience as $aud) {
                    $q->orWhereJsonContains('hoardings.audience_types', $aud);
                }
            });
        }
        if ($request->filled('min_gazeflow')) {
            $query->where('hoardings.expected_eyeball', '>=', $request->min_gazeflow);
        }
        if ($request->filled('max_gazeflow')) {
            $query->where('hoardings.expected_eyeball', '<=', $request->max_gazeflow);
        }
        if ($request->filled('min_height')) {
            $query->where('ooh_hoardings.height', '>=', $request->min_height);
        }
        if ($request->filled('max_height')) {
            $query->where('ooh_hoardings.height', '<=', $request->max_height);
        }
        if ($request->filled('min_price')) {
            $query->having('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->having('price', '<=', $request->max_price);
        }
        if ($request->filled('near_me') && $request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $radius = 25;
            $query->whereRaw("
                (6371 * acos(
                    cos(radians(?)) *
                    cos(radians(hoardings.latitude)) *
                    cos(radians(hoardings.longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(hoardings.latitude))
                )) <= ?
            ", [$lat, $lng, $lat, $radius]);
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = $request->from_date;
            $to   = $request->to_date;
            $query->where(function ($q) use ($from, $to) {
                $q->where(function ($qq) use ($to) {
                    $qq->whereNull('hoardings.available_from')
                    ->orWhere('hoardings.available_from', '<=', $to);
                });
                $q->where(function ($qq) use ($from) {
                    $qq->whereNull('hoardings.available_to')
                    ->orWhere('hoardings.available_to', '>=', $from);
                });
            });
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
        $results = $query->paginate(10)->withQueryString();
        $hoardingIds = $results->pluck('id')->toArray();

        $oohImages = DB::table('hoarding_media')
            ->whereIn('hoarding_id', $hoardingIds)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('hoarding_id');

        $doohImages = DB::table('dooh_screen_media')
            ->join('dooh_screens', 'dooh_screens.id', '=', 'dooh_screen_media.dooh_screen_id')
            ->whereIn('dooh_screens.hoarding_id', $hoardingIds)
            ->orderByDesc('dooh_screen_media.is_primary')
            ->orderBy('dooh_screen_media.sort_order')
            ->get()
            ->groupBy('hoarding_id');

        $results->getCollection()->transform(function ($item) use ($oohImages, $doohImages) {
            $item->images = $item->hoarding_type === 'ooh'
                ? ($oohImages[$item->id] ?? collect())
                : ($doohImages[$item->id] ?? collect());

            return $item;
        });

        $cartHoardingIds = auth()->check()
        ? $cartService->getCartHoardingIds()
        : [];
        return view('search.index', [
            'results' => $results,
            'cartHoardingIds' => $cartHoardingIds,
        ]);
    }
}
