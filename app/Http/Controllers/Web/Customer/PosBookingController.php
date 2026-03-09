<?php

namespace App\Http\Controllers\Web\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\POS\Models\PosBooking;

class PosBookingController extends Controller
{
    /**
     * Display the POS Booking page - fully dynamic (Customer Side)
     */
    public function index(Request $request)
    {
        $customerId = auth()->id();

        $query = PosBooking::with(['hoardings'])
            ->where('customer_id', $customerId); // ✅ sirf us customer ki bookings

        // ── Search ──────────────────────────────────────────────────────────
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('booking_type',  'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%");
            });
        }

        // ── Status Filter ────────────────────────────────────────────────────
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // ── Payment Status Filter ────────────────────────────────────────────
        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        // ── Date Range Filter ────────────────────────────────────────────────
        if ($from = $request->input('from_date')) {
            $query->whereDate('start_date', '>=', $from);
        }
        if ($to = $request->input('to_date')) {
            $query->whereDate('end_date', '<=', $to);
        }

        // ── Sorting ──────────────────────────────────────────────────────────
        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowed = ['created_at', 'start_date', 'end_date', 'total_amount'];

        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        // ── Pagination ───────────────────────────────────────────────────────
        $bookings = $query->paginate(10)->withQueryString();

        // ── Customer's own Stats ─────────────────────────────────────────────
        $base = PosBooking::where('customer_id', $customerId);

        $stats = [
            'total_bookings'  => (clone $base)->count(),
            'active'          => (clone $base)->where('status', 'confirmed')->count(),
            'pending_payment' => (clone $base)->where('payment_status', 'unpaid')->count(),
            'total_spent'     => (clone $base)->where('payment_status', 'paid')->sum('total_amount'),
        ];

        $statusOptions = [
            ''                => 'All Status',
            'confirmed'       => 'Confirmed',
            'pending_payment' => 'Pending Payment',
            'cancelled'       => 'Cancelled',
        ];

        $paymentStatusOptions = [
            ''       => 'All Payments',
            'paid'   => 'Paid',
            'unpaid' => 'Unpaid',
        ];

        return view('customer.pos-booking.index', compact(
            'bookings',
            'stats',
            'statusOptions',
            'paymentStatusOptions'
        ));
    }
}