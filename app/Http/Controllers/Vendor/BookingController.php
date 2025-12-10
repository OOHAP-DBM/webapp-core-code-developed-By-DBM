<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Vendor Booking Management Controller (PROMPT 48)
 * 
 * Handles vendor panel booking operations:
 * - New Bookings: Pending payment or not yet started
 * - Ongoing Bookings: Today between start & end dates
 * - Completed Bookings: End date has passed
 * - Cancelled Bookings: Cancelled or refunded
 */
class BookingController extends Controller
{
    /**
     * All bookings with unified filtering (legacy route)
     */
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        // Calculate stats for all categories
        $stats = [
            'new' => $vendor->bookings()->new()->count(),
            'ongoing' => $vendor->bookings()->ongoing()->count(),
            'completed' => $vendor->bookings()->completed()->count(),
            'cancelled' => $vendor->bookings()->cancelledBookings()->count(),
            'total' => $vendor->bookings()->count(),
        ];
        
        $query = $vendor->bookings()
            ->with(['customer', 'hoarding', 'quotation']);
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }
        
        $bookings = $query->latest()->paginate(20);
        
        return view('vendor.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * New Bookings: Pending payment hold or not yet confirmed (PROMPT 48)
     */
    public function newBookings(Request $request)
    {
        $vendor = Auth::user();
        
        $query = $vendor->bookings()
            ->new()
            ->with(['customer', 'hoarding', 'quotation']);
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        $bookings = $query->latest()->paginate(20);
        
        $stats = [
            'total' => $bookings->total(),
            'pending_payment' => $vendor->bookings()
                ->where('status', Booking::STATUS_PENDING_PAYMENT_HOLD)
                ->count(),
            'payment_hold' => $vendor->bookings()
                ->where('status', Booking::STATUS_PAYMENT_HOLD)
                ->count(),
        ];
        
        return view('vendor.bookings.new', compact('bookings', 'stats'));
    }

    /**
     * Ongoing Bookings: Today between start & end dates (PROMPT 48)
     */
    public function ongoingBookings(Request $request)
    {
        $vendor = Auth::user();
        
        $query = $vendor->bookings()
            ->ongoing()
            ->with(['customer', 'hoarding', 'quotation', 'timelineEvents']);
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Additional filter: campaign progress
        if ($request->filled('progress')) {
            switch ($request->progress) {
                case 'just_started':
                    $query->whereDate('start_date', '>=', now()->subDays(7));
                    break;
                case 'mid_campaign':
                    $query->whereRaw('DATEDIFF(end_date, CURDATE()) > DATEDIFF(CURDATE(), start_date)');
                    break;
                case 'ending_soon':
                    $query->whereDate('end_date', '<=', now()->addDays(7));
                    break;
            }
        }
        
        $bookings = $query->latest('start_date')->paginate(20);
        
        $stats = [
            'total' => $bookings->total(),
            'just_started' => $vendor->bookings()->ongoing()
                ->whereDate('start_date', '>=', now()->subDays(7))
                ->count(),
            'ending_soon' => $vendor->bookings()->ongoing()
                ->whereDate('end_date', '<=', now()->addDays(7))
                ->count(),
        ];
        
        return view('vendor.bookings.ongoing', compact('bookings', 'stats'));
    }

    /**
     * Completed Bookings: End date < today (PROMPT 48)
     */
    public function completedBookings(Request $request)
    {
        $vendor = Auth::user();
        
        $query = $vendor->bookings()
            ->completed()
            ->with(['customer', 'hoarding', 'quotation', 'bookingProofs']);
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Additional filter: proof of display status
        if ($request->filled('pod_status')) {
            switch ($request->pod_status) {
                case 'submitted':
                    $query->whereHas('bookingProofs', function($q) {
                        $q->where('status', 'pending');
                    });
                    break;
                case 'approved':
                    $query->whereHas('bookingProofs', function($q) {
                        $q->where('status', 'approved');
                    });
                    break;
                case 'missing':
                    $query->whereDoesntHave('bookingProofs');
                    break;
            }
        }
        
        $bookings = $query->latest('end_date')->paginate(20);
        
        $stats = [
            'total' => $bookings->total(),
            'with_pod' => $vendor->bookings()->completed()
                ->whereHas('bookingProofs')
                ->count(),
            'without_pod' => $vendor->bookings()->completed()
                ->whereDoesntHave('bookingProofs')
                ->count(),
            'total_revenue' => $vendor->bookings()->completed()
                ->sum('total_amount'),
        ];
        
        return view('vendor.bookings.completed', compact('bookings', 'stats'));
    }

    /**
     * Cancelled Bookings: Cancelled or refunded (PROMPT 48)
     */
    public function cancelledBookings(Request $request)
    {
        $vendor = Auth::user();
        
        $query = $vendor->bookings()
            ->cancelledBookings()
            ->with(['customer', 'hoarding', 'quotation']);
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Additional filter: cancellation type
        if ($request->filled('cancellation_type')) {
            $query->where('status', $request->cancellation_type);
        }
        
        $bookings = $query->latest('cancelled_at')->paginate(20);
        
        $stats = [
            'total' => $bookings->total(),
            'cancelled' => $vendor->bookings()
                ->where('status', Booking::STATUS_CANCELLED)
                ->count(),
            'refunded' => $vendor->bookings()
                ->where('status', Booking::STATUS_REFUNDED)
                ->count(),
            'total_lost_revenue' => $vendor->bookings()
                ->cancelledBookings()
                ->sum('total_amount'),
        ];
        
        return view('vendor.bookings.cancelled', compact('bookings', 'stats'));
    }
    
    /**
     * Show single booking details
     */
    public function show($id)
    {
        $vendor = Auth::user();
        $booking = $vendor->bookings()
            ->with([
                'customer',
                'hoarding',
                'quotation',
                'timelineEvents' => function($q) {
                    $q->orderBy('scheduled_date', 'asc');
                },
                'statusLogs' => function($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'bookingProofs',
                'payments'
            ])
            ->findOrFail($id);
        
        return view('vendor.bookings.show', compact('booking'));
    }
    
    /**
     * Confirm booking (legacy - kept for compatibility)
     */
    public function confirm(Request $request, $id)
    {
        $vendor = Auth::user();
        $booking = $vendor->bookings()->findOrFail($id);
        
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be confirmed'
            ], 400);
        }
        
        $booking->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $vendor->id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully!'
        ]);
    }
    
    /**
     * Cancel booking
     */
    public function cancel(Request $request, $id)
    {
        $vendor = Auth::user();
        $booking = $vendor->bookings()->findOrFail($id);
        
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled'
            ], 400);
        }
        
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $vendor->id,
            'cancellation_reason' => $request->reason,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully!'
        ]);
    }
    
    /**
     * Update booking status
     */
    public function updateStatus(Request $request, $id)
    {
        $vendor = Auth::user();
        $booking = $vendor->bookings()->findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,active,completed,cancelled',
        ]);
        
        $booking->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully!'
        ]);
    }

    /**
     * Apply common filters to query (PROMPT 48)
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('hoarding', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('location', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('hoarding_id')) {
            $query->where('hoarding_id', $request->hoarding_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('total_amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('total_amount', '<=', $request->amount_max);
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->get('sort_order', 'desc');
            switch ($request->sort_by) {
                case 'date':
                    $query->orderBy('start_date', $sortOrder);
                    break;
                case 'amount':
                    $query->orderBy('total_amount', $sortOrder);
                    break;
                case 'customer':
                    $query->join('users as customers', 'bookings.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $sortOrder)
                        ->select('bookings.*');
                    break;
                default:
                    $query->orderBy('created_at', $sortOrder);
            }
        }

        return $query;
    }
}
