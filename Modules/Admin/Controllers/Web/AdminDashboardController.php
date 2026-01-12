<?php

namespace Modules\Admin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hoarding;
use Illuminate\View\View;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ===== USERS =====
        $userCount     = User::count();
        $vendorCount   = User::role('vendor')->count();
        $customerCount = User::role('customer')->count();

        // ===== BOOKINGS =====
        $bookingCount  = class_exists(Booking::class) ? Booking::count() : 0;

        // ===== HOARDINGS =====
        $hoardingCount = Hoarding::count();
        $oohCount      = Hoarding::where('hoarding_type', 'ooh')->count();
        $doohCount     = Hoarding::where('hoarding_type', 'dooh')->count();

        return view('admin.dashboard', compact(
            'userCount',
            'vendorCount',
            'customerCount',
            'bookingCount',
            'hoardingCount',
            'oohCount',
            'doohCount'
        ));
    }

}
