<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\VendorQuote;
use App\Models\User;
use App\Models\Hoarding;
use App\Models\Booking;
use App\Notifications\QuoteRequestPublishedNotification;
use App\Notifications\QuoteRequestClosedNotification;
use Illuminate\Support\Facades\DB;

class QuoteRequestService
{
    /**
     * Create new quote request
     */
    public function createRequest(User $customer, array $data): QuoteRequest
    {
        return DB::transaction(function () use ($customer, $data) {
            $quoteRequest = QuoteRequest::create([
                'customer_id' => $customer->id,
                'hoarding_id' => $data['hoarding_id'],
                'preferred_start_date' => $data['preferred_start_date'],
                'preferred_end_date' => $data['preferred_end_date'],
                'duration_days' => $data['duration_days'] ?? null,
                'duration_type' => $data['duration_type'] ?? QuoteRequest::DURATION_DAYS,
                'requirements' => $data['requirements'] ?? null,
                'printing_required' => $data['printing_required'] ?? false,
                'mounting_required' => $data['mounting_required'] ?? false,
                'lighting_required' => $data['lighting_required'] ?? false,
                'additional_services' => $data['additional_services'] ?? [],
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'vendor_selection_mode' => $data['vendor_selection_mode'] ?? QuoteRequest::MODE_SINGLE,
                'invited_vendor_ids' => $data['invited_vendor_ids'] ?? [],
                'open_to_all_vendors' => $data['open_to_all_vendors'] ?? false,
                'response_deadline' => $data['response_deadline'] ?? now()->addDays(7),
                'decision_deadline' => $data['decision_deadline'] ?? now()->addDays(14),
                'status' => QuoteRequest::STATUS_DRAFT,
            ]);

            return $quoteRequest;
        });
    }

    /**
     * Update quote request (only if draft)
     */
    public function updateRequest(QuoteRequest $quoteRequest, array $data): QuoteRequest
    {
        if (!$quoteRequest->isDraft()) {
            throw new \Exception('Only draft quote requests can be updated');
        }

        $quoteRequest->update($data);

        return $quoteRequest;
    }

    /**
     * Publish quote request and notify vendors
     */
    public function publishRequest(QuoteRequest $quoteRequest): QuoteRequest
    {
        if (!$quoteRequest->canPublish()) {
            throw new \Exception('Quote request cannot be published in its current state');
        }

        DB::transaction(function () use ($quoteRequest) {
            $quoteRequest->publish();

            // Notify eligible vendors
            $this->notifyVendors($quoteRequest);
        });

        return $quoteRequest;
    }

    /**
     * Notify vendors about new quote request
     */
    public function notifyVendors(QuoteRequest $quoteRequest): void
    {
        $vendorIds = $quoteRequest->getEligibleVendors();

        if (empty($vendorIds)) {
            return;
        }

        $vendors = User::whereIn('id', $vendorIds)
            ->where('role', 'vendor')
            ->where('is_active', true)
            ->get();

        foreach ($vendors as $vendor) {
            // Send to all enabled emails if global preference is on
            $notification = new \App\Notifications\QuoteRequestPublishedNotification($quoteRequest);
            $vendor->notifyVendorEmails($notification);
        }
    }

    /**
     * Accept quote and close request
     */
    public function acceptQuote(QuoteRequest $quoteRequest, VendorQuote $quote): Booking
    {
        if (!$quoteRequest->canSelectQuote()) {
            throw new \Exception('Cannot select quote at this time');
        }

        if ($quote->quote_request_id !== $quoteRequest->id) {
            throw new \Exception('Quote does not belong to this request');
        }

        if (!$quote->canAccept()) {
            throw new \Exception('Quote cannot be accepted in its current state');
        }

        return DB::transaction(function () use ($quoteRequest, $quote) {
            // Use VendorQuoteService to accept and create booking
            $quoteService = app(VendorQuoteService::class);
            $booking = $quoteService->acceptQuote($quote);

            // Select the quote in the request
            $quoteRequest->selectQuote($quote);

            // Close the request
            $this->closeRequest($quoteRequest);

            return $booking;
        });
    }

    /**
     * Close quote request
     */
    public function closeRequest(QuoteRequest $quoteRequest): QuoteRequest
    {
        $quoteRequest->close();

        // Notify customer
        $quoteRequest->customer->notify(new QuoteRequestClosedNotification($quoteRequest));

        return $quoteRequest;
    }

    /**
     * Cancel quote request
     */
    public function cancelRequest(QuoteRequest $quoteRequest, string $reason = null): QuoteRequest
    {
        $quoteRequest->cancel();

        // Optionally notify vendors who submitted quotes
        $this->notifyVendorsOfCancellation($quoteRequest, $reason);

        return $quoteRequest;
    }

