<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Booking;


class OrderController extends Controller
{
    /**
     * Display customer orders.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Booking::where('customer_id', auth()->id())
            ->with('hoarding');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(10);

        return view('customer.orders.index', compact('bookings'));
    }

    /**
     * Show order details.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $booking = \Modules\Bookings\Models\Booking::where('customer_id', auth()->id())
            ->with(['hoarding', 'vendor', 'payments'])
            ->findOrFail($id);

        return view('customer.orders.show', compact('booking'));
    }
}
