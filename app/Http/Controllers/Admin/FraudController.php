<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraudAlert;
use App\Models\FraudEvent;
use App\Models\RiskProfile;
use App\Models\User;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FraudController extends Controller
{
    public function __construct(
        private FraudDetectionService $fraudService
    ) {}

    /**
     * Display fraud detection dashboard
     */
    public function dashboard(Request $request)
    {
        // Get filter parameters
        $alertType = $request->input('alert_type');
        $severity = $request->input('severity');
        $status = $request->input('status');
        $timeRange = $request->input('time_range', '24h');
        $search = $request->input('search');

        // Build query for alerts
        $alertsQuery = FraudAlert::query();

        if ($alertType) {
            $alertsQuery->where('alert_type', $alertType);
        }
        if ($severity) {
            $alertsQuery->where('severity', $severity);
        }
        if ($status) {
            $alertsQuery->where('status', $status);
        }
        if ($search) {
            $alertsQuery->where(function($q) use ($search) {
                $q->where('user_email', 'like', "%{$search}%")
                  ->orWhere('user_phone', 'like', "%{$search}%")
                  ->orWhere('user_id', $search);
            });
        }

        // Apply time range
        $startDate = match($timeRange) {
            '24h' => now()->subHours(24),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => null,
        };

        if ($startDate) {
            $alertsQuery->where('created_at', '>=', $startDate);
        }

        // Get alerts
        $alerts = $alertsQuery->latest()->paginate(20);

        // Get critical alerts (always shown)
        $criticalAlerts = FraudAlert::where('severity', 'critical')
            ->where('status', 'pending')
            ->latest()
            ->limit(10)
            ->get();

        // Calculate statistics
        $stats = [
            'critical_alerts' => FraudAlert::where('severity', 'critical')
                ->where('status', 'pending')
                ->count(),
            'pending_alerts' => FraudAlert::where('status', 'pending')->count(),
            'blocked_users' => RiskProfile::where('is_blocked', true)->count(),
            'suspicious_events_24h' => FraudEvent::suspicious()
                ->where('created_at', '>=', now()->subHours(24))
                ->count(),
        ];

        // Chart data
        $chartData = [
            'alert_types' => $this->getAlertTypeDistribution(),
            'risk_levels' => $this->getRiskLevelDistribution(),
        ];

        // Recent suspicious events
        $recentEvents = FraudEvent::suspicious()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.fraud.dashboard', compact(
            'alerts',
            'criticalAlerts',
            'stats',
            'chartData',
            'recentEvents'
        ));
    }

    /**
     * Show alert details (API)
     */
    public function showAlert(FraudAlert $alert)
    {
        return response()->json([
            'id' => $alert->id,
            'alert_type' => str_replace('_', ' ', ucwords($alert->alert_type, '_')),
            'severity' => $alert->severity,
            'status' => $alert->status,
            'user_id' => $alert->user_id,
            'user_email' => $alert->user_email,
            'user_phone' => $alert->user_phone,
            'description' => $alert->description,
            'metadata' => $alert->metadata,
            'risk_score' => $alert->risk_score,
            'confidence_level' => $alert->confidence_level,
            'created_at' => $alert->created_at->format('M d, Y H:i:s'),
            'reviewed_at' => $alert->reviewed_at?->format('M d, Y H:i:s'),
            'review_notes' => $alert->review_notes,
        ]);
    }

    /**
     * Resolve alert
     */
    public function resolveAlert(Request $request, FraudAlert $alert)
    {
        $request->validate([
            'resolution' => 'required|in:resolved,false_positive,confirmed_fraud',
        ]);

        $resolution = $request->input('resolution');
        $notes = $request->input('notes');

        $alert->resolve($resolution, auth()->id(), $notes);

        // If confirmed fraud, increment user's fraud count
        if ($resolution === 'confirmed_fraud' && $alert->user_id) {
            $riskProfile = $this->fraudService->getOrCreateRiskProfile(User::find($alert->user_id));
            $riskProfile->increment('confirmed_fraud_count');
            $riskProfile->recalculateRiskScore();
        }

        return response()->json([
            'success' => true,
            'message' => "Alert marked as {$resolution}",
        ]);
    }

    /**
     * Block user
     */
    public function blockUser(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'alert_id' => 'nullable|exists:fraud_alerts,id',
        ]);

        $riskProfile = $this->fraudService->getOrCreateRiskProfile($user);
        $riskProfile->blockUser($request->input('reason'));

        // Update alert if provided
        if ($request->input('alert_id')) {
            FraudAlert::find($request->input('alert_id'))->update([
                'user_blocked' => true,
                'action_taken' => 'User blocked by admin',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User has been blocked successfully',
        ]);
    }

    /**
     * Unblock user
     */
    public function unblockUser(User $user)
    {
        $riskProfile = $this->fraudService->getOrCreateRiskProfile($user);
        $riskProfile->unblockUser();

        return response()->json([
            'success' => true,
            'message' => 'User has been unblocked',
        ]);
    }

    /**
     * Export fraud report
     */
    public function export(Request $request)
    {
        $alerts = FraudAlert::with('user')
            ->when($request->input('status'), fn($q, $status) => $q->where('status', $status))
            ->when($request->input('severity'), fn($q, $severity) => $q->where('severity', $severity))
            ->latest()
            ->get();

        $csv = "Alert ID,Type,Severity,User Email,Risk Score,Status,Created At\n";
        
        foreach ($alerts as $alert) {
            $csv .= implode(',', [
                $alert->id,
                $alert->alert_type,
                $alert->severity,
                $alert->user_email,
                $alert->risk_score,
                $alert->status,
                $alert->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="fraud_report_' . now()->format('Y-m-d') . '.csv"');
    }

    /**
     * Get alert type distribution for chart
     */
    private function getAlertTypeDistribution(): array
    {
        $distribution = FraudAlert::select('alert_type', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('alert_type')
            ->get();

        return [
            'labels' => $distribution->pluck('alert_type')->map(fn($type) => str_replace('_', ' ', ucwords($type, '_')))->toArray(),
            'data' => $distribution->pluck('count')->toArray(),
        ];
    }

    /**
     * Get risk level distribution for chart
     */
    private function getRiskLevelDistribution(): array
    {
        $distribution = FraudAlert::select('severity', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('severity')
            ->get();

        $levels = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($distribution as $item) {
            $levels[$item->severity] = $item->count;
        }

        return [
            'data' => array_values($levels),
        ];
    }

    /**
     * Show user risk profile
     */
    public function userRiskProfile(User $user)
    {
        $riskProfile = $this->fraudService->getOrCreateRiskProfile($user);
        
        $alerts = FraudAlert::where('user_id', $user->id)
            ->latest()
            ->get();

        $events = FraudEvent::where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.fraud.user-profile', compact('user', 'riskProfile', 'alerts', 'events'));
    }
}
