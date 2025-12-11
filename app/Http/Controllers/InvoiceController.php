<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of invoices (for customer)
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['items', 'booking'])
            ->forCustomer(Auth::id())
            ->orderBy('invoice_date', 'desc');

        // Filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('financial_year') && $request->financial_year !== '') {
            $query->byFinancialYear($request->financial_year);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->where('invoice_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->where('invoice_date', '<=', $request->to_date);
        }

        $invoices = $query->paginate(20);

        return view('customer.invoices.index', compact('invoices'));
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        // Authorization check
        if ($invoice->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to invoice');
        }

        $invoice->load(['items.hoarding', 'booking', 'customer']);

        return view('customer.invoices.show', compact('invoice'));
    }

    /**
     * Download invoice PDF
     */
    public function download(Invoice $invoice)
    {
        // Authorization check
        if ($invoice->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to invoice');
        }

        if (!$invoice->hasPDF()) {
            // Generate PDF if not exists
            $this->invoiceService->generatePDF($invoice);
        }

        $filename = str_replace('/', '_', $invoice->invoice_number) . '.pdf';

        return Storage::disk('public')->download($invoice->pdf_path, $filename);
    }

    /**
     * Send invoice via email
     */
    public function sendEmail(Request $request, Invoice $invoice)
    {
        // Authorization check
        if ($invoice->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to invoice');
        }

        $request->validate([
            'email' => 'nullable|email',
        ]);

        $recipients = $request->email ? [$request->email] : [$invoice->buyer_email];

        $sent = $this->invoiceService->sendInvoiceEmail($invoice, $recipients);

        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully to ' . implode(', ', $recipients),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice email',
            ], 500);
        }
    }

    /**
     * Print invoice (view optimized for printing)
     */
    public function print(Invoice $invoice)
    {
        // Authorization check
        if ($invoice->customer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to invoice');
        }

        $invoice->load(['items.hoarding', 'booking', 'customer']);

        return view('invoices.gst-invoice', compact('invoice'));
    }

    /**
     * Admin: List all invoices
     */
    public function adminIndex(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['customer', 'booking'])
            ->orderBy('invoice_date', 'desc');

        // Filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('financial_year') && $request->financial_year !== '') {
            $query->byFinancialYear($request->financial_year);
        }

        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%")
                    ->orWhere('buyer_gstin', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(50);

        // Get financial year summary
        $currentFY = \App\Models\InvoiceSequence::getCurrentFinancialYear();
        $summary = $this->invoiceService->getFinancialYearSummary($currentFY);

        return view('admin.invoices.index', compact('invoices', 'summary'));
    }

    /**
     * Admin: Show invoice details
     */
    public function adminShow(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items.hoarding', 'booking', 'customer', 'createdBy', 'cancelledBy']);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Admin: Cancel invoice
     */
    public function adminCancel(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->invoiceService->cancelInvoice(
                $invoice,
                $request->reason,
                Auth::id()
            );

            return redirect()->back()->with('success', 'Invoice cancelled successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Mark invoice as paid
     */
    public function adminMarkPaid(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_at' => 'nullable|date',
        ]);

        try {
            $paidAmount = $request->paid_amount ?? $invoice->grand_total;
            $paidAt = $request->paid_at ? new \DateTime($request->paid_at) : null;

            $this->invoiceService->markInvoiceAsPaid($invoice, $paidAmount, $paidAt);

            return redirect()->back()->with('success', 'Invoice marked as paid successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Regenerate PDF
     */
    public function adminRegeneratePDF(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        try {
            $this->invoiceService->generatePDF($invoice);

            return redirect()->back()->with('success', 'Invoice PDF regenerated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to regenerate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export invoices
     */
    public function export(Request $request)
    {
        // Implementation for CSV/Excel export
        // Can use Laravel Excel or similar package
        return response()->json([
            'message' => 'Export feature coming soon',
        ]);
    }
}
