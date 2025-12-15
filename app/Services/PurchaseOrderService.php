<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\PurchaseOrderGeneratedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Threads\Models\Thread;
use Modules\Threads\Models\ThreadMessage;
use Modules\Threads\Services\ThreadService;

/**
 * PROMPT 107: Purchase Order Generation Service
 * PROMPT 109: Enhanced with Currency + Tax Configuration
 * 
 * Auto-generates PO from approved quotations, creates PDF, attaches to thread, and notifies vendor
 */
class PurchaseOrderService
{
    protected ThreadService $threadService;
    protected TaxConfigurationService $taxConfigService;
    protected CurrencyService $currencyService;

    public function __construct(
        ThreadService $threadService,
        TaxConfigurationService $taxConfigService,
        CurrencyService $currencyService
    ) {
        $this->threadService = $threadService;
        $this->taxConfigService = $taxConfigService;
        $this->currencyService = $currencyService;
    }

    /**
     * Generate PO from approved quotation
     *
     * @param Quotation $quotation
     * @return PurchaseOrder
     * @throws \Exception
     */
    public function generateFromQuotation(Quotation $quotation): PurchaseOrder
    {
        // Validate quotation is approved
        if ($quotation->status !== Quotation::STATUS_APPROVED) {
            throw new \Exception('Quotation must be approved before generating PO');
        }

        // Check if PO already exists for this quotation
        $existingPo = PurchaseOrder::where('quotation_id', $quotation->id)->first();
        if ($existingPo) {
            Log::info('PO already exists for quotation', [
                'quotation_id' => $quotation->id,
                'po_id' => $existingPo->id,
                'po_number' => $existingPo->po_number,
            ]);
            return $existingPo;
        }

        return DB::transaction(function () use ($quotation) {
            // Load relationships
            $quotation->load(['offer.enquiry', 'customer', 'vendor']);

            // Create PO
            $po = $this->createPurchaseOrder($quotation);

            // Generate PDF
            $pdfPath = $this->generatePDF($po);
            $po->update([
                'pdf_path' => $pdfPath,
                'pdf_generated_at' => now(),
            ]);

            // Attach to thread
            $this->attachToThread($po);

            // Mark as sent
            $po->markAsSent();

            // Notify vendor
            $this->notifyVendor($po);

            Log::info('Purchase Order generated successfully', [
                'po_id' => $po->id,
                'po_number' => $po->po_number,
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'vendor_id' => $quotation->vendor_id,
            ]);

            return $po->fresh();
        });
    }

    /**
     * Create PO record from quotation (PROMPT 109: Enhanced with tax calculation)
     *
     * @param Quotation $quotation
     * @return PurchaseOrder
     */
    protected function createPurchaseOrder(Quotation $quotation): PurchaseOrder
    {
        $poNumber = PurchaseOrder::generatePoNumber();

        // Get currency configuration
        $currency = $this->currencyService->getDefaultCurrency();

        // Calculate comprehensive taxes (GST + TCS + TDS)
        $taxCalculation = $this->taxConfigService->calculateCompleteTax(
            $quotation->total_amount,
            [
                'transaction_type' => 'purchase_order',
                'applies_to' => 'purchase_order',
                'customer_id' => $quotation->customer_id,
                'vendor_id' => $quotation->vendor_id,
                'customer_state_code' => $quotation->customer->billing_state_code ?? null,
                'vendor_state_code' => $quotation->vendor->billing_state_code ?? null,
                'buyer_state_code' => $quotation->customer->billing_state_code ?? null,
                'seller_state_code' => $quotation->vendor->billing_state_code ?? null,
                'has_gstin' => !empty($quotation->customer->gstin),
                'customer_type' => $quotation->customer->customer_type ?? 'individual',
            ]
        );

        // Determine place of supply
        $placeOfSupply = $quotation->customer->billing_state ?? 
                        $quotation->customer->state ?? 
                        $quotation->vendor->billing_state ?? 
                        'Maharashtra';

        return PurchaseOrder::create([
            'po_number' => $poNumber,
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'vendor_id' => $quotation->vendor_id,
            'enquiry_id' => $quotation->offer->enquiry_id,
            'offer_id' => $quotation->offer_id,
            
            // Items
            'items' => $quotation->items,
            
            // Amounts
            'total_amount' => $quotation->total_amount,
            'subtotal' => $taxCalculation['subtotal'],
            'discount' => $quotation->discount,
            
            // Currency (PROMPT 109)
            'currency_code' => $currency->code,
            'currency_symbol' => $currency->symbol,
            
            // GST (PROMPT 109)
            'tax' => $taxCalculation['gst_amount'],
            'tax_rate' => $taxCalculation['gst_rate'],
            'cgst_rate' => $taxCalculation['cgst_rate'],
            'cgst_amount' => $taxCalculation['cgst_amount'],
            'sgst_rate' => $taxCalculation['sgst_rate'],
            'sgst_amount' => $taxCalculation['sgst_amount'],
            'igst_rate' => $taxCalculation['igst_rate'],
            'igst_amount' => $taxCalculation['igst_amount'],
            
            // TCS (PROMPT 109)
            'has_tcs' => $taxCalculation['tcs_applicable'],
            'tcs_rate' => $taxCalculation['tcs_rate'],
            'tcs_amount' => $taxCalculation['tcs_amount'],
            'tcs_section' => $taxCalculation['tcs_section'],
            
            // TDS (PROMPT 109)
            'has_tds' => $taxCalculation['tds_applicable'],
            'tds_rate' => $taxCalculation['tds_rate'],
            'tds_amount' => $taxCalculation['tds_amount'],
            'tds_section' => $taxCalculation['tds_section'],
            
            // Tax metadata (PROMPT 109)
            'is_intra_state' => $taxCalculation['is_intra_state'],
            'is_reverse_charge' => $taxCalculation['is_reverse_charge'],
            'place_of_supply' => $placeOfSupply,
            'tax_calculation_details' => $taxCalculation['tax_calculation_details'],
            
            // Grand total
            'grand_total' => $taxCalculation['grand_total'],
            
            // Payment details
            'has_milestones' => $quotation->has_milestones,
            'payment_mode' => $quotation->payment_mode,
            'milestone_count' => $quotation->milestone_count,
            'milestone_summary' => $quotation->milestone_summary,
            
            // Notes and terms
            'notes' => $quotation->notes,
            'terms_and_conditions' => $this->getDefaultTermsAndConditions(),
            
            // Status
            'status' => PurchaseOrder::STATUS_PENDING,
        ]);
    }

