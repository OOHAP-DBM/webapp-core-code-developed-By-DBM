<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Modules\POS\Models\POSBooking;
use Modules\Hoardings\Services\HoardingService;

class DashboardController extends Controller
{

    public function __construct(private HoardingService $hoardingService) {}

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
        $totalEarnings = POSBooking::where('vendor_id', $userId)
            ->whereIn('payment_status', ['paid', 'partial_paid'])
            ->sum('total_amount') ?? 0;

        $totalHoardings = Hoarding::where('vendor_id', $userId)->count();
        $oohHoardings = Hoarding::where('vendor_id', $userId)->where('hoarding_type', 'ooh')->count();
        $doohHoardings = Hoarding::where('vendor_id', $userId)->where('hoarding_type', 'dooh')->count();
        $activeHoardings = Hoarding::where('vendor_id', $userId)->where('status', 'active')->count();
        $inactiveHoardings = Hoarding::where('vendor_id', $userId)->where('status', 'inactive')->count();

        $totalBookings = Booking::where('vendor_id', $userId)->count();
        $myOrders = Booking::where('vendor_id', $userId)->count();
        $posBookingsCount = $vendor->posBookings()->where('status', 'confirmed')->count();
        $unsoldHoardings = $this->hoardingService->getUnsoldActiveCountByVendor($userId);
        $stats = [
            'earnings' => $totalEarnings,
            'total_hoardings' => $totalHoardings,
            'ooh' => $oohHoardings,
            'dooh' => $doohHoardings,
            'active' => $activeHoardings,
            'inactive' => $inactiveHoardings,
            'unsold' => $unsoldHoardings,
            'total_bookings' => $posBookingsCount,
            'my_orders' => $posBookingsCount,
            'pos' => $posBookingsCount,
        ];

        // dd($unsoldHoardings);
        // Get top hoardings by bookings
        $topHoardings = Hoarding::where('vendor_id', $userId)
            ->withCount(['bookings as bookings_count'])
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($h) {
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

        // Get Booking stats
        $bookingStatsQuery = Booking::where('vendor_id', $userId);

        $bookingStats = $bookingStatsQuery
            ->selectRaw('customer_id, COUNT(*) as bookings, SUM(total_amount) as amount')
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->keyBy('customer_id');

        // Get POS Booking stats
        $posBookingStatsQuery = POSBooking::where('vendor_id', $userId);

        $posBookingStats = $posBookingStatsQuery
            ->selectRaw('customer_id, COUNT(*) as bookings, SUM(total_amount) as amount')
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->keyBy('customer_id');

        // Merge and sum
        $allCustomerStats = [];

        foreach ($bookingStats as $customerId => $stat) {
            $allCustomerStats[$customerId] = [
                'customer' => $stat->customer,
                'bookings' => $stat->bookings,
                'amount' => $stat->amount,
            ];
        }

        foreach ($posBookingStats as $customerId => $stat) {
            if (isset($allCustomerStats[$customerId])) {
                $allCustomerStats[$customerId]['bookings'] += $stat->bookings;
                $allCustomerStats[$customerId]['amount'] += $stat->amount;
            } else {
                $allCustomerStats[$customerId] = [
                    'customer' => $stat->customer,
                    'bookings' => $stat->bookings,
                    'amount' => $stat->amount,
                ];
            }
        }

        // Sort by amount descending
        usort($allCustomerStats, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        // Take top 5
        $topCustomers = collect($allCustomerStats)->take(5)->map(function ($b) {
            return [
                'name' => $b['customer']->name ?? 'Unknown',
                'id' => isset($b['customer']->id) ? 'OOHAPP' . str_pad($b['customer']->id, 4, '0', STR_PAD_LEFT) : 'N/A',
                'by' => 'System',
                'bookings' => $b['bookings'],
                'amount' => $b['amount'] ?? 0,
                'loc' => $b['customer']->state ?? 'N/A',
            ];
        })->toArray();

        // Get recent transactions
        // Get recent online transactions
        $onlineTransactions = Booking::where('vendor_id', $userId)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($t) {
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
            });

        // Get recent POS transactions
        $posTransactions = POSBooking::where('vendor_id', $userId)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => '#' . str_pad($t->id, 5, '0', STR_PAD_LEFT),
                    'customer' => $t->customer->name ?? 'Unknown',
                    'bookings' => 1,
                    'status' => strtoupper($t->payment_status ?? 'PENDING'),
                    'type' => 'POS',
                    'date' => $t->created_at->format('M d, y · g:i A'),
                    'amount' => $t->total_amount ?? 0,
                    'id_numeric' => $t->id,
                ];
            });

        // Merge and sort by date descending, then take top 5
        $transactions = collect($onlineTransactions)
            ->merge($posTransactions)
            ->take(5)
            ->values()
            ->toArray();

        return view('vendor.dashboard', compact(
            'stats',
            'topHoardings',
            'topCustomers',
            'transactions'
        ));
    }

    public function downloadInvoice($id)
    {
        // Try to find the booking (online or POS)
        $booking = POSBooking::find($id) ?? \Modules\POS\Models\POSBooking::find($id);

        if (!$booking) {
            abort(404, 'Transaction not found');
        }

        // Build invoice number (adjust if you store it differently)
        $invoiceNumber = $booking->invoice_number ?? 'INV_2025-26_' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $fileName = "invoices_{$invoiceNumber}.pdf";
        $pdfPath = storage_path("app/public/{$fileName}");

        if (file_exists($pdfPath)) {
            return response()->download($pdfPath, "{$invoiceNumber}.pdf");
        }
        abort(404, 'Invoice not found');
    }
}
