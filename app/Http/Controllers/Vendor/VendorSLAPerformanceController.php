<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorSLAViolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * VendorSLAPerformanceController
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Vendor controller for viewing own SLA performance
 */
class VendorSLAPerformanceController extends Controller
{
    /**
     * Display vendor's SLA performance dashboard
     */
    public function index()
    {
        $vendor = auth()->user();

        $performance = $vendor->getPerformanceSummary();

        // Get upcoming deadlines
        $upcomingDeadlines = [];
        
        // Get pending quote requests with acceptance deadlines
        $pendingAcceptances = \App\Models\QuoteRequest::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->whereNotNull('sla_acceptance_deadline')
            ->whereNull('vendor_accepted_at')
            ->get(['id', 'sla_acceptance_deadline', 'created_at']);

        foreach ($pendingAcceptances as $request) {
            $upcomingDeadlines[] = [
                'type' => 'acceptance',
                'quote_request_id' => $request->id,
                'deadline' => $request->sla_acceptance_deadline,
                'time_remaining' => now()->diffForHumans($request->sla_acceptance_deadline, true),
            ];
        }

        // Get accepted requests with quote deadlines
        $pendingQuotes = \App\Models\QuoteRequest::where('vendor_id', $vendor->id)
            ->where('status', 'accepted')
            ->whereNotNull('sla_quote_deadline')
            ->whereDoesntHave('vendorQuotes', function ($query) {
                $query->where('status', '!=', 'draft');
            })
            ->get(['id', 'sla_quote_deadline', 'vendor_accepted_at']);

        foreach ($pendingQuotes as $request) {
            $upcomingDeadlines[] = [
                'type' => 'quote_submission',
                'quote_request_id' => $request->id,
                'deadline' => $request->sla_quote_deadline,
                'time_remaining' => now()->diffForHumans($request->sla_quote_deadline, true),
            ];
        }

        // Sort by deadline
        usort($upcomingDeadlines, function ($a, $b) {
            return strtotime($a['deadline']) - strtotime($b['deadline']);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'performance' => $performance,
                'upcoming_deadlines' => $upcomingDeadlines,
                'sla_setting' => \App\Models\VendorSLASetting::getForVendor($vendor),
            ],
        ]);
    }

    /**
     * Get vendor's violations
     */
    public function violations(Request $request)
    {
        $vendor = auth()->user();

        $query = $vendor->slaViolations();

        // Filter by severity
        if ($request->has('severity')) {
            $query->bySeverity($request->severity);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
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
     * View specific violation
     */
    public function showViolation(VendorSLAViolation $violation)
    {
        // Ensure vendor can only view their own violations
        if ($violation->vendor_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $violation->load(['slaSetting', 'violatable', 'reviewedBy']);

        return response()->json([
            'success' => true,
            'data' => $violation,
        ]);
    }

    /**
     * Dispute a violation
     */
    public function disputeViolation(Request $request, VendorSLAViolation $violation)
    {
        // Ensure vendor can only dispute their own violations
        if ($violation->vendor_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'explanation' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $violation->dispute($request->explanation);

            return response()->json([
                'success' => true,
                'message' => 'Violation disputed successfully. Admin will review your explanation.',
                'data' => $violation->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get vendor's performance statistics
     */
    public function statistics()
    {
        $vendor = auth()->user();

        $stats = [
            'current_month' => [
                'violations' => $vendor->slaViolations()->thisMonth()->count(),
                'minor' => $vendor->slaViolations()->thisMonth()->bySeverity('minor')->count(),
                'major' => $vendor->slaViolations()->thisMonth()->bySeverity('major')->count(),
                'critical' => $vendor->slaViolations()->thisMonth()->bySeverity('critical')->count(),
            ],
            'all_time' => [
                'violations' => $vendor->sla_violations_count,
                'minor' => $vendor->slaViolations()->bySeverity('minor')->count(),
                'major' => $vendor->slaViolations()->bySeverity('major')->count(),
                'critical' => $vendor->slaViolations()->bySeverity('critical')->count(),
            ],
            'last_30_days' => [
                'violations' => $vendor->slaViolations()->recent()->count(),
                'acceptance_rate' => $vendor->on_time_acceptance_rate,
                'quote_rate' => $vendor->on_time_quote_rate,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get vendor's performance history (monthly breakdown)
     */
    public function history(Request $request)
    {
        $vendor = auth()->user();
        $months = $request->get('months', 6);

        $history = [];
        for ($i = 0; $i < $months; $i++) {
            $date = now()->subMonths($i);
            $history[] = [
                'month' => $date->format('M Y'),
                'violations' => $vendor->slaViolations()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'minor' => $vendor->slaViolations()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->bySeverity('minor')
                    ->count(),
                'major' => $vendor->slaViolations()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->bySeverity('major')
                    ->count(),
                'critical' => $vendor->slaViolations()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->bySeverity('critical')
                    ->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => array_reverse($history),
        ]);
    }
}
