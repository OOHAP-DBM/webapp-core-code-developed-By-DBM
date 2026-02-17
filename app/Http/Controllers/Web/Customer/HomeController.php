<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Modules\Enquiries\Models\Enquiry;
class HomeController extends Controller
{
    /**
     * Display customer home/dashboard.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $stats = [
            'total_hoardings' => \App\Models\Hoarding::where('status', 'active')->count(),
            'cities' => \App\Models\Hoarding::distinct('city')->count('city'),
            'active_vendors' => \App\Models\User::role('vendor')->where('status', 'active')->count(),
            'bookings' => \App\Models\Booking::where('status', 'completed')->count(),
            'total_enquiries' => (auth()->check() && auth()->user()->hasRole('customer'))
                ? \Modules\Enquiries\Models\Enquiry::where('customer_id', auth()->id())->count()
                : 0,
        ];

        $featuredHoardings = \App\Models\Hoarding::where('status', 'approved')
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        $userLocation = null;
        $nearbyHoardings = null;
        if (session()->has('user_location')) {
            $userLocation = session('user_location');
            $lat = $userLocation['lat'] ?? null;
            $lng = $userLocation['lng'] ?? null;

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
        }

        /* ================= ENQUIRIES (DASHBOARD) ================= */

        $enquiries = null;

        if (auth()->check() && auth()->user()->hasRole('customer')) {
            $query = Enquiry::where('customer_id', auth()->id())
                ->with(['items.hoarding']);

            // SEARCH BY ID ONLY (match EnquiryController)
            $searchId = null;
            if ($request->filled('search')) {
                $search = trim($request->search);
                $searchId = preg_replace('/\D/', '', $search);
                if ($searchId !== '') {
                    $query->where('id', (int) $searchId);
                    $query->orderByRaw(
                        "CASE WHEN id = ? THEN 0 ELSE 1 END",
                        [(int) $searchId]
                    );
                }
            }

            /* DATE FILTER */
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

            $query->orderBy('created_at', 'desc');

            $enquiries = $query
                ->limit(5)
                ->get();
        }

        return view(
            'customer.home',
            compact(
                'stats',
                'featuredHoardings',
                'userLocation',
                'nearbyHoardings',
                'enquiries'
            )
        );
    }
}
