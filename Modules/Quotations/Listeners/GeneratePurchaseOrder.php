<?php

namespace Modules\Quotations\Listeners;

use App\Services\PurchaseOrderService;
use Modules\Quotations\Events\QuotationApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * PROMPT 107: Generate PO when Quotation is Approved
 * 
 * Listens to QuotationApproved event and auto-generates PO
 */
class GeneratePurchaseOrder implements ShouldQueue
{
    use InteractsWithQueue;

    protected PurchaseOrderService $poService;

    /**
     * Create the event listener.
     */
    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    /**
     * Handle the event.
     */
    public function handle(QuotationApproved $event): void
    {
        try {
            $quotation = $event->quotation;

            Log::info('Generating PO for approved quotation', [
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'vendor_id' => $quotation->vendor_id,
            ]);

            // Generate PO (includes PDF generation, thread attachment, and notifications)
            $po = $this->poService->generateFromQuotation($quotation);

            Log::info('PO generation completed', [
                'quotation_id' => $quotation->id,
                'po_id' => $po->id,
                'po_number' => $po->po_number,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate PO for approved quotation', [
                'quotation_id' => $event->quotation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't throw exception to prevent blocking other listeners
            // PO generation can be retried manually if needed
        }
    }
}
