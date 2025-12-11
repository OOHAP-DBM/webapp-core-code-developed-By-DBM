<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorSLAViolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * VendorSLAViolationController
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Admin controller for managing SLA violations
 */
class VendorSLAViolationController extends Controller
{
    /**
     * Display all violations
     */
    public function index(Request $request)
    {
        $query = VendorSLAViolation::with(['vendor', 'slaSetting', 'violatable']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->has('severity')) {
            $query->bySeverity($request->severity);
        }

        // Filter by vendor
        if ($request->has('vendor_id')) {
            $query->forVendor($request->vendor_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Only this month
        if ($request->has('this_month') && $request->this_month) {
            $query->thisMonth();
        }

        $violations = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $violations,
        ]);
    }

    /**
     * Display specific violation
     */
    public function show(VendorSLAViolation $violation)
    {
        $violation->load(['vendor', 'slaSetting', 'violatable', 'waivedBy', 'reviewedBy']);

        return response()->json([
            'success' => true,
            'data' => $violation,
        ]);
    }

    /**
     * Waive a violation
     */
    public function waive(Request $request, VendorSLAViolation $violation)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $violation->waive(auth()->user(), $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Violation waived successfully',
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
     * Resolve a dispute
     */
    public function resolveDispute(Request $request, VendorSLAViolation $violation)
    {
        $validator = Validator::make($request->all(), [
            'accepted' => 'required|boolean',
            'notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $violation->resolveDispute(auth()->user(), $request->accepted, $request->notes);

            return response()->json([
                'success' => true,
                'message' => 'Dispute resolved successfully',
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
     * Escalate a violation
     */
    public function escalate(VendorSLAViolation $violation)
    {
        try {
            $violation->escalate();

            return response()->json([
                'success' => true,
                'message' => 'Violation escalated successfully',
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
     * Mark as resolved
     */
    public function resolve(Request $request, VendorSLAViolation $violation)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $violation->resolve($request->notes);

            return response()->json([
                'success' => true,
                'message' => 'Violation marked as resolved',
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
     * Get pending violations
     */
    public function pending(Request $request)
    {
        $violations = VendorSLAViolation::with(['vendor', 'slaSetting', 'violatable'])
            ->pending()
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $violations,
        ]);
    }

    /**
     * Get disputed violations
     */
    public function disputed(Request $request)
    {
        $violations = VendorSLAViolation::with(['vendor', 'slaSetting', 'violatable'])
            ->disputed()
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $violations,
        ]);
    }

    /**
     * Get critical violations
     */
    public function critical(Request $request)
    {
        $violations = VendorSLAViolation::with(['vendor', 'slaSetting', 'violatable'])
            ->critical()
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $violations,
        ]);
    }
}
