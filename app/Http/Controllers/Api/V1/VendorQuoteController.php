<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\VendorQuote;
use App\Services\VendorQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VendorQuoteController extends Controller
{
    protected $quoteService;

    public function __construct(VendorQuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    /**
     * Get vendor's quotes
     */
    public function index(Request $request)
    {
        $vendor = Auth::user();

        $quotes = VendorQuote::forVendor($vendor->id)
            ->with(['hoarding', 'customer', 'quoteRequest', 'enquiry'])
            ->latest();

        if ($request->status) {
            $quotes->where('status', $request->status);
        }

        if ($request->customer_id) {
            $quotes->where('customer_id', $request->customer_id);
        }

        return response()->json([
            'success' => true,
            'data' => $quotes->paginate(20),
        ]);
    }

    /**
     * Get customer's received quotes
     */
    public function customerQuotes(Request $request)
    {
        $customer = Auth::user();

        $quotes = VendorQuote::forCustomer($customer->id)
            ->with(['hoarding', 'vendor', 'quoteRequest', 'enquiry'])
            ->whereIn('status', [VendorQuote::STATUS_SENT, VendorQuote::STATUS_VIEWED])
            ->latest();

        if ($request->hoarding_id) {
            $quotes->where('hoarding_id', $request->hoarding_id);
        }

        return response()->json([
            'success' => true,
            'data' => $quotes->paginate(20),
        ]);
    }

    /**
     * Show quote details
     */
    public function show($id)
    {
        $quote = VendorQuote::with([
            'hoarding',
            'customer',
            'vendor',
            'quoteRequest',
            'enquiry',
            'parentQuote',
            'revisions',
            'booking'
        ])->findOrFail($id);

        // Mark as viewed if customer is viewing
        if (Auth::id() === $quote->customer_id && $quote->isSent()) {
            $quote->markAsViewed();
        }

        return response()->json([
            'success' => true,
            'data' => $quote,
        ]);
    }

    /**
     * Create quote from quote request
     */
    public function createFromRequest(Request $request)
    {
        $validated = $request->validate([
            'quote_request_id' => 'required|exists:quote_requests,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'duration_days' => 'nullable|integer|min:1',
            'duration_type' => 'nullable|in:days,weeks,months',
            'base_price' => 'required|numeric|min:0',
            'printing_cost' => 'nullable|numeric|min:0',
            'mounting_cost' => 'nullable|numeric|min:0',
            'survey_cost' => 'nullable|numeric|min:0',
            'lighting_cost' => 'nullable|numeric|min:0',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'other_charges_description' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'vendor_notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|array',
            'auto_send' => 'nullable|boolean',
        ]);

        $quoteRequest = \App\Models\QuoteRequest::findOrFail($validated['quote_request_id']);
        $vendor = Auth::user();

        // Check if vendor can submit
        $requestService = app(\App\Services\QuoteRequestService::class);
        if (!$requestService->canVendorSubmitQuote($quoteRequest, $vendor)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot submit a quote for this request',
            ], 403);
        }

        $quote = $requestService->submitVendorQuote($quoteRequest, $vendor, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Quote created successfully',
            'data' => $quote->load(['hoarding', 'customer', 'quoteRequest']),
        ], 201);
    }

    /**
     * Create quote from enquiry
     */
    public function createFromEnquiry(Request $request)
    {
        $validated = $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'duration_days' => 'nullable|integer|min:1',
            'duration_type' => 'nullable|in:days,weeks,months',
            'base_price' => 'required|numeric|min:0',
            'printing_cost' => 'nullable|numeric|min:0',
            'mounting_cost' => 'nullable|numeric|min:0',
            'survey_cost' => 'nullable|numeric|min:0',
            'lighting_cost' => 'nullable|numeric|min:0',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'other_charges_description' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'vendor_notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|array',
        ]);

        $enquiry = \App\Models\Enquiry::findOrFail($validated['enquiry_id']);
        $vendor = Auth::user();

        $quote = $this->quoteService->createFromEnquiry($enquiry, $vendor, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Quote created successfully',
            'data' => $quote->load(['hoarding', 'customer', 'enquiry']),
        ], 201);
    }

    /**
     * Update quote (only draft)
     */
    public function update(Request $request, $id)
    {
        $quote = VendorQuote::findOrFail($id);

        if ($quote->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$quote->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft quotes can be updated',
            ], 422);
        }

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'duration_days' => 'nullable|integer|min:1',
            'base_price' => 'nullable|numeric|min:0',
            'printing_cost' => 'nullable|numeric|min:0',
            'mounting_cost' => 'nullable|numeric|min:0',
            'survey_cost' => 'nullable|numeric|min:0',
            'lighting_cost' => 'nullable|numeric|min:0',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'other_charges_description' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'vendor_notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|array',
        ]);

        $quote = $this->quoteService->updateQuote($quote, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Quote updated successfully',
            'data' => $quote,
        ]);
    }

    /**
     * Send quote to customer
     */
    public function send($id)
    {
        $quote = VendorQuote::findOrFail($id);

        if ($quote->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $this->quoteService->sendQuote($quote);

            return response()->json([
                'success' => true,
                'message' => 'Quote sent successfully',
                'data' => $quote->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Accept quote (customer)
     */
    public function accept($id)
    {
        $quote = VendorQuote::findOrFail($id);

        if ($quote->customer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $booking = $this->quoteService->acceptQuote($quote);

            return response()->json([
                'success' => true,
                'message' => 'Quote accepted and booking created successfully',
                'data' => [
                    'quote' => $quote->fresh(),
                    'booking' => $booking,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject quote (customer)
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $quote = VendorQuote::findOrFail($id);

        if ($quote->customer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $this->quoteService->rejectQuote($quote, $validated['reason'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Quote rejected successfully',
                'data' => $quote->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Create revision of quote
     */
    public function revise(Request $request, $id)
    {
        $quote = VendorQuote::findOrFail($id);

        if ($quote->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'base_price' => 'nullable|numeric|min:0',
            'printing_cost' => 'nullable|numeric|min:0',
            'mounting_cost' => 'nullable|numeric|min:0',
            'survey_cost' => 'nullable|numeric|min:0',
            'lighting_cost' => 'nullable|numeric|min:0',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'vendor_notes' => 'nullable|string',
        ]);

        try {
            $revision = $this->quoteService->createRevision($quote, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Quote revision created successfully',
                'data' => $revision,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download PDF
     */
    public function downloadPdf($id)
    {
        $quote = VendorQuote::findOrFail($id);

        if (!in_array(Auth::id(), [$quote->customer_id, $quote->vendor_id])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$quote->pdf_path || !Storage::disk('private')->exists($quote->pdf_path)) {
            // Generate PDF if not exists
            $this->quoteService->generatePDF($quote);
            $quote->refresh();
        }

        return Storage::disk('private')->download($quote->pdf_path, $quote->getPdfFilename());
    }

    /**
     * Calculate pricing
     */
    public function calculatePricing(Request $request)
    {
        $validated = $request->validate([
            'base_price' => 'required|numeric|min:0',
            'printing_cost' => 'nullable|numeric|min:0',
            'mounting_cost' => 'nullable|numeric|min:0',
            'survey_cost' => 'nullable|numeric|min:0',
            'lighting_cost' => 'nullable|numeric|min:0',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $pricing = $this->quoteService->calculatePricing($validated);

        return response()->json([
            'success' => true,
            'data' => $pricing,
        ]);
    }
}
