<?php

namespace Modules\Admin\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $userCount = User::count();
        $bookingCount = class_exists(Booking::class) ? Booking::count() : 0;
        return view('admin.dashboard', compact('userCount', 'bookingCount'));
    }
}