    /**
     * Mark expired quote requests
     */
    public function markExpiredRequests(): int
    {
        $expiredRequests = QuoteRequest::where('status', QuoteRequest::STATUS_PUBLISHED)
            ->orWhere('status', QuoteRequest::STATUS_QUOTES_RECEIVED)
            ->where('response_deadline', '<', now())
            ->get();

        foreach ($expiredRequests as $request) {
            $request->markExpired();
        }

        return $expiredRequests->count();
    }

    /**
     * Get quote comparison for customer
     */
    public function getQuoteComparison(QuoteRequest $quoteRequest): array
    {
        return $quoteRequest->getQuotesComparison();
    }

    /**
     * Check if vendor can submit quote
     */
    public function canVendorSubmitQuote(QuoteRequest $quoteRequest, User $vendor): bool
    {
        // Check if request is accepting quotes
        if (!$quoteRequest->canReceiveQuotes()) {
            return false;
        }

        // Check if vendor is eligible
        if (!$quoteRequest->isVendorEligible($vendor->id)) {
            return false;
        }

        // Check if vendor hasn't already submitted
        if ($quoteRequest->hasVendorSubmittedQuote($vendor->id)) {
            return false;
        }

        return true;
    }

    /**
     * Submit vendor quote for request
     */
    public function submitVendorQuote(QuoteRequest $quoteRequest, User $vendor, array $quoteData): VendorQuote
    {
        if (!$this->canVendorSubmitQuote($quoteRequest, $vendor)) {
            throw new \Exception('Vendor cannot submit quote for this request');
        }

        $quoteService = app(VendorQuoteService::class);

        return DB::transaction(function () use ($quoteRequest, $vendor, $quoteData, $quoteService) {
            // Create quote
            $quote = $quoteService->createFromQuoteRequest($quoteRequest, $vendor, $quoteData);

            // If configured to auto-send, send the quote
            if ($quoteData['auto_send'] ?? true) {
                $quoteService->sendQuote($quote);
            }

            // Update request status if first quote
            if ($quoteRequest->quotes_received_count === 1) {
                $quoteRequest->update(['status' => QuoteRequest::STATUS_QUOTES_RECEIVED]);
            }

            return $quote;
        });
    }

    /**
     * Get pending requests for vendor
     */
    public function getPendingRequestsForVendor(User $vendor): \Illuminate\Database\Eloquent\Collection
    {
        return QuoteRequest::active()
            ->whereHas('hoarding', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->orWhere(function ($query) use ($vendor) {
                $query->where('open_to_all_vendors', true);
            })
            ->orWhereJsonContains('invited_vendor_ids', $vendor->id)
            ->get()
            ->filter(function ($request) use ($vendor) {
                return !$request->hasVendorSubmittedQuote($vendor->id);
            });
    }

    /**
     * Get customer's quote requests with statistics
     */
    public function getCustomerRequests(User $customer, array $filters = [])
    {
        $query = QuoteRequest::forCustomer($customer->id)
            ->with(['hoarding', 'quotes.vendor', 'selectedQuote']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['hoarding_id'])) {
            $query->where('hoarding_id', $filters['hoarding_id']);
        }

        return $query->latest()->get()->map(function ($request) {
            return [
                'id' => $request->id,
                'request_number' => $request->request_number,
                'hoarding' => [
                    'id' => $request->hoarding->id,
                    'title' => $request->hoarding->title,
                    'location' => $request->hoarding->location,
                ],
                'dates' => [
                    'start' => $request->preferred_start_date->format('Y-m-d'),
                    'end' => $request->preferred_end_date->format('Y-m-d'),
                    'duration' => $request->duration_days . ' ' . $request->duration_type,
                ],
                'status' => $request->status,
                'quotes_count' => $request->quotes_received_count,
                'deadline' => $request->response_deadline?->format('Y-m-d H:i'),
                'days_until_deadline' => $request->getDaysUntilDeadline(),
                'selected_quote_id' => $request->selected_quote_id,
                'created_at' => $request->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    /**
     * Notify vendors of cancellation
     */
    protected function notifyVendorsOfCancellation(QuoteRequest $quoteRequest, ?string $reason): void
    {
        $quotes = $quoteRequest->quotes()->with('vendor')->get();

        foreach ($quotes as $quote) {
            // Send cancellation notification
            // $quote->vendor->notify(new QuoteRequestCancelledNotification($quoteRequest, $reason));
        }
    }
}
