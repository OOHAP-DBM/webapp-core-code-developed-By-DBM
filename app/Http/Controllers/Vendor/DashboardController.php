<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = Auth::user();
        $userId = $vendor->id;
        $profile = $vendor->vendorProfile;
        
        // Only allow dashboard if onboarding_status is pending_approval or approved
        if (!$profile || !in_array($profile->onboarding_status, ['pending_approval', 'approved'])) {
            // Redirect to correct onboarding step
            $step = $profile ? $profile->onboarding_step : 1;
            $routes = [
                1 => 'vendor.onboarding.contact-details',
                2 => 'vendor.onboarding.business-info',
            ];
            $route = $routes[$step] ?? 'vendor.onboarding.contact-details';
            return redirect()->route($route)
                ->with('info', 'Please complete your vendor onboarding.');
        }

        // Get statistics
        $totalEarnings = Booking::where('vendor_id', $userId)
            ->where('payment_status', 'paid')
            ->sum('total_amount') ?? 0;

        $totalHoardings = Hoarding::where('vendor_id', $userId)->count();
        $oohHoardings = Hoarding::where('vendor_id', $userId)->where('hoarding_type', 'ooh')->count();
        $doohHoardings = Hoarding::where('vendor_id', $userId)->where('hoarding_type', 'dooh')->count();
        $activeHoardings = Hoarding::where('vendor_id', $userId)->where('status', 'active')->count();
        $inactiveHoardings = Hoarding::where('vendor_id', $userId)->where('status', 'inactive')->count();
        $unsoldHoardings = Hoarding::where('vendor_id', $userId)->where('status', 'draft')->count();

        $totalBookings = Booking::where('vendor_id', $userId)->count();
        $myOrders = Booking::where('vendor_id', $userId)->count();
        $posBookings = 0;

        $stats = [
            'earnings' => $totalEarnings,
            'total_hoardings' => $totalHoardings,
            'ooh' => $oohHoardings,
            'dooh' => $doohHoardings,
            'active' => $activeHoardings,
            'inactive' => $inactiveHoardings,
            'unsold' => $unsoldHoardings,
            'total_bookings' => $totalBookings,
            'my_orders' => $myOrders,
            'pos' => $posBookings,
        ];

        // Get top hoardings by bookings
        $topHoardings = Hoarding::where('vendor_id', $userId)
            ->withCount('bookings')
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
                     'size'      => $h->display_size ?? '-', 
                    'bookings' => $h->bookings_count,
                    'status' => $h->status,

            ];
            })->toArray();

        // Get top customers by amount spent
        $topCustomers = Booking::where('vendor_id', $userId)
            ->selectRaw('customer_id, COUNT(*) as bookings, SUM(total_amount) as amount')
            ->groupBy('customer_id')
            ->orderBy('amount', 'desc')
            ->take(5)
            ->with('customer')
            ->get()
            ->map(function($b) {
                return [
                    'name' => $b->customer->name ?? 'Unknown',
                    'id' => $b->customer->id ? 'OOHAPP' . str_pad($b->customer->id, 4, '0', STR_PAD_LEFT) : 'N/A',
                    'by' => 'System',
                    'bookings' => $b->bookings,
                    'amount' => $b->amount ?? 0,
                    'loc' => $b->customer->state ?? 'N/A',
                ];
            })->toArray();

        // Get recent transactions
        $transactions = Booking::where('vendor_id', $userId)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($t) {
                return [
                    'id' => '#' . str_pad($t->id, 5, '0', STR_PAD_LEFT),
                    'customer' => $t->customer->name ?? 'Unknown',
                    'bookings' => 1,
                    'status' => strtoupper($t->payment_status ?? 'PENDING'),
                    'type' => 'ONLINE',
                    'date' => $t->created_at->format('M d, y · g:i A'),
                    'amount' => $t->total_amount ?? 0,
                    'id_numeric' => $t->id,
                ];
            })->toArray();

        return view('vendor.dashboard', compact(
            'stats',
            'topHoardings',
            'topCustomers',
            'transactions'
        ));
    }
}
