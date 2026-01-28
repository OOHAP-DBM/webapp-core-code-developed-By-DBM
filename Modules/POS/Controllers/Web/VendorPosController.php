<?php

namespace Modules\POS\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\POS\Services\POSBookingService;

class VendorPosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active_role:vendor']);
    }

    /**
     * Show POS bookings list page for vendor
     */
    public function index(Request $request, POSBookingService $service)
    {
        // Blade view handles API fetch, so just render view
        return view('vendor.pos.list');
    }

    /**
     * Show create booking page
     */
    public function create(Request $request)
    {
        return view('vendor.pos.create');
    }

    /**
     * Show POS dashboard
     */
    public function dashboard(Request $request)
    {
        return view('vendor.pos.dashboard');
    }

    /**
     * Show POS booking details page for vendor
     */
    public function show($id)
    {
        // The view will fetch booking details via API
        return view('vendor.pos.show', ['bookingId' => $id]);
    }

    public function searchHoardings(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Hoarding::query()
            ->where('vendor_id', Auth::id())
            ->where('status', 'approved');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('location_city', 'like', "%{$search}%")
                ->orWhere('location_address', 'like', "%{$search}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereDoesntHave('bookings', function ($q) use ($startDate, $endDate) {
                $q->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->whereIn('status', ['confirmed', 'payment_hold']);
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(20)->get([
                'id',
                'title',
                'location_city',
                'location_state',
                'size',
                'price_per_month'
            ])
        ]);
    }
}
