<?php

namespace App\Services;

use App\Models\PayoutRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * PROMPT 58: Payout Receipt PDF Generator
 * 
 * Generates settlement receipt PDFs for completed payout requests
 */
class PayoutReceiptService
{
    /**
     * Generate settlement receipt PDF
     *
     * @param PayoutRequest $payoutRequest
     * @return string Path to generated PDF
     * @throws Exception
     */
    public function generateReceipt(PayoutRequest $payoutRequest): string
    {
        if (!$payoutRequest->isCompleted()) {
            throw new Exception('Can only generate receipts for completed payout requests');
        }

        try {
            // Prepare data for PDF
            $data = $this->prepareReceiptData($payoutRequest);

            // Generate PDF
            $pdf = Pdf::loadView('pdf.payout-receipt', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif',
                ]);

            // Generate filename
            $filename = $this->generateFilename($payoutRequest);
            $path = "payout-receipts/{$payoutRequest->vendor_id}/{$filename}";

            // Store PDF
            Storage::disk('local')->put($path, $pdf->output());

            // Update payout request
            $payoutRequest->update([
                'receipt_pdf_path' => $path,
                'receipt_generated_at' => now(),
            ]);

            return $path;
        } catch (Exception $e) {
            throw new Exception('Failed to generate receipt PDF: ' . $e->getMessage());
        }
    }

    /**
     * Prepare data for receipt PDF
     *
     * @param PayoutRequest $payoutRequest
     * @return array
     */
    protected function prepareReceiptData(PayoutRequest $payoutRequest): array
    {
        $vendor = $payoutRequest->vendor;
        $bookingPayments = $payoutRequest->getBookingPayments();

        return [
            'payoutRequest' => $payoutRequest,
            'vendor' => $vendor,
            'bookingPayments' => $bookingPayments,
            'company' => [
                'name' => config('app.name', 'OOHAPP'),
                'address' => 'Your Company Address',
                'phone' => 'Your Company Phone',
                'email' => 'finance@oohapp.com',
                'gst' => 'Your GST Number',
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Generate filename for receipt
     *
     * @param PayoutRequest $payoutRequest
     * @return string
     */
    protected function generateFilename(PayoutRequest $payoutRequest): string
    {
        return sprintf(
            'receipt_%s_%s.pdf',
            $payoutRequest->request_reference,
            now()->format('YmdHis')
        );
    }

    /**
     * Download receipt PDF
     *
     * @param PayoutRequest $payoutRequest
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws Exception
     */
    public function downloadReceipt(PayoutRequest $payoutRequest)
    {
        if (!$payoutRequest->receipt_pdf_path) {
            throw new Exception('Receipt PDF not found. Please generate it first.');
        }

        if (!Storage::disk('local')->exists($payoutRequest->receipt_pdf_path)) {
            throw new Exception('Receipt file not found on storage');
        }

        $filename = basename($payoutRequest->receipt_pdf_path);
        
        return Storage::disk('local')->download(
            $payoutRequest->receipt_pdf_path,
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Regenerate receipt PDF
     *
     * @param PayoutRequest $payoutRequest
     * @return string
     * @throws Exception
     */
    public function regenerateReceipt(PayoutRequest $payoutRequest): string
    {
        // Delete old receipt if exists
        if ($payoutRequest->receipt_pdf_path && Storage::disk('local')->exists($payoutRequest->receipt_pdf_path)) {
            Storage::disk('local')->delete($payoutRequest->receipt_pdf_path);
        }

        return $this->generateReceipt($payoutRequest);
    }
}
