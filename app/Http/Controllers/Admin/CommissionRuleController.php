<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRule;
use App\Models\User;
use App\Models\Hoarding;
use App\Services\CommissionRuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionRuleController extends Controller
{
    protected CommissionRuleService $ruleService;

    public function __construct(CommissionRuleService $ruleService)
    {
        $this->ruleService = $ruleService;
    }

    /**
     * Display commission rules dashboard
     */
    public function index()
    {
        $rules = CommissionRule::with(['vendor', 'hoarding', 'creator'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statistics = $this->ruleService->getStatistics();
        $seasonalOffers = $this->ruleService->getActiveSeasonalOffers();

        return view('admin.commission-rules.index', compact('rules', 'statistics', 'seasonalOffers'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $vendors = User::where('role', 'vendor')->orderBy('name')->get();
        $hoardings = Hoarding::with('vendor')->orderBy('title')->get();

        return view('admin.commission-rules.create', compact('vendors', 'hoardings'));
    }

    /**
     * Store new commission rule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'priority' => 'required|integer|min:0',
            'rule_type' => 'required|in:vendor,hoarding,location,flat,time_based,seasonal',
            'vendor_id' => 'nullable|exists:users,id',
            'hoarding_id' => 'nullable|exists:hoardings,id',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'hoarding_type' => 'nullable|in:billboard,digital,transit,street_furniture,wallscape,mobile',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|min:0|max:6',
            'time_range' => 'nullable|array',
            'is_seasonal' => 'boolean',
            'season_name' => 'nullable|string|max:255',
            'commission_type' => 'required|in:percentage,fixed,tiered',
            'commission_value' => 'required|numeric|min:0',
            'tiered_config' => 'nullable|array',
            'enable_distribution' => 'boolean',
            'distribution_config' => 'nullable|array',
            'min_booking_amount' => 'nullable|numeric|min:0',
            'max_booking_amount' => 'nullable|numeric|min:0',
            'min_duration_days' => 'nullable|integer|min:1',
            'max_duration_days' => 'nullable|integer|min:1',
        ]);

        // Validate rule configuration
        $validation = $this->ruleService->validateRuleConfig($validated);
        if (!$validation['valid']) {
            return back()->withErrors(['config' => $validation['errors']])->withInput();
        }

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');
        $validated['is_seasonal'] = $request->has('is_seasonal');
        $validated['enable_distribution'] = $request->has('enable_distribution');

        $rule = CommissionRule::create($validated);

        return redirect()->route('admin.commission-rules.index')
            ->with('success', "Commission rule '{$rule->name}' created successfully");
    }

    /**
     * Show edit form
     */
    public function edit(CommissionRule $commissionRule)
    {
        $vendors = User::where('role', 'vendor')->orderBy('name')->get();
        $hoardings = Hoarding::with('vendor')->orderBy('title')->get();

        return view('admin.commission-rules.edit', compact('commissionRule', 'vendors', 'hoardings'));
    }

    /**
     * Update commission rule
     */
    public function update(Request $request, CommissionRule $commissionRule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'priority' => 'required|integer|min:0',
            'rule_type' => 'required|in:vendor,hoarding,location,flat,time_based,seasonal',
            'vendor_id' => 'nullable|exists:users,id',
            'hoarding_id' => 'nullable|exists:hoardings,id',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'hoarding_type' => 'nullable|in:billboard,digital,transit,street_furniture,wallscape,mobile',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|min:0|max:6',
            'time_range' => 'nullable|array',
            'is_seasonal' => 'boolean',
            'season_name' => 'nullable|string|max:255',
            'commission_type' => 'required|in:percentage,fixed,tiered',
            'commission_value' => 'required|numeric|min:0',
            'tiered_config' => 'nullable|array',
            'enable_distribution' => 'boolean',
            'distribution_config' => 'nullable|array',
            'min_booking_amount' => 'nullable|numeric|min:0',
            'max_booking_amount' => 'nullable|numeric|min:0',
            'min_duration_days' => 'nullable|integer|min:1',
            'max_duration_days' => 'nullable|integer|min:1',
        ]);

        // Validate rule configuration
        $validation = $this->ruleService->validateRuleConfig($validated);
        if (!$validation['valid']) {
            return back()->withErrors(['config' => $validation['errors']])->withInput();
        }

        $validated['updated_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');
        $validated['is_seasonal'] = $request->has('is_seasonal');
        $validated['enable_distribution'] = $request->has('enable_distribution');

        $commissionRule->update($validated);

        return redirect()->route('admin.commission-rules.index')
            ->with('success', "Commission rule '{$commissionRule->name}' updated successfully");
    }

    /**
     * Delete commission rule
     */
    public function destroy(CommissionRule $commissionRule)
    {
        $name = $commissionRule->name;
        $commissionRule->delete();

        return redirect()->route('admin.commission-rules.index')
            ->with('success', "Commission rule '{$name}' deleted successfully");
    }

    /**
     * Toggle rule active status
     */
    public function toggleStatus(CommissionRule $commissionRule)
    {
        $commissionRule->update([
            'is_active' => !$commissionRule->is_active,
            'updated_by' => Auth::id(),
        ]);

        $status = $commissionRule->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Rule '{$commissionRule->name}' {$status} successfully");
    }

    /**
     * Preview commission calculation
     */
    public function preview(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'hoarding_id' => 'required|exists:hoardings,id',
            'amount' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $hoarding = Hoarding::findOrFail($request->hoarding_id);
        $address = $hoarding->address ?? '';
        $addressParts = explode(',', $address);

        $bookingData = [
            'vendor_id' => $request->vendor_id,
            'hoarding_id' => $request->hoarding_id,
            'hoarding_type' => $hoarding->type,
            'city' => trim($addressParts[count($addressParts) - 1] ?? ''),
            'area' => trim($addressParts[0] ?? ''),
            'amount' => $request->amount,
            'duration_days' => $request->duration_days,
            'booking_date' => now(),
        ];

        $preview = $this->ruleService->previewCommission($bookingData, $request->amount);

        return response()->json($preview);
    }

    /**
     * Show rule detail
     */
    public function show(CommissionRule $commissionRule)
    {
        $commissionRule->load(['vendor', 'hoarding', 'creator', 'updater']);

        return view('admin.commission-rules.show', compact('commissionRule'));
    }

    /**
     * Duplicate a rule
     */
    public function duplicate(CommissionRule $commissionRule)
    {
        $newRule = $commissionRule->replicate();
        $newRule->name = $commissionRule->name . ' (Copy)';
        $newRule->is_active = false;
        $newRule->usage_count = 0;
        $newRule->last_used_at = null;
        $newRule->created_by = Auth::id();
        $newRule->updated_by = null;
        $newRule->save();

        return redirect()->route('admin.commission-rules.edit', $newRule)
            ->with('success', "Rule duplicated successfully. Please review and activate.");
    }
}
