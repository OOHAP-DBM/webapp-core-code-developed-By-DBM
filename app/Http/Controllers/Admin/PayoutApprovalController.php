<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\{PayoutService, PayoutReceiptService};
use Illuminate\Http\Request;

/**
 * PROMPT 58: Admin Payout Approval Controller
 * 
 * Handles admin approval and settlement processing for vendor payout requests
 */
class PayoutApprovalController extends Controller
{
    protected PayoutService $payoutService;
    protected PayoutReceiptService $receiptService;

    public function __construct(PayoutService $payoutService, PayoutReceiptService $receiptService)
    {
        $this->middleware('auth');
        $this->middleware('role:admin|super-admin');
        $this->payoutService = $payoutService;
        $this->receiptService = $receiptService;
    }

    /**
     * Display admin payout dashboard
     */
    public function index()
    {
        $statistics = $this->payoutService->getAdminPayoutStatistics();
        
        $pendingRequests = PayoutRequest::with('vendor')
            ->pendingApproval()
            ->orderBy('submitted_at', 'asc')
            ->paginate(15, ['*'], 'pending_page');

        $approvedRequests = PayoutRequest::with('vendor')
            ->approved()
            ->orderBy('approved_at', 'desc')
            ->paginate(15, ['*'], 'approved_page');

        return view('admin.payouts.index', compact('statistics', 'pendingRequests', 'approvedRequests'));
    }

    /**
     * Show all payout requests (with filters)
     */
    public function allRequests(Request $request)
    {
        $query = PayoutRequest::with('vendor');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $payoutRequests = $query->orderBy('created_at', 'desc')->paginate(20);
        $statistics = $this->payoutService->getAdminPayoutStatistics();

        return view('admin.payouts.all', compact('payoutRequests', 'statistics'));
    }

    /**
     * Show payout request details
     */
    public function show(PayoutRequest $payoutRequest)
    {
        $payoutRequest->load(['vendor', 'submitter', 'approver', 'rejecter']);
        $bookingPayments = $payoutRequest->getBookingPayments();

        return view('admin.payouts.show', compact('payoutRequest', 'bookingPayments'));
    }

    /**
     * Approve payout request
     */
    public function approve(Request $request, PayoutRequest $payoutRequest)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->payoutService->approvePayoutRequest(
                $payoutRequest,
                auth()->user(),
                $request->approval_notes
            );

            return redirect()
                ->route('admin.payouts.show', $payoutRequest)
                ->with('success', 'Payout request approved successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject payout request
     */
    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $this->payoutService->rejectPayoutRequest(
                $payoutRequest,
                auth()->user(),
                $request->rejection_reason
            );

            return redirect()
                ->route('admin.payouts.show', $payoutRequest)
                ->with('success', 'Payout request rejected');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process settlement (mark as paid)
     */
    public function processSettlement(Request $request, PayoutRequest $payoutRequest)
    {
        $request->validate([
            'payout_mode' => 'required|in:bank_transfer,razorpay_transfer,upi,cheque,manual',
            'payout_reference' => 'required|string|max:255',
            'payout_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->payoutService->processPayoutSettlement(
                $payoutRequest,
                $request->payout_mode,
                $request->payout_reference,
                $request->payout_notes
            );

            // Generate settlement receipt
            $this->receiptService->generateReceipt($payoutRequest);

            return redirect()
                ->route('admin.payouts.show', $payoutRequest)
                ->with('success', 'Payout settlement processed successfully. Receipt generated.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Generate settlement receipt
     */
    public function generateReceipt(PayoutRequest $payoutRequest)
    {
        try {
            $path = $this->receiptService->generateReceipt($payoutRequest);

            return redirect()
                ->route('admin.payouts.show', $payoutRequest)
                ->with('success', 'Settlement receipt generated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download settlement receipt
     */
    public function downloadReceipt(PayoutRequest $payoutRequest)
    {
        try {
            return $this->receiptService->downloadReceipt($payoutRequest);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Regenerate settlement receipt
     */
    public function regenerateReceipt(PayoutRequest $payoutRequest)
    {
        try {
            $path = $this->receiptService->regenerateReceipt($payoutRequest);

            return redirect()
                ->route('admin.payouts.show', $payoutRequest)
                ->with('success', 'Settlement receipt regenerated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk approve payout requests
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payout_request_ids' => 'required|array',
            'payout_request_ids.*' => 'exists:payout_requests,id',
        ]);

        try {
            $successCount = 0;
            $failedCount = 0;

            foreach ($request->payout_request_ids as $id) {
                try {
                    $payoutRequest = PayoutRequest::find($id);
                    $this->payoutService->approvePayoutRequest($payoutRequest, auth()->user());
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            return redirect()
                ->route('admin.payouts.index')
                ->with('success', "Approved {$successCount} payout request(s). Failed: {$failedCount}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
