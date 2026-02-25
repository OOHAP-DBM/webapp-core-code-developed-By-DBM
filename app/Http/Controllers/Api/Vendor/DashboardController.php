<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hoarding;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Vendor dashboard data for mobile API.
     *
     * GET /api/vendor/dashboard
     */
    public function index(): JsonResponse
    {
        $vendor = Auth::user();
        $userId = $vendor->id;
        $profile = $vendor->vendorProfile;

        /* ─── ONBOARDING GUARD ───────────────────────────────────── */
        if (!$profile || !in_array($profile->onboarding_status, ['pending_approval', 'approved'])) {
            $step = $profile ? $profile->onboarding_step : 1;

            return response()->json([
                'success'         => false,
                'onboarding'      => true,
                'onboarding_step' => $step,
                'message'         => 'Please complete your vendor onboarding.',
            ], 403);
        }

        /* ─── STATS ──────────────────────────────────────────────── */
        $stats = [
            'earnings'        => Booking::where('vendor_id', $userId)->where('payment_status', 'paid')->sum('total_amount') ?? 0,
            'total_hoardings' => Hoarding::where('vendor_id', $userId)->count(),
            'ooh'             => Hoarding::where('vendor_id', $userId)->where('hoarding_type', 'ooh')->count(),
            'dooh'            => Hoarding::where('vendor_id', $userId)->where('hoarding_type', 'dooh')->count(),
            'active'          => Hoarding::where('vendor_id', $userId)->where('status', 'active')->count(),
            'inactive'        => Hoarding::where('vendor_id', $userId)->where('status', 'inactive')->count(),
            'unsold'          => Hoarding::where('vendor_id', $userId)->where('status', 'draft')->count(),
            'total_bookings'  => Booking::where('vendor_id', $userId)->count(),
            'my_orders'       => Booking::where('vendor_id', $userId)->count(),
            'pos'             => 0,
        ];

        /* ─── TOP HOARDINGS ──────────────────────────────────────── */
        $topHoardings = Hoarding::where('vendor_id', $userId)
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn($h) => [
                'id'       => $h->id,
                'title'    => $h->title,
                'type'     => strtoupper($h->hoarding_type),
                'cat'      => $h->category ?? '-',
                'loc'      => $h->display_location ?? '-',
                'size'     => $h->display_size ?? '-',
                'bookings' => $h->bookings_count,
                'status'   => $h->status,
            ])->toArray();

        /* ─── TOP CUSTOMERS ──────────────────────────────────────── */
        $topCustomers = Booking::where('vendor_id', $userId)
            ->selectRaw('customer_id, COUNT(*) as bookings, SUM(total_amount) as amount')
            ->groupBy('customer_id')
            ->orderBy('amount', 'desc')
            ->take(5)
            ->with('customer')
            ->get()
            ->map(fn($b) => [
                'name'     => $b->customer->name ?? 'Unknown',
                'id'       => $b->customer?->id
                                ? 'OOHAPP' . str_pad($b->customer->id, 4, '0', STR_PAD_LEFT)
                                : 'N/A',
                'by'       => 'System',
                'bookings' => $b->bookings,
                'amount'   => $b->amount ?? 0,
                'loc'      => $b->customer->state ?? 'N/A',
            ])->toArray();

        /* ─── RECENT TRANSACTIONS ────────────────────────────────── */
        $transactions = Booking::where('vendor_id', $userId)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn($t) => [
                'id'         => '#' . str_pad($t->id, 5, '0', STR_PAD_LEFT),
                'id_numeric' => $t->id,
                'customer'   => $t->customer->name ?? 'Unknown',
                'bookings'   => 1,
                'status'     => strtoupper($t->payment_status ?? 'PENDING'),
                'type'       => 'ONLINE',
                'date'       => $t->created_at->format('M d, y · g:i A'),
                'amount'     => $t->total_amount ?? 0,
            ])->toArray();

        /* ─── RESPONSE ───────────────────────────────────────────── */
        return response()->json([
            'success' => true,
            'data'    => [
                'stats'         => $stats,
                'top_hoardings' => $topHoardings,
                'top_customers' => $topCustomers,
                'transactions'  => $transactions,
            ],
        ], 200);
    }
}