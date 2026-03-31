<?php
// app/Services/InvoiceService.php
// KEY FIXES:
// 1. addPOSInvoiceItems: Use hoarding_price (pre-tax base) correctly for rate calculation
// 2. calculateAmounts: discount_amount is set BEFORE calling calculateAmounts, so don't overwrite it
// 3. New method: updatePaymentStatus() to sync invoice when payment is received

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
use Illuminate\Support\Facades\Log;
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

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: Generate invoice for a regular booking payment
    // ─────────────────────────────────────────────────────────────────────────

    public function generateInvoiceForBooking(
        Booking $booking,
        ?BookingPayment $bookingPayment = null,
        string $invoiceType = Invoice::TYPE_FULL_PAYMENT,
        ?int $createdBy = null
    ): Invoice {
        return DB::transaction(function () use ($booking, $bookingPayment, $invoiceType, $createdBy) {
            $companySettings = $this->getCompanySettings();
            $customer        = $booking->customer;

            $isIntraState = $this->isIntraStateTansaction(
                $companySettings['state_code'],
                $customer->billing_state_code ?? $companySettings['state_code']
            );

            $invoice = Invoice::create([
                'invoice_number'     => InvoiceSequence::getNextInvoiceNumber(),
                'financial_year'     => InvoiceSequence::getCurrentFinancialYear(),
                'invoice_date'       => now(),
                'invoice_type'       => $invoiceType,
                'booking_id'         => $booking->id,
                'booking_payment_id' => $bookingPayment?->id,

                'seller_name'        => $companySettings['name'],
                'seller_gstin'       => $companySettings['gstin'],
                'seller_address'     => $companySettings['address'],
                'seller_city'        => $companySettings['city'],
                'seller_state'       => $companySettings['state'],
                'seller_state_code'  => $companySettings['state_code'],
                'seller_pincode'     => $companySettings['pincode'],
                'seller_pan'         => $companySettings['pan'] ?? null,

                'customer_id'        => $customer->id,
                'buyer_name'         => $customer->company_name ?? $customer->name,
                'buyer_gstin'        => $customer->gstin,
                  'buyer_address'      => $customer?->billing_address
                    ?? $customer?->address
                    ?? $posBooking->customer_address,
                'buyer_city'         => $customer->billing_city ?? $customer->city ?? $posBooking->customer_city,
                'buyer_state'        => $customer->billing_state ?? $customer->state ?? $companySettings['state'],
                'buyer_state_code'   => $customer->billing_state_code ?? $customer->state_code ?? $companySettings['state_code'],
                'buyer_pincode'      => $customer->billing_pincode ?? $customer->pincode ?? $posBooking->customer_pincode,
                'buyer_pan'          => $customer->pan,
                'buyer_type'         => $customer->customer_type ?? 'individual',
                'buyer_email'        => $customer->email,
                'buyer_phone'        => $customer->phone,

                'place_of_supply'    => $customer->billing_state ?? $customer->state ?? $companySettings['state'],
                'is_intra_state'     => $isIntraState,
                'is_reverse_charge'  => $this->checkReverseCharge($customer),
                'supply_type'        => 'services',

                'status'             => Invoice::STATUS_ISSUED,
                'issued_at'          => now(),
                'due_date'           => now()->addDays($companySettings['payment_terms_days'] ?? 30),
                'created_by'         => $createdBy,
                'terms_conditions'   => $companySettings['invoice_terms'],
                'payment_terms'      => $companySettings['payment_terms_text'],

                'subtotal'           => 0,
                'discount_amount'    => 0,
                'taxable_amount'     => 0,
                'total_tax'          => 0,
                'total_amount'       => 0,
                'grand_total'        => 0,
            ]);

            $this->addInvoiceItems($invoice, $booking, $isIntraState);
            $this->recalculateInvoiceTotals($invoice);
            $this->generatePDF($invoice);
            $this->generateQRCode($invoice);

            return $invoice->fresh(['items', 'customer']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: Generate invoice for a POS booking
    // ─────────────────────────────────────────────────────────────────────────

    public function generateInvoiceForPOSBooking(
        \Modules\POS\Models\POSBooking $posBooking,
        ?int $createdBy = null
    ): Invoice {
        return DB::transaction(function () use ($posBooking, $createdBy) {

            $companySettings = $this->getCompanySettings();

            $customer = $posBooking->customer_id
                ? \App\Models\User::find($posBooking->customer_id)
                : null;
          
            $isIntraState = $this->isIntraStateTansaction(
                $companySettings['state_code'],
                $customer?->billing_state_code ?? $customer?->state_code ?? $companySettings['state_code']
            );

            $invoice = Invoice::create([
                'invoice_number'     => InvoiceSequence::getNextInvoiceNumber(),
                'financial_year'     => InvoiceSequence::getCurrentFinancialYear(),
                'invoice_date'       => now(),
                'invoice_type'       => Invoice::TYPE_POS,
                'pos_booking_id'     => $posBooking->id,
                'booking_id'         => null,
                'booking_payment_id' => null,

                // Seller
                'seller_name'        => $companySettings['name'],
                'seller_gstin'       => $companySettings['gstin'],
                'seller_address'     => $companySettings['address'],
                'seller_city'        => $companySettings['city'],
                'seller_state'       => $companySettings['state'],
                'seller_state_code'  => $companySettings['state_code'],
                'seller_pincode'     => $companySettings['pincode'],
                'seller_pan'         => $companySettings['pan'] ?? null,

                // Buyer
                'customer_id'        => $customer?->id,
                'buyer_name'         => $customer?->company_name
                    ?? $customer?->name
                    ?? $posBooking->customer_name,
                'buyer_gstin'        => $customer?->gstin ?? $posBooking->customer_gstin,
                'buyer_address'      => $customer?->billing_address
                    ?? $customer?->address
                    ?? $posBooking->customer_address,
                'buyer_city'         => $customer?->billing_city ?? $customer?->city ?? $posBooking->customer_city,
                'buyer_state'        => $customer?->billing_state ?? $customer?->state ?? $posBooking->customer_state ?? $companySettings['state'],
                'buyer_state_code'   => $customer?->billing_state_code ?? $customer?->state_code ?? $posBooking->customer_state_code ?? $companySettings['state_code'],
                'buyer_pincode'      => $customer?->billing_pincode ?? $customer?->pincode ?? $posBooking->customer_pincode,
                'buyer_pan'          => $customer?->pan,
                'buyer_type'         => $customer?->customer_type ?? 'individual',
                'buyer_email'        => $customer?->email,
                'buyer_phone'        => $customer?->phone ?? $posBooking->customer_phone,
                'buyer_address'      => $posBooking->customer_address,

                // GST
                'place_of_supply'    => $customer?->billing_state ?? $companySettings['state'],
                'is_intra_state'     => $isIntraState,
                'is_reverse_charge'  => $customer ? $this->checkReverseCharge($customer) : false,
                'supply_type'        => 'services',

                // Status — invoice is ISSUED but unpaid at booking time
                'status'             => Invoice::STATUS_ISSUED,
                'issued_at'          => now(),
                'due_date'           => now()->addDays($companySettings['payment_terms_days'] ?? 30),
                'created_by'         => $createdBy,
                'terms_conditions'   => $companySettings['invoice_terms'],
                'payment_terms'      => $companySettings['payment_terms_text'],

                // Zero placeholders — recalculate fills these
                'subtotal'           => 0,
                'discount_amount'    => 0,
                'taxable_amount'     => 0,
                'total_tax'          => 0,
                'total_amount'       => 0,
                'grand_total'        => 0,
            ]);

            $this->addPOSInvoiceItems($invoice, $posBooking, $isIntraState);
            $this->recalculateInvoiceTotals($invoice);
            $this->generatePDF($invoice);
            $this->generateQRCode($invoice);

            return $invoice->fresh(['items']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: Sync invoice payment status when POS booking payment is received
    // Call this from POSBookingService::markPaymentReceived()
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Update the invoice status to reflect the latest payment from a POS booking.
     *
     * @param  \Modules\POS\Models\POSBooking  $posBooking
     * @param  float                           $paidAmount   Cumulative paid so far
     * @param  \DateTime|null                  $paidAt
     * @return Invoice|null
     */
    public function syncPaymentStatusFromPOSBooking(
        \Modules\POS\Models\POSBooking $posBooking,
        float $paidAmount,
        ?\DateTime $paidAt = null
    ): ?\App\Models\Invoice {
        $invoice = \App\Models\Invoice::where('pos_booking_id', $posBooking->id)
            ->whereNotIn('status', [\App\Models\Invoice::STATUS_CANCELLED, \App\Models\Invoice::STATUS_VOID])
            ->latest()
            ->first();

        if (!$invoice) {
            \Illuminate\Support\Facades\Log::warning('syncPaymentStatus: invoice not found', [
                'pos_booking_id' => $posBooking->id,
            ]);
            return null;
        }

        $grandTotal = (float) $invoice->grand_total;

        if (abs($paidAmount - $grandTotal) < 0.01 || $paidAmount >= $grandTotal) {
            $invoice->markAsPaid($paidAmount, $paidAt ?? now());
        } else {
            $invoice->update([
                'status'      => \App\Models\Invoice::STATUS_PARTIALLY_PAID,
                'paid_amount' => $paidAmount,
            ]);
        }

        // Regenerate PDF so PAID stamp appears
        $this->generatePDF($invoice->fresh(['items', 'customer']));

        return $invoice->fresh();
    }
    // ─────────────────────────────────────────────────────────────────────────
    // PROTECTED: Add items from a regular booking
    // ─────────────────────────────────────────────────────────────────────────

    protected function addInvoiceItems(Invoice $invoice, Booking $booking, bool $isIntraState): void
    {
        $lineNumber = 1;
        $gstRate    = $this->getGSTRate();

        $hoarding = $booking->hoarding;
        $hsnCode  = Setting::get('hsn_advertising_services', '998599');

        $durationDays  = max(1, (int) ($booking->duration_days ?? 1));
        $baseAmount    = (float) $booking->total_amount;
        $ratePerDay    = round($baseAmount / $durationDays, 4);

        $item = new InvoiceItem([
            'line_number'        => $lineNumber,
            'item_type'          => 'hoarding',
            'description'        => $hoarding
                ? "Outdoor Advertising - {$hoarding->title}"
                : 'Hoarding Advertisement',
            'hsn_sac_code'       => $hsnCode,
            'hoarding_id'        => $hoarding?->id,
            'quantity'           => $durationDays,
            'unit'               => 'days',
            'rate'               => $ratePerDay,
            'service_start_date' => $booking->start_date,
            'service_end_date'   => $booking->end_date,
            'duration_days'      => $durationDays,
            'discount_percent'   => 0,
            'discount_amount'    => 0,
        ]);

        $item->calculateAmounts($isIntraState, $gstRate);
        $invoice->items()->save($item);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROTECTED: Add items from POS booking hoardings
    //
    // FIX: The original code used hoarding_price as a "pre-tax" base to
    //      reverse-engineer rate, but hoarding_price IS already the pre-tax
    //      base amount stored by the controller. Use it directly.
    // ─────────────────────────────────────────────────────────────────────────


    protected function addPOSInvoiceItems(
        Invoice $invoice,
        \Modules\POS\Models\POSBooking $posBooking,
        bool $isIntraState
    ): void {
        $lineNumber   = 1;
        $gstRate      = $this->getGSTRate();
        $hsnCode      = \App\Models\Setting::get('hsn_advertising_services', '998599');
        $divisor      = max(1, $gstRate / 100 + 1); // e.g. 1.18 for 18%

        if (!$posBooking->relationLoaded('bookingHoardings')) {
            $posBooking->load('bookingHoardings.hoarding');
        }

        $hoardingCount = max(1, $posBooking->bookingHoardings->count());

        foreach ($posBooking->bookingHoardings as $bookingHoarding) {
            $hoarding = $bookingHoarding->hoarding;

            // ── Resolve pre-tax base amount ──────────────────────────────
            // Priority 1: hoarding_price is the pre-tax base stored by controller
            $lineBaseAmount = (float) ($bookingHoarding->hoarding_price ?? 0);

            if ($lineBaseAmount <= 0) {
                // Priority 2: reverse-engineer from hoarding_total (includes GST)
                $hoardingTotal = (float) ($bookingHoarding->hoarding_total ?? 0);
                if ($hoardingTotal > 0) {
                    $lineBaseAmount = round($hoardingTotal / $divisor, 4);
                }
            }

            if ($lineBaseAmount <= 0) {
                // Priority 3: even split of booking base_amount
                $lineBaseAmount = round((float) $posBooking->base_amount / $hoardingCount, 4);
            }

            $durationDays = max(1, (int) ($bookingHoarding->duration_days ?? 1));
            $ratePerDay   = round($lineBaseAmount / $durationDays, 4);

            // ── Description ──────────────────────────────────────────────
            $fmt = fn($d) => ($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->format('d M Y');

            $description = $hoarding
                ? sprintf(
                    // 'Outdoor Advertising — %s (%s to %s)',
                    $hoarding->title,
                    // $fmt($bookingHoarding->start_date),
                    // $fmt($bookingHoarding->end_date)
                )
                : 'Hoarding Advertisement';

            // ── Build item ────────────────────────────────────────────────
            $item = new \App\Models\InvoiceItem([
                'line_number'        => $lineNumber++,
                'item_type'          => 'hoarding',
                'description'        => $description,
                'hsn_sac_code'       => $hsnCode,
                'hoarding_id'        => $hoarding?->id,
                'quantity'           => $durationDays,
                'unit'               => 'days',
                'rate'               => $ratePerDay,
                'service_start_date' => $bookingHoarding->start_date,
                'service_end_date'   => $bookingHoarding->end_date,
                'duration_days'      => $durationDays,
                'discount_percent'   => 0,
                'discount_amount'    => (float) ($bookingHoarding->hoarding_discount ?? 0),
            ]);

            $item->calculateAmounts($isIntraState, $gstRate);
            $invoice->items()->save($item);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: Recalculate invoice totals from items
    // ─────────────────────────────────────────────────────────────────────────

    public function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $items = $invoice->items()->get(); // Fresh from DB

        $subtotal       = $items->sum('amount');
        $discountAmount = $items->sum('discount_amount');
        $taxableAmount  = $items->sum('taxable_amount');
        $cgstAmount     = $items->sum('cgst_amount');
        $sgstAmount     = $items->sum('sgst_amount');
        $igstAmount     = $items->sum('igst_amount');
        $totalTax       = $items->sum('total_tax');
        $totalAmount    = $items->sum('total_amount');

        $firstItem = $items->first();
        $cgstRate  = $firstItem?->cgst_rate;
        $sgstRate  = $firstItem?->sgst_rate;
        $igstRate  = $firstItem?->igst_rate;

        $grandTotal = round($totalAmount);
        $roundOff   = $grandTotal - $totalAmount;

        $invoice->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'taxable_amount'  => $taxableAmount,
            'cgst_rate'       => $cgstRate,
            'cgst_amount'     => $cgstAmount,
            'sgst_rate'       => $sgstRate,
            'sgst_amount'     => $sgstAmount,
            'igst_rate'       => $igstRate,
            'igst_amount'     => $igstAmount,
            'total_tax'       => $totalTax,
            'total_amount'    => $totalAmount,
            'round_off'       => $roundOff,
            'grand_total'     => $grandTotal,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: Generate PDF
    // ─────────────────────────────────────────────────────────────────────────

    public function generatePDF(Invoice $invoice): string
    {
        $pdf = Pdf::loadView('invoices.gst-invoice', [
            'invoice' => $invoice->load(['items', 'customer']),
        ]);

        $filename = 'invoices/' . str_replace('/', '_', $invoice->invoice_number) . '.pdf';

        Storage::disk('public')->put($filename, $pdf->output());
        $invoice->update(['pdf_path' => $filename]);

        return $filename;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC: Generate QR Code
    // ─────────────────────────────────────────────────────────────────────────

    public function generateQRCode(Invoice $invoice): string
    {
        $qrData = $this->generateQRCodeData($invoice);

        if (!class_exists('SimpleSoftwareIO\\QrCode\\Facades\\QrCode')) {
            Log::warning('QrCode facade not available; skipping QR generation', [
                'invoice_id' => $invoice->id,
            ]);
            $invoice->update(['qr_code_path' => null, 'qr_code_data' => $qrData]);
            return '';
        }

        try {
            $qrCode  = QrCode::format('png')->size(200)->margin(1)->generate($qrData);
            $filename = 'qrcodes/' . str_replace('/', '_', $invoice->invoice_number) . '.png';

            Storage::disk('public')->put($filename, $qrCode);
            $invoice->update(['qr_code_path' => $filename, 'qr_code_data' => $qrData]);

            return $filename;
        } catch (\Throwable $e) {
            Log::warning('Invoice QR generation failed', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);
            $invoice->update(['qr_code_path' => null, 'qr_code_data' => $qrData]);
            return '';
        }
    }

    protected function generateQRCodeData(Invoice $invoice): string
    {
        return json_encode([
            'invoice_number' => $invoice->invoice_number,
            'invoice_date'   => $invoice->invoice_date->format('Y-m-d'),
            'amount'         => $invoice->grand_total,
            'gstin'          => $invoice->seller_gstin,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Other public methods (unchanged logic)
    // ─────────────────────────────────────────────────────────────────────────

    public function sendInvoiceEmail(Invoice $invoice, ?array $recipients = null): bool
    {
        try {
            $recipients = $recipients ?? [$invoice->buyer_email];
            $invoice->markAsSent(implode(', ', $recipients));
            return true;
        } catch (\Exception $e) {
            Log::error('Invoice email failed: ' . $e->getMessage());
            return false;
        }
    }

    public function cancelInvoice(Invoice $invoice, string $reason, ?int $cancelledBy = null): bool
    {
        if (!$invoice->canCancel()) {
            throw new \Exception('Invoice cannot be cancelled');
        }
        return $invoice->cancel($reason, $cancelledBy);
    }

    public function markInvoiceAsPaid(Invoice $invoice, ?float $amount = null, ?\DateTime $paidAt = null): bool
    {
        return $invoice->markAsPaid($amount, $paidAt);
    }

    public function getFinancialYearSummary(string $financialYear): array
    {
        $invoices = Invoice::byFinancialYear($financialYear)->get();

        return [
            'financial_year'    => $financialYear,
            'total_invoices'    => $invoices->count(),
            'total_amount'      => $invoices->sum('grand_total'),
            'total_paid'        => $invoices->where('status', Invoice::STATUS_PAID)->sum('grand_total'),
            'total_unpaid'      => $invoices->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])->sum('grand_total'),
            'total_cancelled'   => $invoices->where('status', Invoice::STATUS_CANCELLED)->count(),
        ];
    }

    public function validateGSTIN(?string $gstin): bool
    {
        if (empty($gstin)) {
            return false;
        }
        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
        return preg_match($pattern, $gstin) === 1;
    }

    public function getStateCodeFromGSTIN(string $gstin): ?string
    {
        if (!$this->validateGSTIN($gstin)) {
            return null;
        }
        return substr($gstin, 0, 2);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function isIntraStateTansaction(string $sellerStateCode, string $buyerStateCode): bool
    {
        return $sellerStateCode === $buyerStateCode;
    }

    protected function checkReverseCharge(User $customer): bool
    {
        return $customer->customer_type === 'business' && !empty($customer->gstin);
    }

    protected function getGSTRate(): float
    {
        return (float) Setting::get('gst_rate', 18.0);
    }

    protected function getCompanySettings(): array
    {
        return [
            'name'                => Setting::get('company_name', 'OOHAPP Private Limited'),
            'gstin'               => Setting::get('company_gstin', '27AABCU9603R1ZX'),
            'address'             => Setting::get('company_address', 'Mumbai, Maharashtra'),
            'city'                => Setting::get('company_city', 'Mumbai'),
            'state'               => Setting::get('company_state', 'Maharashtra'),
            'state_code'          => Setting::get('company_state_code', '27'),
            'pincode'             => Setting::get('company_pincode', '400001'),
            'pan'                 => Setting::get('company_pan', null),
            'invoice_terms'       => Setting::get(
                'invoice_terms_conditions',
                "1. Payment is due within 30 days.\n2. Please quote invoice number in all correspondence.\n3. Subject to Mumbai jurisdiction only."
            ),
            'payment_terms_text'  => Setting::get('invoice_payment_terms', 'Net 30 Days'),
            'payment_terms_days'  => (int) Setting::get('invoice_payment_days', 30),
        ];
    }
}
