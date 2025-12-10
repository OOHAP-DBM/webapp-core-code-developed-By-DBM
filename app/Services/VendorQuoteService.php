<?php

namespace App\Services;

use App\Models\VendorQuote;
use App\Models\QuoteRequest;
use App\Models\Enquiry;
use App\Models\User;
use App\Models\Hoarding;
use App\Models\Booking;
use App\Notifications\VendorQuoteSentNotification;
use App\Notifications\QuoteAcceptedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VendorQuoteService
{
    /**
     * Create quote from quote request
     */
    public function createFromQuoteRequest(QuoteRequest $quoteRequest, User $vendor, array $data): VendorQuote
    {
        return DB::transaction(function () use ($quoteRequest, $vendor, $data) {
            $quote = VendorQuote::create([
                'quote_request_id' => $quoteRequest->id,
                'hoarding_id' => $quoteRequest->hoarding_id,
                'customer_id' => $quoteRequest->customer_id,
                'vendor_id' => $vendor->id,
                'start_date' => $data['start_date'] ?? $quoteRequest->preferred_start_date,
                'end_date' => $data['end_date'] ?? $quoteRequest->preferred_end_date,
                'duration_days' => $data['duration_days'] ?? $quoteRequest->duration_days,
                'duration_type' => $data['duration_type'] ?? $quoteRequest->duration_type,
                'base_price' => $data['base_price'],
                'printing_cost' => $data['printing_cost'] ?? 0,
                'mounting_cost' => $data['mounting_cost'] ?? 0,
                'survey_cost' => $data['survey_cost'] ?? 0,
                'lighting_cost' => $data['lighting_cost'] ?? 0,
                'maintenance_cost' => $data['maintenance_cost'] ?? 0,
                'other_charges' => $data['other_charges'] ?? 0,
                'other_charges_description' => $data['other_charges_description'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'tax_percentage' => $data['tax_percentage'] ?? config('pricing.default_tax_rate', 18),
                'vendor_notes' => $data['vendor_notes'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? $this->getDefaultTerms(),
            ]);

            $quote->recalculateTotals();

            return $quote;
        });
    }

    /**
     * Create quote from enquiry
     */
    public function createFromEnquiry(Enquiry $enquiry, User $vendor, array $data): VendorQuote
    {
        return DB::transaction(function () use ($enquiry, $vendor, $data) {
            $quote = VendorQuote::create([
                'enquiry_id' => $enquiry->id,
                'hoarding_id' => $enquiry->hoarding_id,
                'customer_id' => $enquiry->customer_id,
                'vendor_id' => $vendor->id,
                'start_date' => $data['start_date'] ?? $enquiry->preferred_start_date,
                'end_date' => $data['end_date'] ?? $enquiry->preferred_end_date,
                'duration_days' => $data['duration_days'] ?? $enquiry->duration,
                'duration_type' => $data['duration_type'] ?? VendorQuote::DURATION_DAYS,
                'base_price' => $data['base_price'],
                'printing_cost' => $data['printing_cost'] ?? 0,
                'mounting_cost' => $data['mounting_cost'] ?? 0,
                'survey_cost' => $data['survey_cost'] ?? 0,
                'lighting_cost' => $data['lighting_cost'] ?? 0,
                'maintenance_cost' => $data['maintenance_cost'] ?? 0,
                'other_charges' => $data['other_charges'] ?? 0,
                'other_charges_description' => $data['other_charges_description'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'tax_percentage' => $data['tax_percentage'] ?? config('pricing.default_tax_rate', 18),
                'vendor_notes' => $data['vendor_notes'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? $this->getDefaultTerms(),
            ]);

            $quote->recalculateTotals();

            return $quote;
        });
    }

    /**
     * Update quote
     */
    public function updateQuote(VendorQuote $quote, array $data): VendorQuote
    {
        $quote->update($data);
        $quote->recalculateTotals();

        return $quote;
    }

    /**
     * Send quote to customer (generates PDF and sends email)
     */
    public function sendQuote(VendorQuote $quote): bool
    {
        if (!$quote->canSend()) {
            throw new \Exception('Quote cannot be sent in its current state');
        }

        return DB::transaction(function () use ($quote) {
            // Generate PDF
            $pdfPath = $this->generatePDF($quote);

            // Mark as sent
            $quote->markAsSent();
            $quote->update(['pdf_path' => $pdfPath, 'pdf_generated_at' => now()]);

            // Send email notification
            $quote->customer->notify(new VendorQuoteSentNotification($quote));

            return true;
        });
    }

    /**
     * Generate PDF for quote
     */
    public function generatePDF(VendorQuote $quote): string
    {
        $quote->load(['hoarding', 'customer', 'vendor']);

        $pdf = Pdf::loadView('pdf.vendor-quote', [
            'quote' => $quote,
        ]);

        $filename = $quote->getPdfFilename();
        $path = 'quotes/' . $quote->id . '/' . $filename;

        // Store PDF
        Storage::disk('private')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Accept quote and create booking
     */
    public function acceptQuote(VendorQuote $quote): Booking
    {
        if (!$quote->canAccept()) {
            throw new \Exception('Quote cannot be accepted in its current state');
        }

        return DB::transaction(function () use ($quote) {
            // Accept the quote
            $quote->accept();

            // Create booking from quote
            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'customer_id' => $quote->customer_id,
                'vendor_id' => $quote->vendor_id,
                'hoarding_id' => $quote->hoarding_id,
                'start_date' => $quote->start_date,
                'end_date' => $quote->end_date,
                'duration' => $quote->duration_days,
                'duration_type' => $quote->duration_type,
                'base_amount' => $quote->base_price,
                'printing_charges' => $quote->printing_cost,
                'mounting_charges' => $quote->mounting_cost,
                'survey_charges' => $quote->survey_cost,
                'lighting_charges' => $quote->lighting_cost,
                'maintenance_charges' => $quote->maintenance_cost,
                'other_charges' => $quote->other_charges,
                'discount_amount' => $quote->discount_amount,
                'tax_amount' => $quote->tax_amount,
                'total_amount' => $quote->grand_total,
                'status' => 'confirmed',
                'payment_status' => 'pending',
                'booking_snapshot' => [
                    'quote_id' => $quote->id,
                    'quote_number' => $quote->quote_number,
                    'quote_snapshot' => $quote->quote_snapshot,
                ],
            ]);

            // Update quote with booking reference
            $quote->update(['booking_id' => $booking->id]);

            // Notify vendor
            $quote->vendor->notify(new QuoteAcceptedNotification($quote, $booking));

            return $booking;
        });
    }

    /**
     * Reject quote
     */
    public function rejectQuote(VendorQuote $quote, string $reason = null): bool
    {
        if (!$quote->canReject()) {
            throw new \Exception('Quote cannot be rejected in its current state');
        }

        $quote->reject($reason);

        return true;
    }

    /**
     * Create revision of quote
     */
    public function createRevision(VendorQuote $quote, array $changes): VendorQuote
    {
        if (!$quote->canRevise()) {
            throw new \Exception('Quote cannot be revised in its current state');
        }

        return DB::transaction(function () use ($quote, $changes) {
            $revision = $quote->createRevision();

            // Apply changes
            $revision->update($changes);
            $revision->recalculateTotals();

            return $revision;
        });
    }

    /**
     * Calculate pricing breakdown
     */
    public function calculatePricing(array $data): array
    {
        $basePrice = $data['base_price'] ?? 0;
        $printingCost = $data['printing_cost'] ?? 0;
        $mountingCost = $data['mounting_cost'] ?? 0;
        $surveyCost = $data['survey_cost'] ?? 0;
        $lightingCost = $data['lighting_cost'] ?? 0;
        $maintenanceCost = $data['maintenance_cost'] ?? 0;
        $otherCharges = $data['other_charges'] ?? 0;

        $subtotal = $basePrice + $printingCost + $mountingCost + $surveyCost +
                    $lightingCost + $maintenanceCost + $otherCharges;

        $discountAmount = $data['discount_amount'] ?? 0;
        $discountPercentage = $data['discount_percentage'] ?? 0;

        if ($discountPercentage > 0) {
            $discountAmount = ($subtotal * $discountPercentage) / 100;
        }

        $afterDiscount = $subtotal - $discountAmount;

        $taxPercentage = $data['tax_percentage'] ?? config('pricing.default_tax_rate', 18);
        $taxAmount = ($afterDiscount * $taxPercentage) / 100;

        $grandTotal = $afterDiscount + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Get default terms and conditions
     */
    protected function getDefaultTerms(): array
    {
        return [
            'payment' => [
                '50% advance payment required',
                'Balance payment before campaign start',
                'Payment accepted via bank transfer or online payment',
            ],
            'cancellation' => [
                'Free cancellation up to 7 days before start date',
                '50% refund for cancellation 3-7 days before start',
                'No refund for cancellation within 3 days',
            ],
            'installation' => [
                'Installation to be done 2 days before campaign start',
                'Removal within 2 days after campaign end',
                'Any damage to property will be vendor\'s responsibility',
            ],
            'maintenance' => [
                'Regular maintenance during campaign period',
                'Replacement of damaged material at actual cost',
                'Emergency support available 24/7',
            ],
            'validity' => [
                'This quote is valid for 7 days from the date of issue',
                'Prices are subject to change after validity period',
            ],
        ];
    }

    /**
     * Generate unique booking number
     */
    protected function generateBookingNumber(): string
    {
        do {
            $number = 'BK-' . strtoupper(\Illuminate\Support\Str::random(10));
        } while (Booking::where('booking_number', $number)->exists());

        return $number;
    }
}
