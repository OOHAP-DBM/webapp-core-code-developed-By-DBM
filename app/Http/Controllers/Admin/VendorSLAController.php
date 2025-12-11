<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorSLASetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * VendorSLAController
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Admin controller for managing SLA settings
 */
class VendorSLAController extends Controller
{
    /**
     * Display a listing of SLA settings
     */
    public function index()
    {
        $settings = VendorSLASetting::with(['violations', 'vendors'])
            ->withCount(['violations', 'vendors'])
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Store a newly created SLA setting
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vendor_sla_settings',
            'description' => 'nullable|string',
            'enquiry_acceptance_hours' => 'required|integer|min:1|max:168',
            'quote_submission_hours' => 'required|integer|min:1|max:720',
            'quote_revision_hours' => 'nullable|integer|min:1|max:168',
            'enquiry_response_hours' => 'nullable|integer|min:1|max:720',
            'warning_threshold_percentage' => 'required|integer|min:50|max:90',
            'grace_period_hours' => 'required|integer|min:0|max:24',
            'minor_violation_penalty' => 'required|numeric|min:0|max:50',
            'major_violation_penalty' => 'required|numeric|min:0|max:50',
            'critical_violation_penalty' => 'required|numeric|min:0|max:50',
            'max_violations_per_month' => 'required|integer|min:1|max:50',
            'critical_violation_threshold' => 'required|integer|min:1|max:100',
            'auto_mark_violated' => 'boolean',
            'auto_notify_vendor' => 'boolean',
            'auto_notify_admin' => 'boolean',
            'auto_escalate_critical' => 'boolean',
            'reliability_recovery_days' => 'required|integer|min:1|max:365',
            'recovery_rate_per_day' => 'required|numeric|min:0.1|max:10',
            'count_business_hours_only' => 'boolean',
            'business_hours' => 'nullable|array',
            'excluded_days' => 'nullable|array',
            'applies_to' => 'required|in:all,new,verified,premium',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting = VendorSLASetting::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'SLA setting created successfully',
            'data' => $setting,
        ], 201);
    }

    /**
     * Display the specified SLA setting
     */
    public function show(VendorSLASetting $setting)
    {
        $setting->load(['violations' => function ($query) {
            $query->latest()->limit(10);
        }, 'vendors']);

        $setting->loadCount(['violations', 'vendors']);

        // Get statistics
        $stats = [
            'total_violations' => $setting->violations()->count(),
            'violations_this_month' => $setting->violations()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'minor_violations' => $setting->violations()->bySeverity('minor')->count(),
            'major_violations' => $setting->violations()->bySeverity('major')->count(),
            'critical_violations' => $setting->violations()->bySeverity('critical')->count(),
            'assigned_vendors' => $setting->vendors()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'setting' => $setting,
                'statistics' => $stats,
            ],
        ]);
    }

    /**
     * Update the specified SLA setting
     */
    public function update(Request $request, VendorSLASetting $setting)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:vendor_sla_settings,name,' . $setting->id,
            'description' => 'nullable|string',
            'enquiry_acceptance_hours' => 'sometimes|integer|min:1|max:168',
            'quote_submission_hours' => 'sometimes|integer|min:1|max:720',
            'quote_revision_hours' => 'nullable|integer|min:1|max:168',
            'enquiry_response_hours' => 'nullable|integer|min:1|max:720',
            'warning_threshold_percentage' => 'sometimes|integer|min:50|max:90',
            'grace_period_hours' => 'sometimes|integer|min:0|max:24',
            'minor_violation_penalty' => 'sometimes|numeric|min:0|max:50',
            'major_violation_penalty' => 'sometimes|numeric|min:0|max:50',
            'critical_violation_penalty' => 'sometimes|numeric|min:0|max:50',
            'max_violations_per_month' => 'sometimes|integer|min:1|max:50',
            'critical_violation_threshold' => 'sometimes|integer|min:1|max:100',
            'auto_mark_violated' => 'boolean',
            'auto_notify_vendor' => 'boolean',
            'auto_notify_admin' => 'boolean',
            'auto_escalate_critical' => 'boolean',
            'reliability_recovery_days' => 'sometimes|integer|min:1|max:365',
            'recovery_rate_per_day' => 'sometimes|numeric|min:0.1|max:10',
            'count_business_hours_only' => 'boolean',
            'business_hours' => 'nullable|array',
            'excluded_days' => 'nullable|array',
            'applies_to' => 'sometimes|in:all,new,verified,premium',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'SLA setting updated successfully',
            'data' => $setting->fresh(),
        ]);
    }

    /**
     * Remove the specified SLA setting
     */
    public function destroy(VendorSLASetting $setting)
    {
        if ($setting->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default SLA setting',
            ], 403);
        }

        if ($setting->vendors()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete SLA setting with assigned vendors',
            ], 403);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'SLA setting deleted successfully',
        ]);
    }

    /**
     * Set as default SLA setting
     */
    public function setDefault(VendorSLASetting $setting)
    {
        // Remove default from all others
        VendorSLASetting::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $setting->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default SLA setting updated successfully',
            'data' => $setting->fresh(),
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(VendorSLASetting $setting)
    {
        if ($setting->is_default && $setting->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate default SLA setting',
            ], 403);
        }

        $setting->update(['is_active' => !$setting->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'SLA setting status updated successfully',
            'data' => $setting->fresh(),
        ]);
    }

    /**
     * Get SLA statistics
     */
    public function statistics()
    {
        $stats = [
            'total_settings' => VendorSLASetting::count(),
            'active_settings' => VendorSLASetting::where('is_active', true)->count(),
            'default_setting' => VendorSLASetting::getDefault(),
            'total_violations_today' => \App\Models\VendorSLAViolation::whereDate('created_at', today())->count(),
            'total_violations_this_month' => \App\Models\VendorSLAViolation::thisMonth()->count(),
            'critical_violations_this_month' => \App\Models\VendorSLAViolation::thisMonth()->critical()->count(),
            'pending_violations' => \App\Models\VendorSLAViolation::pending()->count(),
            'disputed_violations' => \App\Models\VendorSLAViolation::disputed()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
