<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Services\QuoteRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuoteRequestController extends Controller
{
    protected $requestService;

    public function __construct(QuoteRequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * Get customer's quote requests
     */
    public function index(Request $request)
    {
        $customer = Auth::user();

        $filters = [
            'status' => $request->status,
            'hoarding_id' => $request->hoarding_id,
        ];

        $requests = $this->requestService->getCustomerRequests($customer, array_filter($filters));

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Get pending requests for vendor
     */
    public function pendingForVendor()
    {
        $vendor = Auth::user();

        $requests = $this->requestService->getPendingRequestsForVendor($vendor);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Show quote request details
     */
    public function show($id)
    {
        $quoteRequest = QuoteRequest::with([
            'hoarding',
            'customer',
            'quotes.vendor',
            'selectedQuote'
        ])->findOrFail($id);

        // Check authorization
        $user = Auth::user();
        if ($user->id !== $quoteRequest->customer_id &&
            !$quoteRequest->isVendorEligible($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $quoteRequest,
        ]);
    }

    /**
     * Create new quote request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hoarding_id' => 'required|exists:hoardings,id',
            'preferred_start_date' => 'required|date|after:today',
            'preferred_end_date' => 'required|date|after:preferred_start_date',
            'duration_days' => 'nullable|integer|min:1',
            'duration_type' => 'nullable|in:days,weeks,months',
            'requirements' => 'nullable|string|max:2000',
            'printing_required' => 'nullable|boolean',
            'mounting_required' => 'nullable|boolean',
            'lighting_required' => 'nullable|boolean',
            'additional_services' => 'nullable|array',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'vendor_selection_mode' => 'nullable|in:single,multiple',
            'invited_vendor_ids' => 'nullable|array',
            'invited_vendor_ids.*' => 'exists:users,id',
            'open_to_all_vendors' => 'nullable|boolean',
            'response_deadline' => 'nullable|date|after:now',
            'decision_deadline' => 'nullable|date|after:response_deadline',
        ]);

        $customer = Auth::user();

        $quoteRequest = $this->requestService->createRequest($customer, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Quote request created successfully',
            'data' => $quoteRequest->load(['hoarding']),
        ], 201);
    }

    /**
     * Update quote request (only draft)
     */
    public function update(Request $request, $id)
    {
        $quoteRequest = QuoteRequest::findOrFail($id);

        if ($quoteRequest->customer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'preferred_start_date' => 'nullable|date|after:today',
            'preferred_end_date' => 'nullable|date|after:preferred_start_date',
            'duration_days' => 'nullable|integer|min:1',
            'requirements' => 'nullable|string|max:2000',
            'printing_required' => 'nullable|boolean',
            'mounting_required' => 'nullable|boolean',
            'lighting_required' => 'nullable|boolean',
            'additional_services' => 'nullable|array',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'invited_vendor_ids' => 'nullable|array',
            'open_to_all_vendors' => 'nullable|boolean',
            'response_deadline' => 'nullable|date|after:now',
            'decision_deadline' => 'nullable|date|after:response_deadline',
        ]);

        try {
            $quoteRequest = $this->requestService->updateRequest($quoteRequest, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Quote request updated successfully',
                'data' => $quoteRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Publish quote request
     */
    public function publish($id)
    {
        $quoteRequest = QuoteRequest::findOrFail($id);

        if ($quoteRequest->customer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $quoteRequest = $this->requestService->publishRequest($quoteRequest);

            return response()->json([
                'success' => true,
                'message' => 'Quote request published successfully. Vendors have been notified.',
                'data' => $quoteRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get quote comparison
     */
    public function comparison($id)
    {
        $quoteRequest = QuoteRequest::findOrFail($id);

        if ($quoteRequest->customer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $comparison = $this->requestService->getQuoteComparison($quoteRequest);

        return response()->json([
            'success' => true,
            'data' => [
                'request' => $quoteRequest,
                'quotes' => $comparison,
            ],
        ]);
    }

    /**
     * Accept a quote
     */
    public function acceptQuote(Request $request, $id)
    {
        $validated = $request->validate([
            'quote_id' => 'required|exists:vendor_quotes,id',
        ]);

        $quoteRequest = QuoteRequest::findOrFail($id);

        if ($quoteRequest->customer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $quote = \App\Models\VendorQuote::findOrFail($validated['quote_id']);

        try {
            $booking = $this->requestService->acceptQuote($quoteRequest, $quote);

            return response()->json([
                'success' => true,
                'message' => 'Quote accepted and booking created successfully',
                'data' => [
                    'quote_request' => $quoteRequest->fresh(),
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
     * Close quote request
     */
    public function close($id)
    {
        $quoteRequest = QuoteRequest::findOrFail($id);

        if ($quoteRequest->customer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $quoteRequest = $this->requestService->closeRequest($quoteRequest);

        return response()->json([
            'success' => true,
            'message' => 'Quote request closed successfully',
            'data' => $quoteRequest,
        ]);
    }

    /**
     * Cancel quote request
     */
    public function cancel(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $quoteRequest = QuoteRequest::findOrFail($id);

        if ($quoteRequest->customer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $quoteRequest = $this->requestService->cancelRequest($quoteRequest, $validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Quote request cancelled successfully',
            'data' => $quoteRequest,
        ]);
    }
}
