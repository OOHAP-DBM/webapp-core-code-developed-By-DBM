<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRefund;
use App\Models\CancellationPolicy;
use App\Services\BookingCancellationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    protected BookingCancellationService $cancellationService;

    public function __construct(BookingCancellationService $cancellationService)
    {
        $this->cancellationService = $cancellationService;
    }

    /**
     * Display refund report dashboard
     */
    public function index(Request $request)
    {
        $query = BookingRefund::with(['booking', 'cancelledBy', 'cancellationPolicy', 'approvedBy'])
            ->latest('initiated_at');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('refund_method')) {
            $query->where('refund_method', $request->refund_method);
        }
        if ($request->filled('cancelled_by_role')) {
            $query->where('cancelled_by_role', $request->cancelled_by_role);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('initiated_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('initiated_at', '<=', $request->date_to);
        }

        $refunds = $query->paginate(20);
        $statistics = $this->cancellationService->getStatistics();

        return view('admin.refunds.index', compact('refunds', 'statistics'));
    }

    /**
     * Show refund details
     */
    public function show(BookingRefund $refund)
    {
        $refund->load(['booking', 'cancelledBy', 'cancellationPolicy', 'approvedBy']);
        
        return view('admin.refunds.show', compact('refund'));
    }

    /**
     * Approve refund
     */
    public function approve(Request $request, BookingRefund $refund)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $refund->markAsApproved(Auth::id(), $request->notes);

        // Process auto-refund if applicable
        if ($refund->refund_method === 'auto') {
            try {
                $this->cancellationService->processAutoRefund($refund);
                return redirect()->back()->with('success', 'Refund approved and processed successfully');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Refund approved but processing failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Refund approved successfully');
    }

    /**
     * Process manual refund
     */
    public function processManual(Request $request, BookingRefund $refund)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->cancellationService->processAutoRefund($refund);
            return redirect()->back()->with('success', 'Manual refund processed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process manual refund: ' . $e->getMessage());
        }
    }

    /**
     * Export refund report
     */
    public function export(Request $request)
    {
        // TODO: Implement CSV/Excel export
        return redirect()->back()->with('info', 'Export functionality coming soon');
    }

    /**
     * Cancellation policies management
     */
    public function policies()
    {
        $policies = CancellationPolicy::with(['creator', 'updater'])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.cancellation-policies.index', compact('policies'));
    }

    /**
     * Show policy create form
     */
    public function createPolicy()
    {
        return view('admin.cancellation-policies.create');
    }

    /**
     * Store new policy
     */
    public function storePolicy(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'applies_to' => 'required|in:all,customer,vendor,admin',
            'booking_type' => 'nullable|in:ooh,dooh,pos',
            'time_windows' => 'required|array',
            'time_windows.*.hours_before' => 'required|integer|min:0',
            'time_windows.*.refund_percent' => 'required|integer|min:0|max:100',
            'customer_fee_type' => 'required|in:percentage,fixed',
            'customer_fee_value' => 'required|numeric|min:0',
            'vendor_penalty_type' => 'required|in:percentage,fixed',
            'vendor_penalty_value' => 'required|numeric|min:0',
            'auto_refund_enabled' => 'boolean',
            'refund_processing_days' => 'required|integer|min:1',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');
        $validated['auto_refund_enabled'] = $request->has('auto_refund_enabled');
        $validated['pos_auto_refund_disabled'] = $request->has('pos_auto_refund_disabled');

        // Process time_windows array
        if ($request->has('time_windows')) {
            $timeWindows = [];
            foreach ($request->input('time_windows') as $window) {
                $timeWindows[] = [
                    'hours_before' => (int)$window['hours_before'],
                    'refund_percent' => (int)$window['refund_percent'],
                    'customer_fee_percent' => isset($window['customer_fee_percent']) && $window['customer_fee_percent'] !== '' 
                        ? (int)$window['customer_fee_percent'] 
                        : null,
                    'vendor_penalty_percent' => isset($window['vendor_penalty_percent']) && $window['vendor_penalty_percent'] !== '' 
                        ? (int)$window['vendor_penalty_percent'] 
                        : null,
                ];
            }
            // Sort by hours_before descending
            usort($timeWindows, fn($a, $b) => $b['hours_before'] <=> $a['hours_before']);
            $validated['time_windows'] = $timeWindows;
        }

        // If set as default, unset other defaults
        if ($validated['is_default']) {
            CancellationPolicy::where('is_default', true)->update(['is_default' => false]);
        }

        CancellationPolicy::create($validated);

        return redirect()->route('admin.cancellation-policies.index')
            ->with('success', 'Cancellation policy created successfully');
    }

    /**
     * Show policy edit form
     */
    public function editPolicy(CancellationPolicy $policy)
    {
        return view('admin.cancellation-policies.edit', compact('policy'));
    }

    /**
     * Update existing policy
     */
    public function updatePolicy(Request $request, CancellationPolicy $policy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'applies_to' => 'required|in:all,customer,vendor,admin',
            'booking_type' => 'nullable|in:ooh,dooh,pos',
            'time_windows' => 'required|array',
            'time_windows.*.hours_before' => 'required|integer|min:0',
            'time_windows.*.refund_percent' => 'required|integer|min:0|max:100',
            'customer_fee_type' => 'required|in:percentage,fixed',
            'customer_fee_value' => 'required|numeric|min:0',
            'vendor_penalty_type' => 'required|in:percentage,fixed',
            'vendor_penalty_value' => 'required|numeric|min:0',
            'auto_refund_enabled' => 'boolean',
            'refund_processing_days' => 'required|integer|min:1',
            'enforce_campaign_start' => 'boolean',
            'allow_partial_refund' => 'boolean',
        ]);

        $validated['updated_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');
        $validated['auto_refund_enabled'] = $request->has('auto_refund_enabled');
        $validated['pos_auto_refund_disabled'] = $request->has('pos_auto_refund_disabled');
        $validated['enforce_campaign_start'] = $request->has('enforce_campaign_start');
        $validated['allow_partial_refund'] = $request->has('allow_partial_refund');

        // Process time_windows array
        if ($request->has('time_windows')) {
            $timeWindows = [];
            foreach ($request->input('time_windows') as $window) {
                $timeWindows[] = [
                    'hours_before' => (int)$window['hours_before'],
                    'refund_percent' => (int)$window['refund_percent'],
                    'customer_fee_percent' => isset($window['customer_fee_percent']) && $window['customer_fee_percent'] !== '' 
                        ? (int)$window['customer_fee_percent'] 
                        : null,
                    'vendor_penalty_percent' => isset($window['vendor_penalty_percent']) && $window['vendor_penalty_percent'] !== '' 
                        ? (int)$window['vendor_penalty_percent'] 
                        : null,
                ];
            }
            // Sort by hours_before descending
            usort($timeWindows, fn($a, $b) => $b['hours_before'] <=> $a['hours_before']);
            $validated['time_windows'] = $timeWindows;
        }

        // If set as default, unset other defaults
        if ($validated['is_default']) {
            CancellationPolicy::where('id', '!=', $policy->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $policy->update($validated);

        return redirect()->route('admin.cancellation-policies.index')
            ->with('success', 'Cancellation policy updated successfully');
    }

    /**
     * Delete policy
     */
    public function destroyPolicy(CancellationPolicy $policy)
    {
        // Check if policy is being used
        if ($policy->refunds()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete policy that has been used in refunds');
        }

        if ($policy->is_default) {
            return redirect()->back()->with('error', 'Cannot delete the default policy');
        }

        $policy->delete();

        return redirect()->route('admin.cancellation-policies.index')
            ->with('success', 'Cancellation policy deleted successfully');
    }

    /**
     * Toggle policy status
     */
    public function togglePolicyStatus(Request $request, CancellationPolicy $policy)
    {
        $policy->update(['is_active' => !$policy->is_active]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $policy->is_active,
            ]);
        }

        return redirect()->back()->with('success', 'Policy status updated');
    }

    /**
     * View all vendor-created policies
     */
    public function vendorPolicies()
    {
        $policies = CancellationPolicy::whereNotNull('vendor_id')
            ->with(['vendor', 'creator', 'updater'])
            ->latest()
            ->paginate(20);

        return view('admin.cancellation-policies.vendor-policies', compact('policies'));
    }
}
