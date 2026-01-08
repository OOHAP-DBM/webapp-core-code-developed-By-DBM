<?php

namespace Modules\Hoardings\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use App\Models\Booking;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HoardingCalendarController extends Controller
{
    /**
     * Display the calendar view for a hoarding
     */
    public function show(Request $request, $id)
    {
        $hoarding = Hoarding::where('vendor_id', Auth::id())
            ->findOrFail($id);

        return view('vendor.hoardings.calendar', compact('hoarding'));
    }

    /**
     * Get calendar data API endpoint
     * GET /vendor/hoarding/{id}/calendar
     * 
     * Returns events in FullCalendar format with:
     * - Booked dates (red) - confirmed bookings
     * - Enquiry dates (yellow) - pending enquiries
     * - Available dates (green) - no conflicts
     */
    public function getCalendarData(Request $request, $id): JsonResponse
    {
        // Verify vendor ownership
        $hoarding = Hoarding::where('vendor_id', Auth::id())
            ->findOrFail($id);

        $start = $request->input('start');
        $end = $request->input('end');

        $events = [];

        // Get all bookings for this hoarding within date range
        $bookings = Booking::where('hoarding_id', $id)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                          ->where('end_date', '>=', $end);
                    });
            })
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_PAYMENT_HOLD,
                Booking::STATUS_PENDING_PAYMENT_HOLD
            ])
            ->with(['customer'])
            ->get();

        // Add booking events (red)
        foreach ($bookings as $booking) {
            $statusColors = [
                Booking::STATUS_CONFIRMED => '#dc2626', // red-600
                Booking::STATUS_PAYMENT_HOLD => '#ea580c', // orange-600
                Booking::STATUS_PENDING_PAYMENT_HOLD => '#f59e0b', // amber-500
            ];

            $events[] = [
                'id' => 'booking-' . $booking->id,
                'title' => 'Booked: ' . $booking->customer->name,
                'start' => $booking->start_date,
                'end' => Carbon::parse($booking->end_date)->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                'backgroundColor' => $statusColors[$booking->status] ?? '#dc2626',
                'borderColor' => $statusColors[$booking->status] ?? '#dc2626',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'booking',
                    'bookingId' => $booking->id,
                    'customerName' => $booking->customer->name,
                    'customerPhone' => $booking->customer->phone ?? 'N/A',
                    'amount' => '₹' . number_format($booking->total_amount, 2),
                    'status' => ucfirst(str_replace('_', ' ', $booking->status)),
                    'duration' => $booking->duration_days . ' days',
                ],
            ];
        }

        // Get all enquiries for this hoarding within date range
        $enquiries = Enquiry::where('hoarding_id', $id)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('preferred_start_date', [$start, $end])
                    ->orWhereBetween('preferred_end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('preferred_start_date', '<=', $start)
                          ->where('preferred_end_date', '>=', $end);
                    });
            })
            ->where('status', Enquiry::STATUS_PENDING)
            ->with(['customer'])
            ->get();

        // Add enquiry events (yellow)
        foreach ($enquiries as $enquiry) {
            $events[] = [
                'id' => 'enquiry-' . $enquiry->id,
                'title' => 'Enquiry: ' . $enquiry->customer->name,
                'start' => $enquiry->preferred_start_date,
                'end' => Carbon::parse($enquiry->preferred_end_date)->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                'backgroundColor' => '#fbbf24', // yellow-400
                'borderColor' => '#f59e0b', // yellow-500
                'textColor' => '#1f2937', // gray-800
                'extendedProps' => [
                    'type' => 'enquiry',
                    'enquiryId' => $enquiry->id,
                    'customerName' => $enquiry->customer->name,
                    'customerPhone' => $enquiry->customer->phone ?? 'N/A',
                    'message' => $enquiry->message ?? 'No message',
                    'status' => 'Pending',
                    'duration' => Carbon::parse($enquiry->preferred_start_date)
                        ->diffInDays(Carbon::parse($enquiry->preferred_end_date)) . ' days',
                ],
            ];
        }

        // Calculate available dates (green) - dates without bookings or enquiries
        // Only show available slots for the requested range
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        
        // Get all dates that are booked or have enquiries
        $occupiedDates = [];
        
        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_date);
            $bookingEnd = Carbon::parse($booking->end_date);
            
            while ($bookingStart->lte($bookingEnd)) {
                $occupiedDates[$bookingStart->format('Y-m-d')] = true;
                $bookingStart->addDay();
            }
        }
        
        foreach ($enquiries as $enquiry) {
            $enquiryStart = Carbon::parse($enquiry->preferred_start_date);
            $enquiryEnd = Carbon::parse($enquiry->preferred_end_date);
            
            while ($enquiryStart->lte($enquiryEnd)) {
                $occupiedDates[$enquiryStart->format('Y-m-d')] = true;
                $enquiryStart->addDay();
            }
        }

        // Find continuous available date ranges
        $currentDate = $startDate->copy();
        $availableStart = null;
        
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            
            if (!isset($occupiedDates[$dateStr])) {
                // Available date
                if ($availableStart === null) {
                    $availableStart = $currentDate->copy();
                }
            } else {
                // Occupied date - close previous available range if exists
                if ($availableStart !== null) {
                    $events[] = [
                        'id' => 'available-' . $availableStart->format('Ymd') . '-' . $currentDate->copy()->subDay()->format('Ymd'),
                        'title' => 'Available',
                        'start' => $availableStart->format('Y-m-d'),
                        'end' => $currentDate->format('Y-m-d'), // FullCalendar end is exclusive
                        'backgroundColor' => '#10b981', // green-500
                        'borderColor' => '#059669', // green-600
                        'textColor' => '#ffffff',
                        'display' => 'background',
                        'extendedProps' => [
                            'type' => 'available',
                        ],
                    ];
                    $availableStart = null;
                }
            }
            
            $currentDate->addDay();
        }
        
        // Close last available range if exists
        if ($availableStart !== null) {
            $events[] = [
                'id' => 'available-' . $availableStart->format('Ymd') . '-' . $endDate->format('Ymd'),
                'title' => 'Available',
                'start' => $availableStart->format('Y-m-d'),
                'end' => $endDate->copy()->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                'backgroundColor' => '#10b981', // green-500
                'borderColor' => '#059669', // green-600
                'textColor' => '#ffffff',
                'display' => 'background',
                'extendedProps' => [
                    'type' => 'available',
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Get hoarding statistics for calendar header
     */
    public function getStats(Request $request, $id): JsonResponse
    {
        // Verify vendor ownership
        $hoarding = Hoarding::where('vendor_id', Auth::id())
            ->findOrFail($id);

        $now = Carbon::now();

        // Current month stats
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

        $stats = [
            'total_bookings' => Booking::where('hoarding_id', $id)
                ->whereIn('status', [Booking::STATUS_CONFIRMED])
                ->count(),
            
            'active_bookings' => Booking::where('hoarding_id', $id)
                ->where('status', Booking::STATUS_CONFIRMED)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->count(),
            
            'pending_enquiries' => Enquiry::where('hoarding_id', $id)
                ->where('status', Enquiry::STATUS_PENDING)
                ->count(),
            
            'current_month_bookings' => Booking::where('hoarding_id', $id)
                ->whereIn('status', [Booking::STATUS_CONFIRMED])
                ->whereBetween('start_date', [$currentMonthStart, $currentMonthEnd])
                ->count(),
            
            'current_month_revenue' => Booking::where('hoarding_id', $id)
                ->where('status', Booking::STATUS_CONFIRMED)
                ->whereBetween('start_date', [$currentMonthStart, $currentMonthEnd])
                ->sum('total_amount'),
            
            'occupancy_rate' => $this->calculateOccupancyRate($id, $currentMonthStart, $currentMonthEnd),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate occupancy rate for a date range
     */
    private function calculateOccupancyRate($hoardingId, $startDate, $endDate): float
    {
        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        
        $bookedDays = Booking::where('hoarding_id', $hoardingId)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->get()
            ->sum(function ($booking) use ($startDate, $endDate) {
                $bookingStart = Carbon::parse($booking->start_date)->max(Carbon::parse($startDate));
                $bookingEnd = Carbon::parse($booking->end_date)->min(Carbon::parse($endDate));
                return $bookingStart->diffInDays($bookingEnd) + 1;
            });

        return $totalDays > 0 ? round(($bookedDays / $totalDays) * 100, 2) : 0;
    }
}
