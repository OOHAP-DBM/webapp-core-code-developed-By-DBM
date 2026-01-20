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
        $this->middleware(['auth', 'role:vendor']);
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
}