    /**
     * Generate PDF for PO
     *
     * @param PurchaseOrder $po
     * @return string Path to PDF
     */
    public function generatePDF(PurchaseOrder $po): string
    {
        $po->load(['quotation', 'customer', 'vendor', 'enquiry', 'offer']);

        $pdf = Pdf::loadView('pdf.purchase-order', [
            'po' => $po,
        ]);

        $filename = $po->getPdfFilename();
        $path = 'purchase-orders/' . $po->id . '/' . $filename;

        // Store PDF in private storage
        Storage::disk('private')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Attach PO PDF to conversation thread
     *
     * @param PurchaseOrder $po
     * @return ThreadMessage|null
     */
    protected function attachToThread(PurchaseOrder $po): ?ThreadMessage
    {
        try {
            // Get or create thread for enquiry
            $thread = $this->threadService->getOrCreateThread($po->enquiry_id);

            if (!$thread) {
                Log::warning('Could not find/create thread for PO', [
                    'po_id' => $po->id,
                    'enquiry_id' => $po->enquiry_id,
                ]);
                return null;
            }

            // Get PDF URL
            $pdfUrl = Storage::disk('private')->url($po->pdf_path);

            // Create thread message with PO attachment
            $message = ThreadMessage::create([
                'thread_id' => $thread->id,
                'sender_type' => ThreadMessage::SENDER_SYSTEM,
                'message_type' => ThreadMessage::TYPE_SYSTEM,
                'message' => $this->getThreadMessage($po),
                'attachments' => [
                    [
                        'name' => $po->getPdfFilename(),
                        'path' => $po->pdf_path,
                        'size' => Storage::disk('private')->size($po->pdf_path),
                        'type' => 'application/pdf',
                        'url' => $pdfUrl,
                    ]
                ],
                'is_read_customer' => false,
                'is_read_vendor' => false,
            ]);

            // Update PO with thread info
            $po->update([
                'thread_id' => $thread->id,
                'thread_message_id' => $message->id,
            ]);

            // Increment unread counts
            $thread->incrementUnread('customer');
            $thread->incrementUnread('vendor');
            $thread->update(['last_message_at' => now()]);

            Log::info('PO attached to thread', [
                'po_id' => $po->id,
                'thread_id' => $thread->id,
                'message_id' => $message->id,
            ]);

            return $message;

        } catch (\Exception $e) {
            Log::error('Failed to attach PO to thread', [
                'po_id' => $po->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get thread system message content
     *
     * @param PurchaseOrder $po
     * @return string
     */
    protected function getThreadMessage(PurchaseOrder $po): string
    {
        return "📄 **Purchase Order Generated**\n\n" .
               "PO Number: {$po->po_number}\n" .
               "Quotation ID: #{$po->quotation_id}\n" .
               "Amount: {$po->getFormattedGrandTotal()}\n\n" .
               "A Purchase Order has been automatically generated based on the approved quotation. " .
               "Please download the attached PDF for complete details.\n\n" .
               "**Next Steps:**\n" .
               "- Customer: Review and confirm the PO\n" .
               "- Vendor: Acknowledge receipt and proceed with order execution";
    }

    /**
     * Notify vendor about new PO
     *
     * @param PurchaseOrder $po
     * @return void
     */
    protected function notifyVendor(PurchaseOrder $po): void
    {
        try {
            $po->vendor->notify(new PurchaseOrderGeneratedNotification($po));

            // Also notify customer
            $po->customer->notify(new PurchaseOrderGeneratedNotification($po, true));

            Log::info('PO notifications sent', [
                'po_id' => $po->id,
                'vendor_id' => $po->vendor_id,
                'customer_id' => $po->customer_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send PO notifications', [
                'po_id' => $po->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get default terms and conditions
     *
     * @return string
     */
    protected function getDefaultTermsAndConditions(): string
    {
        return "1. Payment Terms: As per quotation payment mode (full or milestone-based)\n" .
               "2. Delivery Timeline: As per booking dates specified in quotation\n" .
               "3. Quality Standards: Services must meet industry standards\n" .
               "4. Cancellation: Subject to cancellation policy as per booking agreement\n" .
               "5. Liability: Vendor is responsible for equipment and installation quality\n" .
               "6. Compliance: All statutory requirements and permits are vendor's responsibility\n" .
               "7. Disputes: Subject to jurisdiction as per platform terms\n" .
               "8. Modifications: Any changes require written approval from both parties";
    }

    /**
     * Regenerate PDF for existing PO
     *
     * @param PurchaseOrder $po
     * @return string New PDF path
     */
    public function regeneratePDF(PurchaseOrder $po): string
    {
        // Delete old PDF if exists
        if ($po->pdf_path) {
            Storage::disk('private')->delete($po->pdf_path);
        }

        // Generate new PDF
        $pdfPath = $this->generatePDF($po);

        $po->update([
            'pdf_path' => $pdfPath,
            'pdf_generated_at' => now(),
        ]);

        Log::info('PO PDF regenerated', [
            'po_id' => $po->id,
            'po_number' => $po->po_number,
        ]);

        return $pdfPath;
    }

    /**
     * Cancel PO
     *
     * @param PurchaseOrder $po
     * @param string $reason
     * @param string|int $cancelledBy
     * @return PurchaseOrder
     */
    public function cancelPO(PurchaseOrder $po, string $reason, $cancelledBy = 'system'): PurchaseOrder
    {
        if (!$po->canCancel()) {
            throw new \Exception('PO cannot be cancelled in its current state');
        }

        $po->cancel($reason, $cancelledBy);

        // Post system message to thread
        if ($po->thread_id) {
            try {
                ThreadMessage::create([
                    'thread_id' => $po->thread_id,
                    'sender_type' => ThreadMessage::SENDER_SYSTEM,
                    'message_type' => ThreadMessage::TYPE_SYSTEM,
                    'message' => "⚠️ Purchase Order {$po->po_number} has been cancelled.\n\nReason: {$reason}",
                    'is_read_customer' => false,
                    'is_read_vendor' => false,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to post PO cancellation to thread', [
                    'po_id' => $po->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('PO cancelled', [
            'po_id' => $po->id,
            'po_number' => $po->po_number,
            'reason' => $reason,
            'cancelled_by' => $cancelledBy,
        ]);

        return $po->fresh();
    }

    /**
     * Get PO by quotation
     *
     * @param int $quotationId
     * @return PurchaseOrder|null
     */
    public function getByQuotation(int $quotationId): ?PurchaseOrder
    {
        return PurchaseOrder::where('quotation_id', $quotationId)
            ->with(['quotation', 'customer', 'vendor', 'thread'])
            ->first();
    }

    /**
     * Get POs for customer
     *
     * @param int $customerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerPOs(int $customerId)
    {
        return PurchaseOrder::byCustomer($customerId)
            ->with(['quotation', 'vendor', 'enquiry'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get POs for vendor
     *
     * @param int $vendorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVendorPOs(int $vendorId)
    {
        return PurchaseOrder::byVendor($vendorId)
            ->with(['quotation', 'customer', 'enquiry'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
