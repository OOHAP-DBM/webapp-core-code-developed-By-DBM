<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SettlementBatch, VendorLedger, User};
use App\Services\PaymentSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SettlementController extends Controller
{
    protected PaymentSettlementService $settlementService;

    public function __construct(PaymentSettlementService $settlementService)
    {
        $this->settlementService = $settlementService;
    }

    /**
     * Settlement batches dashboard
     */
    public function index(Request $request)
    {
        $query = SettlementBatch::with(['creator', 'approver']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20);
        $statistics = $this->settlementService->getStatistics();

        return view('admin.settlements.index', compact('batches', 'statistics'));
    }

    /**
     * Show settlement batch details
     */
    public function show(SettlementBatch $batch)
    {
        $batch->load(['creator', 'approver', 'bookingPayments.booking.vendor']);

        return view('admin.settlements.show', compact('batch'));
    }

    /**
     * Show create batch form
     */
    public function create()
    {
        return view('admin.settlements.create');
    }

    /**
     * Create new settlement batch
     */
    public function store(Request $request)
    {
        $request->validate([
            'batch_name' => 'nullable|string|max:255',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $batch = $this->settlementService->createSettlementBatch(
            Carbon::parse($request->period_start),
            Carbon::parse($request->period_end),
            Auth::id(),
            $request->batch_name
        );

        return redirect()->route('admin.settlements.show', $batch)
            ->with('success', 'Settlement batch created successfully');
    }

    /**
     * Submit batch for approval
     */
    public function submitForApproval(SettlementBatch $batch)
    {
        try {
            $this->settlementService->submitForApproval($batch);

            return redirect()->route('admin.settlements.show', $batch)
                ->with('success', 'Batch submitted for approval');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve settlement batch
     */
    public function approve(Request $request, SettlementBatch $batch)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->settlementService->approveBatch(
                $batch,
                Auth::id(),
                $request->approval_notes
            );

            return redirect()->route('admin.settlements.show', $batch)
                ->with('success', 'Settlement batch approved successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process approved settlement batch
     */
    public function process(SettlementBatch $batch)
    {
        try {
            $results = $this->settlementService->processBatch($batch);

            $message = sprintf(
                'Batch processed. Success: %d, Held: %d, Failed: %d',
                count($results['success']),
                count($results['held']),
                count($results['failed'])
            );

            return redirect()->route('admin.settlements.show', $batch)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Vendor ledger dashboard
     */
    public function ledgers(Request $request)
    {
        $query = User::role('vendor')->withCount('ledgerEntries');

        // Search by vendor name/email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $vendors = $query->paginate(20);

        // Add balance calculation for each vendor
        $vendors->getCollection()->transform(function ($vendor) {
            $vendor->ledger_balance = VendorLedger::calculateVendorBalance($vendor->id);
            return $vendor;
        });

        return view('admin.settlements.ledgers', compact('vendors'));
    }

    /**
     * Show vendor ledger details
     */
    public function vendorLedger(Request $request, User $vendor)
    {
        $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : null;
        $endDate = $request->has('end_date') ? Carbon::parse($request->end_date) : null;

        $ledgerData = $this->settlementService->getVendorLedgerSummary(
            $vendor->id,
            $startDate,
            $endDate
        );

        return view('admin.settlements.vendor-ledger', [
            'vendor' => $vendor,
            'balance' => $ledgerData['balance'],
            'entries' => $ledgerData['entries'],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Release held amounts for vendor
     */
    public function releaseHeldAmounts(User $vendor)
    {
        try {
            $released = $this->settlementService->releaseHeldAmounts($vendor->id, Auth::id());

            return redirect()->route('admin.settlements.vendor-ledger', $vendor)
                ->with('success', sprintf('%d held entries released', count($released)));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Manual ledger adjustment
     */
    public function createAdjustment(Request $request, User $vendor)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $entry = VendorLedger::recordTransaction([
                'vendor_id' => $vendor->id,
                'transaction_type' => 'adjustment',
                'amount' => $request->amount,
                'description' => $request->description,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('admin.settlements.vendor-ledger', $vendor)
                ->with('success', 'Ledger adjustment recorded successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
