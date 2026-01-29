<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Enquiries\Models\Enquiry;
class HomeController extends Controller
{
    /**
     * Display customer home/dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $stats = [
            'total_hoardings' => \App\Models\Hoarding::where('status', 'active')->count(),
            'cities' => \App\Models\Hoarding::distinct('city')->count('city'),
            'active_vendors' => \App\Models\User::role('vendor')->where('status', 'active')->count(),
            'bookings' => \App\Models\Booking::where('status', 'completed')->count(),
        ];

        // Get featured hoardings
        $featuredHoardings = \App\Models\Hoarding::where('status', 'approved')
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        // Check if user has location stored
        $userLocation = null;
        $nearbyHoardings = null;
        if (session()->has('user_location')) {
            $userLocation = session('user_location');
            // Get nearby hoardings if location exists
            $lat = $userLocation['lat'] ?? null;
            $lng = $userLocation['lng'] ?? null;
            if ($lat && $lng) {
                $nearbyHoardings = \App\Models\Hoarding::selectRaw("
                    *, ( 6371 * acos( cos( radians(?) ) *
                    cos( radians( latitude ) ) *
                    cos( radians( longitude ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( latitude ) ) ) ) AS distance
                ", [$lat, $lng, $lat])
                ->having('distance', '<', 10)
                ->orderBy('distance')
                ->take(6)
                ->get();
            }
        }
        $enquiries = null;

        if (auth()->check() && auth()->user()->hasRole('customer')) {
            $enquiries =Enquiry::where('customer_id', auth()->id())
                ->with(['items.hoarding'])
                ->latest()
                ->paginate(10);
        }


        return view('customer.home', compact('stats', 'featuredHoardings', 'userLocation', 'nearbyHoardings','enquiries'));
    }
}
