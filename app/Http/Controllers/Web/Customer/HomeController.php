<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Enquiries\Models\Enquiry;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total_hoardings' => \App\Models\Hoarding::where('status', 'active')->count(),
            'cities'          => \App\Models\Hoarding::distinct('city')->count('city'),
            'active_vendors'  => \App\Models\User::role('vendor')->where('status', 'active')->count(),
            'bookings'        => \App\Models\Booking::where('status', 'completed')->count(),
            'total_enquiries' => (auth()->check() && auth()->user()->hasRole('customer'))
                ? Enquiry::where('customer_id', auth()->id())->count()
                : 0,
        ];

        $featuredHoardings = \App\Models\Hoarding::where('status', 'approved')
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        $userLocation    = null;
        $nearbyHoardings = null;
        if (session()->has('user_location')) {
            $userLocation = session('user_location');
            $lat = $userLocation['lat'] ?? null;
            $lng = $userLocation['lng'] ?? null;

            if ($lat && $lng) {
                $nearbyHoardings = \App\Models\Hoarding::selectRaw("
                    *, (6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    )) AS distance
                ", [$lat, $lng, $lat])
                    ->having('distance', '<', 10)
                    ->orderBy('distance')
                    ->take(6)
                    ->get();
            }
        }

        /* ── ENQUIRIES ─────────────────────────────────────────────── */
        $enquiries = null;
        if (auth()->check() && auth()->user()->hasRole('customer')) {
            $query = Enquiry::where('customer_id', auth()->id())
                ->with(['items.hoarding']);

            if ($request->filled('search')) {
                $search   = trim((string) $request->search);
                $searchId = preg_replace('/\D/', '', $search);

                $query->where(function ($q) use ($search, $searchId) {
                    if ($searchId !== '') {
                        $q->orWhere('id', (int) $searchId);
                    }
                    $q->orWhere('customer_note', 'like', "%{$search}%")
                        ->orWhereHas('items.hoarding', function ($hq) use ($search) {
                            $hq->where('title', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%");
                        });
                });

                if ($searchId !== '') {
                    $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [(int) $searchId]);
                }
            }

            if ($request->filled('date_filter')) {
                $now = Carbon::now();
                switch ($request->date_filter) {
                    case 'last_week':
                        $query->whereBetween('created_at', [
                            $now->copy()->subWeek()->startOfWeek(),
                            $now->copy()->subWeek()->endOfWeek(),
                        ]);
                        break;
                    case 'last_month':
                        $query->whereBetween('created_at', [
                            $now->copy()->subMonth()->startOfMonth(),
                            $now->copy()->subMonth()->endOfMonth(),
                        ]);
                        break;
                    case 'last_year':
                        $query->whereBetween('created_at', [
                            $now->copy()->subYear()->startOfYear(),
                            $now->copy()->subYear()->endOfYear(),
                        ]);
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

            $enquiries = $query->orderBy('created_at', 'desc')->limit(5)->get();
        }

        /* ── CHART FILTER PARAMS ───────────────────────────────────── */
        $chartFilter = $request->get('chart_filter', 'this_month');
        $chartOffset = (int) $request->get('chart_offset', 0);

        /* ── BOOKING STATS (pos_bookings + bookings) ───────────────── */
        $bookingStats    = ['labels' => [], 'data' => []];
        $hasBookingStats = false;
        $chartRangeLabel = '';

        if (auth()->check() && auth()->user()->hasRole('customer')) {
            $customerId = auth()->id();

            [$labels, $data, $chartRangeLabel] =
                $this->buildChartData($chartFilter, $chartOffset, $customerId);

            $bookingStats    = compact('labels', 'data');
            $hasBookingStats = array_sum($data) > 0;
        }

        return view('customer.home', compact(
            'stats',
            'featuredHoardings',
            'userLocation',
            'nearbyHoardings',
            'enquiries',
            'bookingStats',
            'hasBookingStats',
            'chartFilter',
            'chartOffset',
            'chartRangeLabel',
        ));
    }

    /* ================================================================
     |  PRIVATE — Build chart labels + data
     |  Merges results from bookings + pos_bookings
     |  Sab jagah created_at use hota hai
     ================================================================ */
    private function buildChartData(string $filter, int $offset, int $customerId): array
    {
        switch ($filter) {

            /* ── TODAY: hour-wise (00:00 – 23:00) ─────────────────── */
            case 'today':
                $day    = Carbon::today()->addDays($offset);
                $label  = $day->format('d M, Y');
                $labels = [];
                $data   = [];

                for ($h = 0; $h < 24; $h++) {
                    $from     = $day->copy()->addHours($h);
                    $to       = $from->copy()->addHour();
                    $labels[] = $from->format('H:00');
                    $data[]   = $this->countBookings($customerId, $from, $to);
                }

                return [$labels, $data, $label];

                /* ── THIS WEEK: day-wise (Mon – Sun) ──────────────────── */
            case 'this_week':
                $weekStart = Carbon::now()->startOfWeek()->addWeeks($offset);
                $weekEnd   = $weekStart->copy()->endOfWeek();
                $label     = $weekStart->format('d M') . ' – ' . $weekEnd->format('d M, Y');
                $labels    = [];
                $data      = [];

                for ($d = 0; $d < 7; $d++) {
                    $day      = $weekStart->copy()->addDays($d);
                    $labels[] = $day->format('D, d M'); // Mon, 16 Mar
                    $data[]   = $this->countBookings(
                        $customerId,
                        $day->copy()->startOfDay(),
                        $day->copy()->endOfDay()
                    );
                }

                return [$labels, $data, $label];

                /* ── THIS MONTH: week-wise ─────────────────────────────── */
            case 'this_month':
            default:
                $monthStart = Carbon::now()->startOfMonth()->addMonths($offset);
                $monthEnd   = $monthStart->copy()->endOfMonth();
                $label      = $monthStart->format('M Y');
                $labels     = [];
                $data       = [];

                $cursor = $monthStart->copy();
                while ($cursor->lte($monthEnd)) {
                    $weekEnd2 = $cursor->copy()->endOfWeek()->min($monthEnd);
                    $labels[] = $cursor->format('d') . '–' . $weekEnd2->format('d M');
                    $data[]   = $this->countBookings(
                        $customerId,
                        $cursor->copy()->startOfDay(),
                        $weekEnd2->copy()->endOfDay()
                    );
                    $cursor = $weekEnd2->copy()->addDay();
                }

                return [$labels, $data, $label];
        }
    }

    /* ================================================================
     |  Count bookings from BOTH tables — sirf created_at use hoga
     ================================================================ */
    private function countBookings(
        int    $customerId,
        Carbon $from,
        Carbon $to
    ): int {
        // bookings table
        $fromBookings = DB::table('bookings')
            ->where('customer_id', $customerId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // pos_bookings table
        $fromPos = DB::table('pos_bookings')
            ->where('customer_id', $customerId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        return $fromBookings + $fromPos;
    }
}
