<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorSLAViolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * VendorReliabilityController
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Admin controller for viewing and managing vendor reliability scores
 */
class VendorReliabilityController extends Controller
{
    /**
     * Display vendor reliability dashboard
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])
            ->select([
                'id', 'name', 'email', 'role', 'reliability_score', 'reliability_tier',
                'sla_violations_count', 'sla_violations_this_month', 'total_penalty_points',
                'on_time_acceptance_rate', 'on_time_quote_rate', 'avg_acceptance_time_hours',
                'avg_quote_time_hours', 'last_sla_violation_at', 'created_at'
            ]);

        // Filter by tier
        if ($request->has('tier')) {
            $query->where('reliability_tier', $request->tier);
        }

        // Filter by score range
        if ($request->has('min_score')) {
            $query->where('reliability_score', '>=', $request->min_score);
        }
        if ($request->has('max_score')) {
            $query->where('reliability_score', '<=', $request->max_score);
        }

        // Filter by violation count
        if ($request->has('min_violations')) {
            $query->where('sla_violations_count', '>=', $request->min_violations);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'reliability_score');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $vendors = $query->paginate($request->get('per_page', 20));

        // Calculate summary stats
        $stats = [
            'total_vendors' => User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])->count(),
            'excellent_vendors' => User::where('reliability_tier', 'excellent')->count(),
            'good_vendors' => User::where('reliability_tier', 'good')->count(),
            'average_vendors' => User::where('reliability_tier', 'average')->count(),
            'poor_vendors' => User::where('reliability_tier', 'poor')->count(),
            'critical_vendors' => User::where('reliability_tier', 'critical')->count(),
            'avg_reliability_score' => round(User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])->avg('reliability_score'), 2),
            'vendors_at_risk' => User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])
                ->where(function ($q) {
                    $q->whereBetween('reliability_score', [40, 60])
                      ->orWhere('sla_violations_this_month', '>=', 2)
                      ->orWhere('sla_violations_count', '>=', 5);
                })
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'vendors' => $vendors,
                'statistics' => $stats,
            ],
        ]);
    }

    /**
     * Display specific vendor reliability details
     */
    public function show(User $vendor)
    {
        if (!in_array($vendor->role, ['vendor', 'premium_vendor', 'verified_vendor'])) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a vendor',
            ], 400);
        }

        $vendor->load([
            'slaViolations' => function ($query) {
                $query->latest()->limit(20);
            },
            'customSLASetting',
        ]);

        // Get violation breakdown
        $violationStats = [
            'total' => $vendor->sla_violations_count,
            'this_month' => $vendor->sla_violations_this_month,
            'by_severity' => [
                'minor' => $vendor->slaViolations()->bySeverity('minor')->count(),
                'major' => $vendor->slaViolations()->bySeverity('major')->count(),
                'critical' => $vendor->slaViolations()->bySeverity('critical')->count(),
            ],
            'by_type' => [
                'acceptance_late' => $vendor->slaViolations()->where('violation_type', 'enquiry_acceptance_late')->count(),
                'quote_late' => $vendor->slaViolations()->where('violation_type', 'quote_submission_late')->count(),
                'no_response' => $vendor->slaViolations()->where('violation_type', 'no_response')->count(),
            ],
            'pending' => $vendor->slaViolations()->pending()->count(),
            'disputed' => $vendor->slaViolations()->disputed()->count(),
        ];

        // Get performance summary
        $performance = $vendor->getPerformanceSummary();

        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => $vendor,
                'violation_statistics' => $violationStats,
                'performance' => $performance,
            ],
        ]);
    }

    /**
     * Get all violations for a vendor
     */
    public function violations(User $vendor, Request $request)
    {
        if (!in_array($vendor->role, ['vendor', 'premium_vendor', 'verified_vendor'])) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a vendor',
            ], 400);
        }

        $query = $vendor->slaViolations();

        // Filter by severity
        if ($request->has('severity')) {
            $query->bySeverity($request->severity);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('violation_type', $request->type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $violations = $query->with(['slaSetting', 'violatable'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $violations,
        ]);
    }

    /**
     * Manually adjust vendor reliability score
     */
    public function adjustScore(Request $request, User $vendor)
    {
        $validator = Validator::make($request->all(), [
            'adjustment' => 'required|numeric|min:-50|max:50',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!in_array($vendor->role, ['vendor', 'premium_vendor', 'verified_vendor'])) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a vendor',
            ], 400);
        }

        $oldScore = $vendor->reliability_score;
        $newScore = max(0, min(100, $oldScore + $request->adjustment));

        $vendor->update([
            'reliability_score' => $newScore,
            'last_score_update_at' => now(),
        ]);

        $vendor->updateReliabilityTier();

        // Log the manual adjustment
        \Log::info('Manual reliability score adjustment', [
            'vendor_id' => $vendor->id,
            'admin_id' => auth()->id(),
            'old_score' => $oldScore,
            'new_score' => $newScore,
            'adjustment' => $request->adjustment,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reliability score adjusted successfully',
            'data' => [
                'old_score' => $oldScore,
                'new_score' => $newScore,
                'new_tier' => $vendor->reliability_tier,
            ],
        ]);
    }

    /**
     * Assign custom SLA setting to vendor
     */
    public function assignSLASetting(Request $request, User $vendor)
    {
        $validator = Validator::make($request->all(), [
            'sla_setting_id' => 'required|exists:vendor_sla_settings,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vendor->update([
            'vendor_sla_setting_id' => $request->sla_setting_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom SLA setting assigned successfully',
            'data' => $vendor->load('customSLASetting'),
        ]);
    }

    /**
     * Remove custom SLA setting (revert to default)
     */
    public function removeSLASetting(User $vendor)
    {
        $vendor->update([
            'vendor_sla_setting_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom SLA setting removed, vendor will use default settings',
        ]);
    }

    /**
     * Get vendors at risk
     */
    public function atRisk(Request $request)
    {
        $vendors = User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])
            ->where(function ($q) {
                $q->whereBetween('reliability_score', [40, 60])
                  ->orWhere('sla_violations_this_month', '>=', 2)
                  ->orWhere('sla_violations_count', '>=', 5);
            })
            ->orderBy('reliability_score', 'asc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $vendors,
        ]);
    }

    /**
     * Get top performing vendors
     */
    public function topPerformers(Request $request)
    {
        $vendors = User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])
            ->where('reliability_score', '>=', 90)
            ->where('on_time_acceptance_rate', '>=', 95)
            ->where('on_time_quote_rate', '>=', 95)
            ->orderBy('reliability_score', 'desc')
            ->orderBy('on_time_acceptance_rate', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $vendors,
        ]);
    }

    /**
     * Export vendor reliability report
     */
    public function exportReport(Request $request)
    {
        $vendors = User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])
            ->get(['id', 'name', 'email', 'role', 'reliability_score', 'reliability_tier',
                   'sla_violations_count', 'sla_violations_this_month', 'on_time_acceptance_rate',
                   'on_time_quote_rate', 'avg_acceptance_time_hours', 'avg_quote_time_hours']);

        return response()->json([
            'success' => true,
            'data' => $vendors,
            'generated_at' => now()->toDateTimeString(),
        ]);
    }
}
