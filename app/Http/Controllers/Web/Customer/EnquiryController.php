<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Hoarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnquiryController extends Controller
{
    /**
     * Display a listing of customer enquiries.
     */
    public function index(Request $request)
    {
        $query = Enquiry::where('customer_id', Auth::id())
            ->with(['hoarding', 'quotation'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->paginate(10);

        return view('customer.enquiries.index', compact('enquiries'));
    }

    /**
     * Show the form for creating a new enquiry.
     */
    public function create(Request $request)
    {
        // Get hoarding from query parameter
        $hoardingId = $request->query('hoarding_id');
        $hoarding = null;

        if ($hoardingId) {
            $hoarding = Hoarding::where('status', 'approved')->findOrFail($hoardingId);
        }

        return view('customer.enquiry-create', compact('hoarding'));
    }

    /**
     * Store a newly created enquiry in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
        $validated = $request->validate([
            'hoarding_id' => 'required|exists:hoardings,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'message' => 'nullable|string|max:1000',
            'budget' => 'nullable|numeric|min:0',
        ]);

        // Check if hoarding is available
        $hoarding = Hoarding::where('id', $validated['hoarding_id'])
            ->where('status', 'approved')
            ->firstOrFail();

        // Create enquiry
        $enquiry = Enquiry::create([
            'customer_id' => Auth::id(),
            'vendor_id' => $hoarding->vendor_id,
            'hoarding_id' => $hoarding->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'message' => $validated['message'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'status' => 'pending',
        ]);

        // TODO: Send notification to vendor

        return redirect()
            ->route('customer.enquiries.show', $enquiry->id)
            ->with('success', 'Enquiry submitted successfully! The vendor will respond soon.');
    }

    /**
     * Display the specified enquiry.
     */
    public function show(int $id)
    {
        $enquiry = Enquiry::where('customer_id', Auth::id())
            ->with(['hoarding.vendor', 'quotation'])
            ->findOrFail($id);

        return view('customer.enquiries.show', compact('enquiry'));
    }

    /**
     * Cancel an enquiry.
     */
    public function cancel(int $id)
    {
        $enquiry = Enquiry::where('customer_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $enquiry->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enquiry cancelled successfully',
        ]);
    }
}
