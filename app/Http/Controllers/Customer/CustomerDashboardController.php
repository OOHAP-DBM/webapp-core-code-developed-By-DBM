<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CustomerDashboardService;
use App\Models\Booking;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(CustomerDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
        $this->middleware('auth');
    }

    /**
     * Display customer dashboard
     */
    public function index()
    {
        $customer = auth()->user();
        
        // Get statistics
        $stats = $this->dashboardService->getStats($customer);
        
        // Get recent activities
        $recentActivities = $this->dashboardService->getRecentActivities($customer, 10);
        
        // Get upcoming bookings
        $upcomingBookings = $this->dashboardService->getUpcomingBookings($customer, 5);
        
        // Get pending payments
        $pendingPayments = $this->dashboardService->getPendingPayments($customer);
        
        // Get chart data
        $bookingChart = $this->dashboardService->getBookingChartData($customer, 'monthly');
        $spendingSummary = $this->dashboardService->getSpendingSummary($customer);

        return view('customer.dashboard.index', compact(
            'stats',
            'recentActivities',
            'upcomingBookings',
            'pendingPayments',
            'bookingChart',
            'spendingSummary'
        ));
    }

    /**
     * Display my bookings
     */
    public function myBookings(Request $request)
    {
        $customer = auth()->user();
        
        $query = Booking::where('customer_id', $customer->id)
            ->with(['hoarding', 'vendor']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('booking_number', 'like', "%{$request->search}%")
                  ->orWhereHas('hoarding', function($hq) use ($request) {
                      $hq->where('title', 'like', "%{$request->search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $bookings = $query->paginate(15)->withQueryString();

        // Summary stats for filtered results
        $summary = [
            'total' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'paid_amount' => $query->where('payment_status', 'paid')->sum('total_amount'),
        ];

        return view('customer.dashboard.bookings', compact('bookings', 'summary'));
    }

    /**
     * Display my payments
     */
    public function myPayments(Request $request)
    {
        $customer = auth()->user();
        
        $query = DB::table('booking_payments')
            ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
            ->where('bookings.customer_id', $customer->id)
            ->select('booking_payments.*', 'bookings.booking_number');

        // Filters
        if ($request->filled('status')) {
            $query->where('booking_payments.status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('booking_payments.payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->where('booking_payments.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('booking_payments.created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('booking_payments.transaction_id', 'like', "%{$request->search}%")
                  ->orWhere('bookings.booking_number', 'like', "%{$request->search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'booking_payments.created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $payments = $query->paginate(15)->withQueryString();

        // Summary
        $summary = [
            'total_paid' => DB::table('booking_payments')
                ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
                ->where('bookings.customer_id', $customer->id)
                ->where('booking_payments.status', 'completed')
                ->sum('booking_payments.amount'),
            'total_pending' => DB::table('booking_payments')
                ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
                ->where('bookings.customer_id', $customer->id)
                ->where('booking_payments.status', 'pending')
                ->sum('booking_payments.amount'),
            'total_refunded' => DB::table('booking_payments')
                ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
                ->where('bookings.customer_id', $customer->id)
                ->where('booking_payments.status', 'refunded')
                ->sum('booking_payments.amount'),
        ];

        return view('customer.dashboard.payments', compact('payments', 'summary'));
    }

    /**
     * Display my enquiries
     */
    public function myEnquiries(Request $request)
    {
        $customer = auth()->user();
        
        $query = Enquiry::where('customer_id', $customer->id)
            ->with(['hoarding', 'vendor']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', "%{$request->search}%")
                  ->orWhereHas('hoarding', function($hq) use ($request) {
                      $hq->where('title', 'like', "%{$request->search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $enquiries = $query->paginate(15)->withQueryString();

        // Summary
        $summary = [
            'total' => Enquiry::where('customer_id', $customer->id)->count(),
            'pending' => Enquiry::where('customer_id', $customer->id)->where('status', 'pending')->count(),
            'responded' => Enquiry::where('customer_id', $customer->id)->whereIn('status', ['responded', 'converted'])->count(),
        ];

        return view('customer.dashboard.enquiries', compact('enquiries', 'summary'));
    }

    /**
     * Display my offers
     */
    public function myOffers(Request $request)
    {
        $customer = auth()->user();
        
        $query = DB::table('offers')
            ->where('customer_id', $customer->id);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('offer_code', 'like', "%{$request->search}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $offers = $query->paginate(15)->withQueryString();

        // Summary
        $summary = [
            'total' => DB::table('offers')->where('customer_id', $customer->id)->count(),
            'active' => DB::table('offers')->where('customer_id', $customer->id)->where('status', 'active')->count(),
            'used' => DB::table('offers')->where('customer_id', $customer->id)->where('status', 'used')->count(),
        ];

        return view('customer.dashboard.offers', compact('offers', 'summary'));
    }

    /**
     * Display my quotations
     */
    public function myQuotations(Request $request)
    {
        $customer = auth()->user();
        
        $query = DB::table('quotations')
            ->where('customer_id', $customer->id);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('quotation_number', 'like', "%{$request->search}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $quotations = $query->paginate(15)->withQueryString();

        // Summary
        $summary = [
            'total' => DB::table('quotations')->where('customer_id', $customer->id)->count(),
            'pending' => DB::table('quotations')->where('customer_id', $customer->id)->where('status', 'pending')->count(),
            'approved' => DB::table('quotations')->where('customer_id', $customer->id)->where('status', 'approved')->count(),
            'total_amount' => DB::table('quotations')->where('customer_id', $customer->id)->sum('total_amount'),
        ];

        return view('customer.dashboard.quotations', compact('quotations', 'summary'));
    }

    /**
     * Display my invoices
     */
    public function myInvoices(Request $request)
    {
        $customer = auth()->user();
        
        $query = Booking::where('customer_id', $customer->id)
            ->whereNotNull('invoice_number')
            ->with(['hoarding', 'vendor']);

        // Filters
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhere('booking_number', 'like', "%{$request->search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'invoice_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $invoices = $query->paginate(15)->withQueryString();

        // Summary
        $summary = [
            'total' => Booking::where('customer_id', $customer->id)->whereNotNull('invoice_number')->count(),
            'paid' => Booking::where('customer_id', $customer->id)->whereNotNull('invoice_number')->where('payment_status', 'paid')->count(),
            'unpaid' => Booking::where('customer_id', $customer->id)->whereNotNull('invoice_number')->whereIn('payment_status', ['pending', 'partial'])->count(),
            'total_amount' => Booking::where('customer_id', $customer->id)->whereNotNull('invoice_number')->sum('total_amount'),
            'paid_amount' => Booking::where('customer_id', $customer->id)->whereNotNull('invoice_number')->where('payment_status', 'paid')->sum('total_amount'),
        ];

        return view('customer.dashboard.invoices', compact('invoices', 'summary'));
    }

    /**
     * Display my threads
     */
    public function myThreads(Request $request)
    {
        $customer = auth()->user();
        
        $query = DB::table('threads')
            ->where('customer_id', $customer->id);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('unread_only') && $request->unread_only == '1') {
            $query->where('has_unread_messages', 1);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('subject', 'like', "%{$request->search}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $threads = $query->paginate(15)->withQueryString();

        // Summary
        $summary = [
            'total' => DB::table('threads')->where('customer_id', $customer->id)->count(),
            'unread' => DB::table('threads')->where('customer_id', $customer->id)->where('has_unread_messages', 1)->count(),
            'active' => DB::table('threads')->where('customer_id', $customer->id)->where('status', 'active')->count(),
        ];

        return view('customer.dashboard.threads', compact('threads', 'summary'));
    }

    /**
     * Export bookings
     */
    public function exportBookings(Request $request, string $format = 'pdf')
    {
        $customer = auth()->user();
        
        $query = Booking::where('customer_id', $customer->id)
            ->with(['hoarding', 'vendor']);

        // Apply same filters as myBookings
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $bookings = $query->get();

        switch ($format) {
            case 'csv':
                return $this->exportBookingsCSV($bookings, $customer);
            case 'excel':
                return $this->exportBookingsExcel($bookings, $customer);
            default:
                return $this->exportBookingsPDF($bookings, $customer);
        }
    }

    /**
     * Export bookings as PDF
     */
    protected function exportBookingsPDF($bookings, $customer)
    {
        $pdf = PDF::loadView('customer.dashboard.exports.bookings-pdf', compact('bookings', 'customer'));
        return $pdf->download('my-bookings-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export bookings as CSV
     */
    protected function exportBookingsCSV($bookings, $customer)
    {
        $filename = 'my-bookings-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($bookings) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Booking Number',
                'Hoarding',
                'Start Date',
                'End Date',
                'Status',
                'Payment Status',
                'Total Amount',
                'Created At'
            ]);

            // Data
            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->booking_number,
                    $booking->hoarding->title ?? 'N/A',
                    $booking->start_date,
                    $booking->end_date,
                    $booking->status,
                    $booking->payment_status,
                    $booking->total_amount,
                    $booking->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export bookings as Excel (using CSV format for simplicity)
     */
    protected function exportBookingsExcel($bookings, $customer)
    {
        // For basic implementation, use CSV with .xlsx extension
        // In production, use a proper Excel library like PhpSpreadsheet
        return $this->exportBookingsCSV($bookings, $customer);
    }

    /**
     * Export payments
     */
    public function exportPayments(Request $request, string $format = 'pdf')
    {
        $customer = auth()->user();
        
        $payments = DB::table('booking_payments')
            ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
            ->where('bookings.customer_id', $customer->id)
            ->select('booking_payments.*', 'bookings.booking_number')
            ->get();

        $filename = 'my-payments-' . now()->format('Y-m-d');

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $callback = function() use ($payments) {
                $file = fopen('php://output', 'w');
                
                fputcsv($file, [
                    'Transaction ID',
                    'Booking Number',
                    'Amount',
                    'Payment Method',
                    'Status',
                    'Date'
                ]);

                foreach ($payments as $payment) {
                    fputcsv($file, [
                        $payment->transaction_id ?? 'N/A',
                        $payment->booking_number,
                        $payment->amount,
                        $payment->payment_method ?? 'N/A',
                        $payment->status,
                        $payment->created_at,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // PDF export
        $pdf = PDF::loadView('customer.dashboard.exports.payments-pdf', compact('payments', 'customer'));
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export invoices
     */
    public function exportInvoices(Request $request, string $format = 'pdf')
    {
        $customer = auth()->user();
        
        $invoices = Booking::where('customer_id', $customer->id)
            ->whereNotNull('invoice_number')
            ->with(['hoarding', 'vendor'])
            ->get();

        $filename = 'my-invoices-' . now()->format('Y-m-d');

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $callback = function() use ($invoices) {
                $file = fopen('php://output', 'w');
                
                fputcsv($file, [
                    'Invoice Number',
                    'Booking Number',
                    'Invoice Date',
                    'Total Amount',
                    'Payment Status',
                    'Due Date'
                ]);

                foreach ($invoices as $invoice) {
                    fputcsv($file, [
                        $invoice->invoice_number,
                        $invoice->booking_number,
                        $invoice->invoice_date ?? 'N/A',
                        $invoice->total_amount,
                        $invoice->payment_status,
                        $invoice->due_date ?? 'N/A',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // PDF export
        $pdf = PDF::loadView('customer.dashboard.exports.invoices-pdf', compact('invoices', 'customer'));
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Refresh dashboard statistics
     */
    public function refreshStats()
    {
        $customer = auth()->user();
        $this->dashboardService->getStats($customer, true);

        return redirect()->back()->with('success', 'Dashboard statistics refreshed successfully!');
    }
}
