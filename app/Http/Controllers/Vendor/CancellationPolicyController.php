<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\CancellationPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Vendor Cancellation Policy Controller
 * 
 * Allows vendors to create and manage their own cancellation policies.
 * Vendor policies override global policies for their bookings.
 */
class CancellationPolicyController extends Controller
{
    /**
     * Display vendor's cancellation policies
     */
    public function index()
    {
        $vendor = Auth::user();

        $policies = CancellationPolicy::with(['creator', 'updater'])
            ->where(function($query) use ($vendor) {
                $query->forVendor($vendor->id)
                      ->orWhere(function($q) {
                          $q->global()->where('is_default', true);
                      });
            })
            ->orderByRaw('vendor_id IS NULL') // Vendor policies first
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Split into vendor and global policies
        $vendorPolicies = $policies->filter(fn($p) => $p->vendor_id === $vendor->id);
        $globalPolicies = $policies->filter(fn($p) => $p->vendor_id === null);

        return view('vendor.cancellation-policies.index', compact('vendorPolicies', 'globalPolicies'));
    }

    /**
     * Show create policy form
     */
    public function create()
    {
        return view('vendor.cancellation-policies.create');
    }

    /**
     * Store new vendor policy
     */
    public function store(Request $request)
    {
        $vendor = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'booking_type' => 'nullable|in:ooh,dooh,pos',
            'time_windows' => 'required|array|min:1',
            'time_windows.*.hours_before' => 'required|integer|min:0',
            'time_windows.*.refund_percent' => 'required|integer|min:0|max:100',
            'time_windows.*.customer_fee_percent' => 'nullable|integer|min:0|max:100',
            'customer_fee_type' => 'required|in:percentage,fixed',
            'customer_fee_value' => 'required|numeric|min:0',
            'customer_min_fee' => 'nullable|numeric|min:0',
            'customer_max_fee' => 'nullable|numeric|min:0',
            'auto_refund_enabled' => 'boolean',
            'enforce_campaign_start' => 'boolean',
            'allow_partial_refund' => 'boolean',
            'refund_processing_days' => 'required|integer|min:1|max:30',
        ]);

        // Process time_windows array
        $timeWindows = [];
        foreach ($request->input('time_windows') as $window) {
            $timeWindows[] = [
                'hours_before' => (int)$window['hours_before'],
                'refund_percent' => (int)$window['refund_percent'],
                'customer_fee_percent' => isset($window['customer_fee_percent']) && $window['customer_fee_percent'] !== '' 
                    ? (int)$window['customer_fee_percent'] 
                    : null,
            ];
        }

        // Sort by hours_before descending
        usort($timeWindows, fn($a, $b) => $b['hours_before'] <=> $a['hours_before']);

        // Vendor policies cannot be default and always apply to 'customer' role
        $policyData = array_merge($validated, [
            'vendor_id' => $vendor->id,
            'time_windows' => $timeWindows,
            'is_active' => $request->has('is_active'),
            'is_default' => false, // Vendor policies cannot be system default
            'applies_to' => 'customer', // Vendor policies apply to customer cancellations
            'auto_refund_enabled' => $request->has('auto_refund_enabled'),
            'enforce_campaign_start' => $request->has('enforce_campaign_start') || !$request->has('allow_refund_after_start'),
            'allow_partial_refund' => $request->has('allow_partial_refund'),
            'pos_auto_refund_disabled' => true, // POS always manual
            'vendor_penalty_type' => 'percentage',
            'vendor_penalty_value' => 0, // No vendor penalty for own policy
            'created_by' => $vendor->id,
        ]);

        $policy = CancellationPolicy::create($policyData);

        Log::info('Vendor cancellation policy created', [
            'vendor_id' => $vendor->id,
            'policy_id' => $policy->id,
            'policy_name' => $policy->name,
        ]);

        return redirect()->route('vendor.cancellation-policies.index')
            ->with('success', 'Cancellation policy created successfully! This policy will now apply to your bookings.');
    }

    /**
     * Show edit policy form
     */
    public function edit(int $id)
    {
        $vendor = Auth::user();
        $policy = CancellationPolicy::forVendor($vendor->id)->findOrFail($id);

        return view('vendor.cancellation-policies.edit', compact('policy'));
    }

    /**
     * Update vendor policy
     */
    public function update(Request $request, int $id)
    {
        $vendor = Auth::user();
        $policy = CancellationPolicy::forVendor($vendor->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'booking_type' => 'nullable|in:ooh,dooh,pos',
            'time_windows' => 'required|array|min:1',
            'time_windows.*.hours_before' => 'required|integer|min:0',
            'time_windows.*.refund_percent' => 'required|integer|min:0|max:100',
            'time_windows.*.customer_fee_percent' => 'nullable|integer|min:0|max:100',
            'customer_fee_type' => 'required|in:percentage,fixed',
            'customer_fee_value' => 'required|numeric|min:0',
            'customer_min_fee' => 'nullable|numeric|min:0',
            'customer_max_fee' => 'nullable|numeric|min:0',
            'auto_refund_enabled' => 'boolean',
            'enforce_campaign_start' => 'boolean',
            'allow_partial_refund' => 'boolean',
            'refund_processing_days' => 'required|integer|min:1|max:30',
        ]);

        // Process time_windows array
        $timeWindows = [];
        foreach ($request->input('time_windows') as $window) {
            $timeWindows[] = [
                'hours_before' => (int)$window['hours_before'],
                'refund_percent' => (int)$window['refund_percent'],
                'customer_fee_percent' => isset($window['customer_fee_percent']) && $window['customer_fee_percent'] !== '' 
                    ? (int)$window['customer_fee_percent'] 
                    : null,
            ];
        }

        usort($timeWindows, fn($a, $b) => $b['hours_before'] <=> $a['hours_before']);

        $policy->update(array_merge($validated, [
            'time_windows' => $timeWindows,
            'is_active' => $request->has('is_active'),
            'auto_refund_enabled' => $request->has('auto_refund_enabled'),
            'enforce_campaign_start' => $request->has('enforce_campaign_start') || !$request->has('allow_refund_after_start'),
            'allow_partial_refund' => $request->has('allow_partial_refund'),
            'updated_by' => $vendor->id,
        ]));

        Log::info('Vendor cancellation policy updated', [
            'vendor_id' => $vendor->id,
            'policy_id' => $policy->id,
        ]);

        return redirect()->route('vendor.cancellation-policies.index')
            ->with('success', 'Cancellation policy updated successfully!');
    }

    /**
     * Toggle policy active status
     */
    public function toggleStatus(int $id)
    {
        $vendor = Auth::user();
        $policy = CancellationPolicy::forVendor($vendor->id)->findOrFail($id);

        $policy->update([
            'is_active' => !$policy->is_active,
            'updated_by' => $vendor->id,
        ]);

        $status = $policy->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Policy {$status} successfully",
            'is_active' => $policy->is_active,
        ]);
    }

    /**
     * Delete vendor policy
     */
    public function destroy(int $id)
    {
        $vendor = Auth::user();
        $policy = CancellationPolicy::forVendor($vendor->id)->findOrFail($id);

        // Check if policy is being used
        if ($policy->refunds()->exists()) {
            return back()->with('error', 'Cannot delete policy that has been used for refunds.');
        }

        $policy->delete();

        Log::info('Vendor cancellation policy deleted', [
            'vendor_id' => $vendor->id,
            'policy_id' => $id,
        ]);

        return redirect()->route('vendor.cancellation-policies.index')
            ->with('success', 'Cancellation policy deleted successfully!');
    }

    /**
     * Preview refund calculation
     */
    public function previewRefund(Request $request)
    {
        $validated = $request->validate([
            'policy_id' => 'required|exists:cancellation_policies,id',
            'booking_amount' => 'required|numeric|min:0',
            'hours_before_start' => 'required|integer|min:0',
        ]);

        $policy = CancellationPolicy::findOrFail($validated['policy_id']);
        $calculation = $policy->calculateRefund(
            $validated['booking_amount'],
            $validated['hours_before_start'],
            'customer'
        );

        return response()->json([
            'success' => true,
            'calculation' => $calculation,
            'formatted' => [
                'booking_amount' => '₹' . number_format($validated['booking_amount'], 2),
                'refundable_amount' => '₹' . number_format($calculation['refundable_amount'], 2),
                'customer_fee' => '₹' . number_format($calculation['customer_fee'], 2),
                'refund_amount' => '₹' . number_format($calculation['refund_amount'], 2),
                'refund_percent' => $calculation['refund_percent'] . '%',
            ],
        ]);
    }
}
