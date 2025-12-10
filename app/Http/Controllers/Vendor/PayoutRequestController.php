<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\{PayoutService, PayoutReceiptService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * PROMPT 58: Vendor Payout Request Controller
 * 
 * Handles vendor-side payout request creation and management
 */
class PayoutRequestController extends Controller
{
    protected PayoutService $payoutService;
    protected PayoutReceiptService $receiptService;

    public function __construct(PayoutService $payoutService, PayoutReceiptService $receiptService)
    {
        $this->middleware('auth');
        $this->middleware('role:vendor');
        $this->payoutService = $payoutService;
        $this->receiptService = $receiptService;
    }

    /**
     * Display vendor payout dashboard
     */
    public function index()
    {
        $vendor = Auth::user();
        $summary = $this->payoutService->getVendorPayoutSummary($vendor);
        
        $payoutRequests = PayoutRequest::where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('vendor.payouts.index', compact('summary', 'payoutRequests'));
    }

    /**
     * Show form to create new payout request
     */
    public function create()
    {
        $vendor = Auth::user();
        $summary = $this->payoutService->getVendorPayoutSummary($vendor);

        return view('vendor.payouts.create', compact('summary'));
    }

    /**
     * Preview payout calculation
     */
    public function preview(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'adjustment_amount' => 'nullable|numeric',
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $vendor = Auth::user();
        
        $preview = $this->payoutService->calculatePayoutPreview(
            $vendor,
            Carbon::parse($request->period_start),
            Carbon::parse($request->period_end),
            (float) ($request->adjustment_amount ?? 0),
            (float) ($request->gst_percentage ?? 0)
        );

        return response()->json([
            'success' => true,
            'data' => $preview,
        ]);
    }

    /**
     * Store new payout request
     */
    public function store(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'adjustment_amount' => 'nullable|numeric',
            'adjustment_reason' => 'required_if:adjustment_amount,!=,0',
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $vendor = Auth::user();

            $payoutRequest = $this->payoutService->createPayoutRequest(
                $vendor,
                Carbon::parse($request->period_start),
                Carbon::parse($request->period_end),
                [
                    'adjustment_amount' => (float) ($request->adjustment_amount ?? 0),
                    'adjustment_reason' => $request->adjustment_reason,
                    'gst_percentage' => (float) ($request->gst_percentage ?? 0),
                ]
            );

            return redirect()
                ->route('vendor.payouts.show', $payoutRequest)
                ->with('success', 'Payout request created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show payout request details
     */
    public function show(PayoutRequest $payoutRequest)
    {
        // Ensure vendor can only view their own requests
        if ($payoutRequest->vendor_id !== Auth::id()) {
            abort(403);
        }

        $payoutRequest->load(['vendor', 'submitter', 'approver', 'rejecter']);
        $bookingPayments = $payoutRequest->getBookingPayments();

        return view('vendor.payouts.show', compact('payoutRequest', 'bookingPayments'));
    }

    /**
     * Submit payout request for approval
     */
    public function submit(PayoutRequest $payoutRequest)
    {
        // Ensure vendor can only submit their own requests
        if ($payoutRequest->vendor_id !== Auth::id()) {
            abort(403);
        }

        try {
            $this->payoutService->submitForApproval($payoutRequest, Auth::user());

            return redirect()
                ->route('vendor.payouts.show', $payoutRequest)
                ->with('success', 'Payout request submitted for approval');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel payout request
     */
    public function cancel(PayoutRequest $payoutRequest)
    {
        // Ensure vendor can only cancel their own requests
        if ($payoutRequest->vendor_id !== Auth::id()) {
            abort(403);
        }

        try {
            if (!$payoutRequest->canCancel()) {
                throw new \Exception('This payout request cannot be cancelled');
            }

            $payoutRequest->cancel();

            return redirect()
                ->route('vendor.payouts.index')
                ->with('success', 'Payout request cancelled successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download settlement receipt
     */
    public function downloadReceipt(PayoutRequest $payoutRequest)
    {
        // Ensure vendor can only download their own receipts
        if ($payoutRequest->vendor_id !== Auth::id()) {
            abort(403);
        }

        try {
            if (!$payoutRequest->receipt_pdf_path) {
                throw new \Exception('Receipt not yet generated');
            }

            return $this->receiptService->downloadReceipt($payoutRequest);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
