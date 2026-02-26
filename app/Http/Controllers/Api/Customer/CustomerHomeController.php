<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Modules\Enquiries\Models\Enquiry;

class CustomerHomeController extends Controller
{
    /**
     * Customer dashboard/home data for mobile API.
     *
     * GET /api/customer/home
     *
     * Query params (all optional):
     *   search        - enquiry ID search
     *   date_filter   - last_week | last_month | last_year | custom
     *   from_date     - required when date_filter=custom (Y-m-d)
     *   to_date       - required when date_filter=custom (Y-m-d)
     *   lat           - user latitude  (for nearby hoardings)
     *   lng           - user longitude (for nearby hoardings)
     */
    public function index(Request $request): JsonResponse
    {
        /* ─── STATS ─────────────────────────────────────────────── */
        $stats = [
            'total_hoardings'  => \App\Models\Hoarding::where('status', 'active')->count(),
            'cities'           => \App\Models\Hoarding::distinct('city')->count('city'),
            'active_vendors'   => \App\Models\User::role('vendor')->where('status', 'active')->count(),
            'bookings'         => \App\Models\Booking::where('status', 'completed')->count(),
            'total_enquiries'  => (auth()->check() && auth()->user()->hasRole('customer'))
                ? Enquiry::where('customer_id', auth()->id())->count()
                : 0,
        ];

        /* ─── FEATURED HOARDINGS ─────────────────────────────────── */
        $featuredHoardings = \App\Models\Hoarding::where('status', 'approved')
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        /* ─── NEARBY HOARDINGS ───────────────────────────────────── */
        $nearbyHoardings = null;
        $lat = $request->filled('lat') ? (float) $request->lat : null;
        $lng = $request->filled('lng') ? (float) $request->lng : null;

        if ($lat && $lng) {
            $nearbyHoardings = \App\Models\Hoarding::selectRaw("
                *, (6371 * acos(
                    cos(radians(?)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(latitude))
                )) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<', 10)
            ->orderBy('distance')
            ->take(6)
            ->get();
        }

        /* ─── ENQUIRIES ──────────────────────────────────────────── */
        $enquiries = null;

        if (auth()->check() && auth()->user()->hasRole('customer')) {
            $query = Enquiry::where('customer_id', auth()->id())
                ->with(['items.hoarding']);

            // Search by enquiry ID
            if ($request->filled('search')) {
                $searchId = preg_replace('/\D/', '', trim($request->search));
                if ($searchId !== '') {
                    $query->where('id', (int) $searchId)
                          ->orderByRaw(
                              "CASE WHEN id = ? THEN 0 ELSE 1 END",
                              [(int) $searchId]
                          );
                }
            }

            // Date filter
            if ($request->filled('date_filter')) {
                switch ($request->date_filter) {
                    case 'last_week':
                        $query->where('created_at', '>=', Carbon::now()->subWeek());
                        break;
                    case 'last_month':
                        $query->where('created_at', '>=', Carbon::now()->subMonth());
                        break;
                    case 'last_year':
                        $query->where('created_at', '>=', Carbon::now()->subYear());
                        break;
                    case 'custom':
                        if ($request->filled('from_date') && $request->filled('to_date')) {
                            $query->whereBetween('created_at', [
                                Carbon::parse($request->from_date)->startOfDay(),
                                Carbon::parse($request->to_date)->endOfDay(),
                            ]);
                        }
                        break;
                }
            }

            $enquiries = $query
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        /* ─── RESPONSE ───────────────────────────────────────────── */
        return response()->json([
            'success' => true,
            'data'    => [
                'stats'             => $stats,
                'featured_hoardings'=> $featuredHoardings,
                'nearby_hoardings'  => $nearbyHoardings,
                'enquiries'         => $enquiries,
            ],
        ], 200);
    }
}
