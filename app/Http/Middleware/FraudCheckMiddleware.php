<?php

namespace App\Http\Middleware;

use App\Models\RiskProfile;
use App\Services\FraudDetectionService;
use App\Services\FraudEventLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FraudCheckMiddleware
{
    public function __construct(
        private FraudDetectionService $fraudService,
        private FraudEventLogger $eventLogger
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$checks): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Get or create risk profile
        $riskProfile = $this->fraudService->getOrCreateRiskProfile($user);

        // Check if user is blocked
        if ($riskProfile->is_blocked) {
            $this->eventLogger->logSuspiciousActivity($user, 'blocked_user_attempt', [
                'action' => $request->path(),
                'method' => $request->method(),
                'risk_score' => 100,
                'block_reason' => $riskProfile->block_reason,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been blocked due to suspicious activity. Please contact support.',
                    'error_code' => 'ACCOUNT_BLOCKED',
                ], 403);
            }

            return redirect()->route('account.blocked')
                ->with('error', 'Your account has been blocked. Please contact support for assistance.');
        }

        // Check if user requires manual review for high-value transactions
        if ($riskProfile->requires_manual_review && in_array('high_value', $checks)) {
            // Log the attempt
            $this->eventLogger->logSuspiciousActivity($user, 'manual_review_required', [
                'action' => $request->path(),
                'risk_score' => $riskProfile->overall_risk_score,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This transaction requires manual review. Our team will contact you shortly.',
                    'error_code' => 'MANUAL_REVIEW_REQUIRED',
                ], 403);
            }

            return redirect()->back()
                ->with('warning', 'Your account is under review. High-value transactions require approval.');
        }

        // Log high-risk user activity
        if ($riskProfile->isHighRisk()) {
            $this->eventLogger->logEvent([
                'event_type' => 'high_risk_user_activity',
                'event_category' => 'general',
                'user_id' => $user->id,
                'eventable' => $user,
                'event_data' => [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'risk_level' => $riskProfile->risk_level,
                ],
                'is_suspicious' => true,
                'risk_score' => $riskProfile->overall_risk_score,
            ]);
        }

        return $next($request);
    }
}
