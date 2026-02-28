<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSequence;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\User;
use App\Models\Setting;
use App\Services\TaxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class InvoiceService
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Generate invoice for a booking payment
     */
    public function generateInvoiceForBooking(
        Booking $booking,
        ?BookingPayment $bookingPayment = null,
        string $invoiceType = Invoice::TYPE_FULL_PAYMENT,
        ?int $createdBy = null
    ): Invoice {
        return DB::transaction(function () use ($booking, $bookingPayment, $invoiceType, $createdBy) {
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
                'invoice_number' => InvoiceSequence::getNextInvoiceNumber(),
                'financial_year' => InvoiceSequence::getCurrentFinancialYear(),
                'invoice_date' => now(),
                'invoice_type' => $invoiceType,
                'booking_id' => $booking->id,
                'booking_payment_id' => $bookingPayment?->id,
                
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
                'status' => Invoice::STATUS_ISSUED,
                'issued_at' => now(),
                'due_date' => now()->addDays($companySettings['payment_terms_days'] ?? 30),
                'created_by' => $createdBy,
                'terms_conditions' => $companySettings['invoice_terms'],
                'payment_terms' => $companySettings['payment_terms_text'],
            ]);
            
            // Add invoice items
            $this->addInvoiceItems($invoice, $booking, $isIntraState);
            
            // Calculate and update totals
            $this->recalculateInvoiceTotals($invoice);
            
            // Generate PDF
            $this->generatePDF($invoice);
            
            // Generate QR Code
            $this->generateQRCode($invoice);
            
            return $invoice->fresh(['items', 'customer']);
        });
    }

    /**
     * Add invoice items from booking
     */
    protected function addInvoiceItems(Invoice $invoice, Booking $booking, bool $isIntraState): void
    {
        $lineNumber = 1;
        $gstRate = $this->getGSTRate();
        
        // Get hoarding details
        $hoarding = $booking->hoarding;
        // $hsnCode = Setting::getValue('hsn_advertising_services', '998599');
        $hsnCode = Setting::get('hsn_advertising_services', '998599');

        
        // Create line item for hoarding booking
        $item = new InvoiceItem([
            'line_number' => $lineNumber++,
            'item_type' => 'hoarding',
            'description' => $hoarding ? "Outdoor Advertising - {$hoarding->title}" : 'Hoarding Advertisement',
            'hsn_sac_code' => $hsnCode,
            'hoarding_id' => $hoarding?->id,
            'quantity' => $booking->duration_days ?? 1,
            'unit' => 'days',
            'rate' => $booking->total_amount / ($booking->duration_days ?? 1),
            'service_start_date' => $booking->start_date,
            'service_end_date' => $booking->end_date,
            'duration_days' => $booking->duration_days,
            'discount_percent' => 0,
            'discount_amount' => 0,
        ]);
        
        // Calculate amounts
        $item->calculateAmounts($isIntraState, $gstRate);
        
        $invoice->items()->save($item);
    }

    /**
     * Recalculate invoice totals from items
     */
    public function recalculateInvoiceTotals(Invoice $invoice): void
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
        
        // Get tax rate from first item (all items should have same rate)
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
     * Generate PDF for invoice
     */
    public function generatePDF(Invoice $invoice): string
    {
        $pdf = Pdf::loadView('invoices.gst-invoice', [
            'invoice' => $invoice->load(['items', 'customer']),
        ]);
        
        $filename = 'invoices/' . $invoice->invoice_number . '.pdf';
        $filename = str_replace('/', '_', $filename);
        
        Storage::disk('public')->put($filename, $pdf->output());
        
        $invoice->update(['pdf_path' => $filename]);
        
        return $filename;
    }

    /**
     * Generate QR Code for invoice
     */
    public function generateQRCode(Invoice $invoice): string
    {
        // Create QR code data (can be UPI link or invoice data)
        $qrData = $this->generateQRCodeData($invoice);

        if (!class_exists('SimpleSoftwareIO\\QrCode\\Facades\\QrCode')) {
            \Log::warning('QrCode facade not available; skipping invoice QR generation', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            $invoice->update([
                'qr_code_path' => null,
                'qr_code_data' => $qrData,
            ]);

            return '';
        }
        
        try {
            $qrCode = QrCode::format('png')
                ->size(200)
                ->margin(1)
                ->generate($qrData);

            $filename = 'qrcodes/' . $invoice->invoice_number . '.png';
            $filename = str_replace('/', '_', $filename);

            Storage::disk('public')->put($filename, $qrCode);

            $invoice->update([
                'qr_code_path' => $filename,
                'qr_code_data' => $qrData,
            ]);

            return $filename;
        } catch (\Throwable $e) {
            \Log::warning('Invoice QR generation failed; continuing without QR code', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'error' => $e->getMessage(),
            ]);

            $invoice->update([
                'qr_code_path' => null,
                'qr_code_data' => $qrData,
            ]);

            return '';
        }
    }

    /**
     * Generate QR code data (invoice details or UPI)
     */
    protected function generateQRCodeData(Invoice $invoice): string
    {
        // Simple invoice data format
        return json_encode([
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
            'amount' => $invoice->grand_total,
            'gstin' => $invoice->seller_gstin,
        ]);
    }

    /**
     * Send invoice via email
     */
    public function sendInvoiceEmail(Invoice $invoice, ?array $recipients = null): bool
    {
        try {
            $recipients = $recipients ?? [$invoice->buyer_email];
            
            // Send email logic here
            // Mail::to($recipients)->send(new InvoiceEmail($invoice));
            
            $invoice->markAsSent(implode(', ', $recipients));
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Invoice email failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if transaction is intra-state
     */
    protected function isIntraStateTansaction(string $sellerStateCode, string $buyerStateCode): bool
    {
        return $sellerStateCode === $buyerStateCode;
    }

    /**
     * Check if reverse charge applicable
     */
    protected function checkReverseCharge(User $customer): bool
    {
        // Reverse charge: B2B with GSTIN
        return $customer->customer_type === 'business' && !empty($customer->gstin);
    }

    /**
     * Get GST rate from settings
     */
    protected function getGSTRate(): float
    {
        return (float) Setting::get('gst_rate', 18.0);
    }

    /**
     * Get company/seller settings
     */
    protected function getCompanySettings(): array
    {
        return [
            'name' => Setting::get('company_name', 'OOHAPP Private Limited'),
            'gstin' => Setting::get('company_gstin', '27AABCU9603R1ZX'),
            'address' => Setting::get('company_address', 'Mumbai, Maharashtra'),
            'city' => Setting::get('company_city', 'Mumbai'),
            'state' => Setting::get('company_state', 'Maharashtra'),
            'state_code' => Setting::get('company_state_code', '27'),
            'pincode' => Setting::get('company_pincode', '400001'),
            'pan' => Setting::get('company_pan', null),
            'invoice_terms' => Setting::get('invoice_terms_conditions', 
                "1. Payment is due within 30 days.\n2. Please quote invoice number in all correspondence.\n3. Subject to Mumbai jurisdiction only."),
            'payment_terms_text' => Setting::get('invoice_payment_terms', 'Net 30 Days'),
            'payment_terms_days' => (int) Setting::get('invoice_payment_days', 30),
        ];
    }

    /**
     * Validate GSTIN format
     */
    public function validateGSTIN(?string $gstin): bool
    {
        if (empty($gstin)) {
            return false;
        }
        
        // GSTIN format: 2 digits (state) + 10 chars (PAN) + 1 char (entity) + 1 char (Z) + 1 char (checksum)
        // Example: 27AABCU9603R1ZX
        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
        
        return preg_match($pattern, $gstin) === 1;
    }

    /**
     * Get state code from GSTIN
     */
    public function getStateCodeFromGSTIN(string $gstin): ?string
    {
        if (!$this->validateGSTIN($gstin)) {
            return null;
        }
        
        return substr($gstin, 0, 2);
    }

    /**
     * Generate invoice for POS booking
     */
    public function generatePOSInvoice($posBooking, ?int $createdBy = null): Invoice
    {
        // Similar to generateInvoiceForBooking but for POS
        // Implementation based on POS booking structure
        return $this->generateInvoiceForBooking(
            $posBooking->booking ?? $posBooking,
            null,
            Invoice::TYPE_POS,
            $createdBy
        );
    }

    /**
     * Generate milestone invoice
     */
    public function generateMilestoneInvoice($milestone, ?int $createdBy = null): Invoice
    {
        // Implementation for milestone-based invoices
        // To be expanded based on milestone structure
        throw new \Exception('Milestone invoices not yet implemented');
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice(Invoice $invoice, string $reason, ?int $cancelledBy = null): bool
    {
        if (!$invoice->canCancel()) {
            throw new \Exception('Invoice cannot be cancelled');
        }
        
        return $invoice->cancel($reason, $cancelledBy);
    }

    /**
     * Mark invoice as paid
     */
    public function markInvoiceAsPaid(Invoice $invoice, ?float $amount = null, ?\DateTime $paidAt = null): bool
    {
        return $invoice->markAsPaid($amount, $paidAt);
    }

    /**
     * Get financial year summary
     */
    public function getFinancialYearSummary(string $financialYear): array
    {
        $invoices = Invoice::byFinancialYear($financialYear)->get();
        
        return [
            'financial_year' => $financialYear,
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('grand_total'),
            'total_paid' => $invoices->where('status', Invoice::STATUS_PAID)->sum('grand_total'),
            'total_unpaid' => $invoices->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])->sum('grand_total'),
            'total_cancelled' => $invoices->where('status', Invoice::STATUS_CANCELLED)->count(),
        ];
    }


     /**
     * Generate invoice for a POS booking (Modules\POS\Models\POSBooking)
     */
    public function generateInvoiceForPOSBooking(
        \Modules\POS\Models\POSBooking $posBooking,
        ?int $createdBy = null
    ): Invoice {
        return DB::transaction(function () use ($posBooking, $createdBy) {
            // Get company settings
            $companySettings = $this->getCompanySettings();

            // Get customer details
            $customer = $posBooking->customer_id
                ? \App\Models\User::find($posBooking->customer_id)
                : null;

            // Determine if intra-state or inter-state
            $isIntraState = $this->isIntraStateTansaction(
                $companySettings['state_code'],
                $customer?->state_code ?? $companySettings['state_code']
            );

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => InvoiceSequence::getNextInvoiceNumber(),
                'financial_year' => InvoiceSequence::getCurrentFinancialYear(),
                'invoice_date' => now(),
                'invoice_type' => Invoice::TYPE_POS,
                'pos_booking_id' => $posBooking->id,
                'booking_id' => null,
                'booking_payment_id' => null,

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
                'customer_id' => $customer?->id,
                'buyer_name' => $customer?->company_name ?? $customer?->name ?? $posBooking->customer_name,
                'buyer_gstin' => $customer?->gstin,
                'buyer_address' => $customer?->billing_address ?? $customer?->address ?? $posBooking->customer_address,
                'buyer_city' => $customer?->billing_city ?? $customer?->city,
                'buyer_state' => $customer?->billing_state ?? $companySettings['state'],
                'buyer_state_code' => $customer?->billing_state_code ?? $companySettings['state_code'],
                'buyer_pincode' => $customer?->billing_pincode ?? $customer?->pincode,
                'buyer_pan' => $customer?->pan,
                'buyer_type' => $customer?->customer_type ?? 'individual',
                'buyer_email' => $customer?->email ?? null,
                'buyer_phone' => $customer?->phone ?? null,

                // GST details
                'place_of_supply' => $customer?->billing_state ?? $companySettings['state'],
                'is_intra_state' => $isIntraState,
                'is_reverse_charge' => $customer ? $this->checkReverseCharge($customer) : false,
                'supply_type' => 'services',

                // Status and metadata
                'status' => Invoice::STATUS_ISSUED,
                'issued_at' => now(),
                'due_date' => now()->addDays($companySettings['payment_terms_days'] ?? 30),
                'created_by' => $createdBy,
                'terms_conditions' => $companySettings['invoice_terms'],
                'payment_terms' => $companySettings['payment_terms_text'],
                'subtotal' => 1000, // Will be updated later
                'taxable_amount' => 1200, // Will be updated later
                'total_tax' => 200, // Will be updated later
                'total_amount' => 1400, // Will be updated later    
                'grand_total' => 1400, // Will be updated later
                
            ]);

            // Add invoice items for each hoarding in the POS booking
            $this->addPOSInvoiceItems($invoice, $posBooking, $isIntraState);

            // Calculate and update totals
            $this->recalculateInvoiceTotals($invoice);

            // Generate PDF
            $this->generatePDF($invoice);

            // Generate QR Code
            $this->generateQRCode($invoice);

            return $invoice->fresh(['items']);
        });
    }

    /**
     * Add invoice items from POS booking
     */
    protected function addPOSInvoiceItems(Invoice $invoice, \Modules\POS\Models\POSBooking $posBooking, bool $isIntraState): void
    {
        $lineNumber = 1;
        $gstRate = $this->getGSTRate();
        $hsnCode = Setting::get('hsn_advertising_services', '998599');

        // Loop through all hoardings in the POS booking
        foreach ($posBooking->bookingHoardings as $bookingHoarding) {
            $hoarding = $bookingHoarding->hoarding;
            $item = new InvoiceItem([
                'line_number' => $lineNumber++,
                'item_type' => 'hoarding',
                'description' => $hoarding ? "Outdoor Advertising - {$hoarding->title}" : 'Hoarding Advertisement',
                'hsn_sac_code' => $hsnCode,
                'hoarding_id' => $hoarding?->id,
                'quantity' => $bookingHoarding->duration_days ?? 1,
                'unit' => 'days',
                'rate' => $bookingHoarding->total_amount / ($bookingHoarding->duration_days ?? 1),
                'service_start_date' => $bookingHoarding->start_date,
                'service_end_date' => $bookingHoarding->end_date,
                'duration_days' => $bookingHoarding->duration_days,
                'discount_percent' => 0,
                'discount_amount' => 0,
            ]);
            $item->calculateAmounts($isIntraState, $gstRate);
            $invoice->items()->save($item);
        }
    }
}
