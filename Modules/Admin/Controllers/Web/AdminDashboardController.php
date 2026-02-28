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

        // ===== TOP 5 BEST SELLING HOARDINGS =====
        $topHoardings = Hoarding::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get()
            ->map(function($h) {
                return [
                    'id' => $h->id,
                    'title' => $h->title,
                    'type' => strtoupper($h->hoarding_type),
                    'cat' => $h->category ?? '-',
                    'loc' => $h->display_location ?? '-',
                    'size' => $h->display_size ?? '-',
                    'bookings' => $h->bookings_count,
                    'published_by' => optional($h->vendor)->name ?? '-',
                ];
            });

        // ===== RECENTLY BOOKED HOARDINGS (POS) =====
        $recentBookings = Booking::with(['customer', 'hoarding'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($b) {
                return [
                    'customer' => optional($b->customer)->name ?? '-',
                    'bookings' => 1, // Each row is a booking
                    'grand_total' => $b->total_amount,
                    'amount_received' => $b->payment_status === 'paid' ? $b->total_amount : 0,
                    'due_amount' => $b->payment_status === 'paid' ? 0 : $b->total_amount,
                    'action' => 'Request for payment',
                ];
            });

        // ===== RECENT TRANSACTIONS =====
        $transactions = Booking::with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($t) {
                return [
                    'id' => '#' . str_pad($t->id, 5, '0', STR_PAD_LEFT),
                    'customer' => optional($t->customer)->name ?? '-',
                    'bookings' => 1,
                    'status' => strtoupper($t->payment_status ?? 'PENDING'),
                    'type' => 'ONLINE',
                    'date' => $t->created_at->format('d M, y · g:i A'),
                    'amount' => $t->total_amount ?? 0,
                ];
            });

        // ===== MONTHLY USER GROWTH (last 12 months) =====
        $userGrowth = [];
        $now = now();
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $userGrowth[] = User::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // ===== MONTHLY BOOKINGS (Booking + POSBooking, last 12 months) =====
        $bookingGrowth = [];
        $posBookingModel = null;
        if (class_exists('Modules\\POS\\Models\\POSBooking')) {
            $posBookingModel = app('Modules\\POS\\Models\\POSBooking');
        }
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $normal = Booking::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $pos = $posBookingModel ? $posBookingModel->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count() : 0;
            $bookingGrowth[] = $normal + $pos;
        }

        return view('admin.dashboard', compact(
            'userCount',
            'vendorCount',
            'customerCount',
            'bookingCount',
            'hoardingCount',
            'oohCount',
            'doohCount',
            'topHoardings',
            'recentBookings',
            'transactions',
            'userGrowth',
            'bookingGrowth'
        ));
    }

}
