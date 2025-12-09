<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        // Calculate stats
        $stats = [
            'pending' => $vendor->bookings()->where('status', 'pending')->count(),
            'confirmed' => $vendor->bookings()->where('status', 'confirmed')->count(),
            'active' => $vendor->bookings()->where('status', 'active')->count(),
            'completed' => $vendor->bookings()->where('status', 'completed')->count(),
        ];
        
        $query = $vendor->bookings()
            ->with(['customer', 'hoarding']);
        
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
    
    public function show($id)
    {
        $vendor = Auth::user();
        $booking = $vendor->bookings()
            ->with(['customer', 'hoarding', 'payments'])
            ->findOrFail($id);
        
        return view('vendor.bookings.show', compact('booking'));
    }
    
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
        
        // Send confirmation notification to customer
        // $booking->customer->notify(new BookingConfirmed($booking));
        
        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully!'
        ]);
    }
    
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
        
        // Process refund if payment was made
        if ($booking->payment_status === 'paid') {
            // $this->processRefund($booking);
        }
        
        // Send cancellation notification to customer
        // $booking->customer->notify(new BookingCancelled($booking));
        
        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully!'
        ]);
    }
    
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
}
