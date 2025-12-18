<?php

namespace Modules\Admin\Controllers\Web\Bookings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingsController extends Controller
{
    public function index(): View
    {
        // Add logic to fetch bookings data
        return view('admin.bookings.index');
    }
}
