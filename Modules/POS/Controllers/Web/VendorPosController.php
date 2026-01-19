<?php

namespace Modules\POS\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\POS\Services\POSBookingService;

class VendorPosController extends Controller
{
    /**
     * Show POS bookings list page for vendor
     */
    public function index(Request $request, POSBookingService $service)
    {
        // Only vendors can access
        $this->middleware(['auth', 'role:vendor']);
        // Blade view handles API fetch, so just render view
        return view('vendor.pos.list');
    }

    /**
     * Show create booking page
     */
    public function create(Request $request)
    {
        $this->middleware(['auth', 'role:vendor']);
        return view('vendor.pos.create');
    }

    /**
     * Show POS dashboard
     */
    public function dashboard(Request $request)
    {
        $this->middleware(['auth', 'role:vendor']);
        return view('vendor.pos.dashboard');
    }

    // Add more actions as needed (edit, view, etc.)
}
