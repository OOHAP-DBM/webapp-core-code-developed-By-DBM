<?php

namespace App\Services\Vendor;

use App\Models\Booking;
use App\Models\Hoarding;
use Modules\Hoardings\Services\HoardingService;
use Modules\POS\Models\POSBooking;

class VendorDashboardService
{
    public function __construct(private HoardingService $hoardingService)
    {
    }

    /**
     * Compute dashboard stats for a vendor.
     *
     * - earnings:       sum of POS bookings with paid/partial_paid payment status.
     * - total_bookings / my_orders / pos: confirmed POS booking count.
     * - unsold:         active hoardings not covered by any confirmed booking.
     */
    public function getStats(int $vendorId): array
    {
        $totalEarnings = POSBooking::where('vendor_id', $vendorId)
            ->whereIn('payment_status', [
                POSBooking::PAYMENT_STATUS_PAID,
                'partial_paid',
            ])
            ->sum('total_amount') ?? 0;

        $posBookingsCount = POSBooking::where('vendor_id', $vendorId)
            ->where('status', POSBooking::STATUS_CONFIRMED)
            ->count();

        return [
            'earnings'        => $totalEarnings,
            'total_hoardings' => Hoarding::where('vendor_id', $vendorId)->count(),
            'ooh'             => Hoarding::where('vendor_id', $vendorId)->where('hoarding_type', 'ooh')->count(),
            'dooh'            => Hoarding::where('vendor_id', $vendorId)->where('hoarding_type', 'dooh')->count(),
            'active'          => Hoarding::where('vendor_id', $vendorId)->where('status', 'active')->count(),
            'inactive'        => Hoarding::where('vendor_id', $vendorId)->where('status', 'inactive')->count(),
            'unsold'          => $this->hoardingService->getUnsoldActiveCountByVendor($vendorId),
            'total_bookings'  => $posBookingsCount,
            'my_orders'       => $posBookingsCount,
            'pos'             => $posBookingsCount,
        ];
    }

    /**
     * Top hoardings for the vendor ordered by booking count.
     */
    public function getTopHoardings(int $vendorId, int $limit = 5): array
    {
        return Hoarding::where('vendor_id', $vendorId)
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take($limit)
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
    }

    /**
     * Top customers merged from online Booking and POSBooking, sorted by total spend.
     */
    public function getTopCustomers(int $vendorId, int $limit = 5): array
    {
        $bookingStats = Booking::where('vendor_id', $vendorId)
            ->selectRaw('customer_id, COUNT(*) as bookings, SUM(total_amount) as amount')
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->keyBy('customer_id');

        $posBookingStats = POSBooking::where('vendor_id', $vendorId)
            ->selectRaw('customer_id, COUNT(*) as bookings, SUM(total_amount) as amount')
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->keyBy('customer_id');

        $merged = [];

        foreach ($bookingStats as $customerId => $stat) {
            $merged[$customerId] = [
                'customer' => $stat->customer,
                'bookings' => (int) $stat->bookings,
                'amount'   => (float) $stat->amount,
            ];
        }

        foreach ($posBookingStats as $customerId => $stat) {
            if (isset($merged[$customerId])) {
                $merged[$customerId]['bookings'] += (int) $stat->bookings;
                $merged[$customerId]['amount']   += (float) $stat->amount;
            } else {
                $merged[$customerId] = [
                    'customer' => $stat->customer,
                    'bookings' => (int) $stat->bookings,
                    'amount'   => (float) $stat->amount,
                ];
            }
        }

        usort($merged, fn($a, $b) => $b['amount'] <=> $a['amount']);

        return collect($merged)->take($limit)->map(fn($b) => [
            'name'     => $b['customer']->name ?? 'Unknown',
            'id'       => $b['customer']?->id
                            ? 'OOHAPP' . str_pad($b['customer']->id, 4, '0', STR_PAD_LEFT)
                            : 'N/A',
            'by'       => 'System',
            'bookings' => $b['bookings'],
            'amount'   => $b['amount'],
            'loc'      => $b['customer']->state ?? 'N/A',
        ])->toArray();
    }

    /**
     * Recent transactions merged from online Booking and POSBooking, sorted by date.
     */
    public function getRecentTransactions(int $vendorId, int $limit = 5): array
    {
        $online = Booking::where('vendor_id', $vendorId)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take($limit)
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
            ]);

        $pos = POSBooking::where('vendor_id', $vendorId)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(fn($t) => [
                'id'         => '#' . str_pad($t->id, 5, '0', STR_PAD_LEFT),
                'id_numeric' => $t->id,
                'customer'   => $t->customer->name ?? 'Unknown',
                'bookings'   => 1,
                'status'     => strtoupper($t->payment_status ?? 'PENDING'),
                'type'       => 'POS',
                'date'       => $t->created_at->format('M d, y · g:i A'),
                'amount'     => $t->total_amount ?? 0,
            ]);

        return collect($online)
            ->merge($pos)
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->toArray();
    }
}