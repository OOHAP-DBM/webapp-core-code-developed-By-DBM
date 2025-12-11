<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSequence;
use App\Models\Booking;
use App\Models\QuotationMilestone;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

/**
 * MilestoneInvoiceService
 * 
 * Generates separate invoices for each milestone payment.
 * Does NOT modify existing InvoiceService - works alongside it.
 */
class MilestoneInvoiceService
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Generate invoice for a milestone payment
     * 
     * @param QuotationMilestone $milestone
     * @param Booking $booking
     * @param int|null $createdBy
     * @return Invoice
     */
    public function generateMilestoneInvoice(
        QuotationMilestone $milestone,
        Booking $booking,
        ?int $createdBy = null
    ): Invoice {
        return DB::transaction(function () use ($milestone, $booking, $createdBy) {
            // Get company settings
            $companySettings = $this->getCompanySettings();
            
            // Get customer details
            $customer = $booking->customer;
            
            // Determine if intra-state or inter-state
            $isIntraState = $this->isIntraStateTansaction(
                $companySettings['state_code'],
                $customer->billing_state_code ?? $companySettings['state_code']
            );
            
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $this->generateMilestoneInvoiceNumber($milestone),
                'financial_year' => InvoiceSequence::getCurrentFinancialYear(),
                'invoice_date' => now(),
                'invoice_type' => Invoice::TYPE_MILESTONE_PAYMENT,
                'booking_id' => $booking->id,
                'booking_payment_id' => $milestone->payment_transaction_id,
                'quotation_milestone_id' => $milestone->id,
                
                // Seller (Company) details
                'seller_name' => $companySettings['name'],
                'seller_gstin' => $companySettings['gstin'],
                'seller_address' => $companySettings['address'],
                'seller_city' => $companySettings['city'],
                'seller_state' => $companySettings['state'],
                'seller_state_code' => $companySettings['state_code'],
                'seller_pincode' => $companySettings['pincode'],
                'seller_pan' => $companySettings['pan'] ?? null,
                
                // Buyer (Customer) details
                'customer_id' => $customer->id,
                'buyer_name' => $customer->company_name ?? $customer->name,
                'buyer_gstin' => $customer->gstin,
                'buyer_address' => $customer->billing_address ?? $customer->address,
                'buyer_city' => $customer->billing_city ?? $customer->city,
                'buyer_state' => $customer->billing_state ?? $companySettings['state'],
                'buyer_state_code' => $customer->billing_state_code ?? $companySettings['state_code'],
                'buyer_pincode' => $customer->billing_pincode ?? $customer->pincode,
                'buyer_pan' => $customer->pan,
                'buyer_type' => $customer->customer_type ?? 'individual',
                'buyer_email' => $customer->email,
                'buyer_phone' => $customer->phone,
                
                // GST details
                'place_of_supply' => $customer->billing_state ?? $companySettings['state'],
                'is_intra_state' => $isIntraState,
                'is_reverse_charge' => $this->checkReverseCharge($customer),
                'supply_type' => 'services',
                
                // Status and metadata
                'status' => Invoice::STATUS_PAID,
                'issued_at' => now(),
                'paid_at' => $milestone->paid_at,
                'due_date' => $milestone->due_date,
                'created_by' => $createdBy,
                'terms_conditions' => $companySettings['invoice_terms'],
                'payment_terms' => $companySettings['payment_terms_text'],
                'notes' => "Milestone Payment: {$milestone->title} (Milestone {$milestone->sequence_no}/{$booking->milestone_total})",
            ]);
            
            // Add invoice items
            $this->addMilestoneInvoiceItem($invoice, $milestone, $booking, $isIntraState);
            
            // Calculate and update totals
            $this->recalculateInvoiceTotals($invoice);
            
            // Generate PDF
            $this->generatePDF($invoice);
            
            // Generate QR Code
            $this->generateQRCode($invoice);
            
            // Store invoice number in milestone
            $milestone->update(['invoice_number' => $invoice->invoice_number]);
            
            return $invoice->fresh(['items', 'customer']);
        });
    }

    /**
     * Generate milestone-specific invoice number
     * Format: INV/FY/SEQ-M-MILESTONE_SEQ
     * Example: INV/2024-25/0042-M-1
     */
    protected function generateMilestoneInvoiceNumber(QuotationMilestone $milestone): string
    {
        $baseNumber = InvoiceSequence::getNextInvoiceNumber();
        
        // Append milestone sequence (e.g., INV/2024-25/0042 becomes INV/2024-25/0042-M-1)
        return $baseNumber . '-M-' . $milestone->sequence_no;
    }

    /**
     * Add milestone payment as invoice item
     */
    protected function addMilestoneInvoiceItem(
        Invoice $invoice,
        QuotationMilestone $milestone,
        Booking $booking,
        bool $isIntraState
    ): void {
        $gstRate = $this->getGSTRate();
        $hsnCode = Setting::getValue('hsn_advertising_services', '998599');
        
        // Create description
        $description = "{$milestone->title}";
        if ($milestone->description) {
            $description .= " - {$milestone->description}";
        }
        $description .= " (Milestone {$milestone->sequence_no} of {$booking->milestone_total})";
        
        // Create line item
        $item = new InvoiceItem([
            'line_number' => 1,
            'item_type' => 'milestone',
            'description' => $description,
            'hsn_sac_code' => $hsnCode,
            'hoarding_id' => $booking->hoarding_id,
            'quantity' => 1,
            'unit' => 'service',
            'rate' => $milestone->calculated_amount,
            'service_start_date' => $booking->start_date,
            'service_end_date' => $booking->end_date,
            'duration_days' => $booking->duration_days,
            'discount_percent' => 0,
            'discount_amount' => 0,
        ]);
        
        // Calculate amounts with GST
        $item->calculateAmounts($isIntraState, $gstRate);
        
        $invoice->items()->save($item);
    }

    /**
     * Recalculate invoice totals from items
     */
    protected function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $items = $invoice->items;
        
        $subtotal = $items->sum('amount');
        $discountAmount = $items->sum('discount_amount');
        $taxableAmount = $items->sum('taxable_amount');
        $cgstAmount = $items->sum('cgst_amount');
        $sgstAmount = $items->sum('sgst_amount');
        $igstAmount = $items->sum('igst_amount');
        $totalTax = $items->sum('total_tax');
        $totalAmount = $items->sum('total_amount');
        
        // Get tax rate from first item
        $firstItem = $items->first();
        $cgstRate = $firstItem?->cgst_rate;
        $sgstRate = $firstItem?->sgst_rate;
        $igstRate = $firstItem?->igst_rate;
        
        // Round off calculation
        $grandTotal = round($totalAmount);
        $roundOff = $grandTotal - $totalAmount;
        
        $invoice->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'taxable_amount' => $taxableAmount,
            'cgst_rate' => $cgstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_rate' => $sgstRate,
            'sgst_amount' => $sgstAmount,
            'igst_rate' => $igstRate,
            'igst_amount' => $igstAmount,
            'total_tax' => $totalTax,
            'total_amount' => $totalAmount,
            'round_off' => $roundOff,
            'grand_total' => $grandTotal,
        ]);
    }

    /**
     * Generate PDF for milestone invoice
     */
    protected function generatePDF(Invoice $invoice): string
    {
        $pdf = Pdf::loadView('invoices.milestone-invoice', [
            'invoice' => $invoice->load(['items', 'customer', 'milestone']),
        ]);
        
        $filename = 'invoices/milestone/' . $invoice->invoice_number . '.pdf';
        $filename = str_replace('/', '_', $filename);
        
        Storage::disk('public')->put($filename, $pdf->output());
        
        $invoice->update(['pdf_path' => $filename]);
        
        return $filename;
    }

    /**
     * Generate QR Code for milestone invoice
     */
    protected function generateQRCode(Invoice $invoice): string
    {
        // Create QR code data
        $qrData = $this->generateQRCodeData($invoice);
        
        $qrCode = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($qrData);
        
        $filename = 'qrcodes/milestone/' . $invoice->invoice_number . '.png';
        $filename = str_replace('/', '_', $filename);
        
        Storage::disk('public')->put($filename, $qrCode);
        
        $invoice->update([
            'qr_code_path' => $filename,
            'qr_code_data' => $qrData,
        ]);
        
        return $filename;
    }

    /**
     * Generate QR code data
     */
    protected function generateQRCodeData(Invoice $invoice): string
    {
        return json_encode([
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
            'amount' => $invoice->grand_total,
            'gstin' => $invoice->seller_gstin,
            'type' => 'milestone',
        ]);
    }

    /**
     * Get company settings
     */
    protected function getCompanySettings(): array
    {
        return [
            'name' => Setting::getValue('company_name', config('app.name')),
            'gstin' => Setting::getValue('company_gstin'),
            'address' => Setting::getValue('company_address'),
            'city' => Setting::getValue('company_city'),
            'state' => Setting::getValue('company_state'),
            'state_code' => Setting::getValue('company_state_code'),
            'pincode' => Setting::getValue('company_pincode'),
            'pan' => Setting::getValue('company_pan'),
            'payment_terms_days' => Setting::getValue('invoice_payment_terms_days', 30),
            'invoice_terms' => Setting::getValue('invoice_terms_conditions'),
            'payment_terms_text' => Setting::getValue('invoice_payment_terms_text'),
        ];
    }

    /**
     * Check if transaction is intra-state (within same state)
     */
    protected function isIntraStateTansaction(string $sellerStateCode, string $buyerStateCode): bool
    {
        return $sellerStateCode === $buyerStateCode;
    }

    /**
     * Check if reverse charge is applicable
     */
    protected function checkReverseCharge($customer): bool
    {
        // Reverse charge not applicable for milestone payments
        return false;
    }

    /**
     * Get GST rate for advertising services
     */
    protected function getGSTRate(): float
    {
        return (float) Setting::getValue('gst_rate_advertising', 18.0);
    }

    /**
     * Get all milestones for a booking with their invoice status
     */
    public function getMilestoneInvoicesSummary(Booking $booking): array
    {
        if (!$booking->hasMilestones()) {
            return [];
        }

        $milestones = $booking->getMilestones();
        
        return $milestones->map(function (QuotationMilestone $milestone) {
            $invoice = $milestone->invoice_number ? 
                Invoice::where('invoice_number', $milestone->invoice_number)->first() : 
                null;

            return [
                'milestone_id' => $milestone->id,
                'sequence_no' => $milestone->sequence_no,
                'title' => $milestone->title,
                'amount' => $milestone->calculated_amount,
                'status' => $milestone->status,
                'due_date' => $milestone->due_date?->format('Y-m-d'),
                'paid_at' => $milestone->paid_at?->format('Y-m-d H:i:s'),
                'invoice_number' => $milestone->invoice_number,
                'invoice_id' => $invoice?->id,
                'invoice_pdf_url' => $invoice?->pdf_path ? 
                    Storage::disk('public')->url($invoice->pdf_path) : 
                    null,
            ];
        })->toArray();
    }

    /**
     * Regenerate invoice for a milestone (if needed)
     */
    public function regenerateMilestoneInvoice(
        QuotationMilestone $milestone,
        Booking $booking
    ): Invoice {
        // Delete old invoice if exists
        if ($milestone->invoice_number) {
            $oldInvoice = Invoice::where('invoice_number', $milestone->invoice_number)->first();
            if ($oldInvoice) {
                // Delete PDF and QR code
                if ($oldInvoice->pdf_path) {
                    Storage::disk('public')->delete($oldInvoice->pdf_path);
                }
                if ($oldInvoice->qr_code_path) {
                    Storage::disk('public')->delete($oldInvoice->qr_code_path);
                }
                
                $oldInvoice->delete();
            }
        }

        // Generate new invoice
        return $this->generateMilestoneInvoice($milestone, $booking);
    }
}
