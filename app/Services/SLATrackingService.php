<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\VendorQuote;
use App\Models\User;
use App\Models\VendorSLASetting;
use App\Models\VendorSLAViolation;
use App\Notifications\SLAWarningNotification;
use App\Notifications\SLAViolationNotification;
use App\Notifications\SLACriticalViolationNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SLATrackingService
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Core service for monitoring SLA deadlines, detecting violations,
 * and managing vendor reliability scores.
 */
class SLATrackingService
{
    /**
     * Monitor all pending SLA deadlines
     * Called by scheduled task (hourly)
     */
    public function monitorPendingDeadlines(): array
    {
        $results = [
            'warnings_sent' => 0,
            'violations_detected' => 0,
            'acceptances_checked' => 0,
            'quotes_checked' => 0,
            'errors' => [],
        ];

        try {
            // Check acceptance deadlines
            $acceptanceResults = $this->checkAcceptanceDeadlines();
            $results['warnings_sent'] += $acceptanceResults['warnings_sent'];
            $results['violations_detected'] += $acceptanceResults['violations_detected'];
            $results['acceptances_checked'] = $acceptanceResults['checked'];

            // Check quote submission deadlines
            $quoteResults = $this->checkQuoteDeadlines();
            $results['warnings_sent'] += $quoteResults['warnings_sent'];
            $results['violations_detected'] += $quoteResults['violations_detected'];
            $results['quotes_checked'] = $quoteResults['checked'];

            // Process daily recovery for compliant vendors
            $this->processDailyRecovery();

        } catch (\Exception $e) {
            Log::error('SLA Monitoring Error: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Check acceptance deadlines for pending quote requests
     */
    public function checkAcceptanceDeadlines(): array
    {
        $results = ['checked' => 0, 'warnings_sent' => 0, 'violations_detected' => 0];

        // Get quote requests waiting for vendor acceptance
        $pendingRequests = QuoteRequest::where('status', 'published')
            ->whereNotNull('vendor_notified_at')
            ->whereNull('vendor_accepted_at')
            ->whereNotNull('sla_acceptance_deadline')
            ->where('sla_acceptance_violated', false)
            ->get();

        $results['checked'] = $pendingRequests->count();

        foreach ($pendingRequests as $request) {
            try {
                $deadline = Carbon::parse($request->sla_acceptance_deadline);
                $setting = VendorSLASetting::find($request->sla_setting_id) ?? VendorSLASetting::getDefault();

                // Check if deadline passed
                if (now()->isAfter($deadline)) {
                    $this->recordAcceptanceViolation($request, $setting);
                    $results['violations_detected']++;
                }
                // Check if in warning zone
                elseif ($setting->isInWarningZone($deadline)) {
                    $this->sendAcceptanceWarning($request, $deadline);
                    $results['warnings_sent']++;
                }
            } catch (\Exception $e) {
                Log::error("Error checking acceptance deadline for QuoteRequest {$request->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Check quote submission deadlines
     */
    public function checkQuoteDeadlines(): array
    {
        $results = ['checked' => 0, 'warnings_sent' => 0, 'violations_detected' => 0];

        // Get accepted quote requests waiting for vendor quotes
        $acceptedRequests = QuoteRequest::where('status', 'accepted')
            ->whereNotNull('vendor_accepted_at')
            ->whereNotNull('sla_quote_deadline')
            ->where('sla_quote_violated', false)
            ->whereDoesntHave('vendorQuotes', function ($query) {
                $query->where('status', '!=', 'draft');
            })
            ->get();

        $results['checked'] = $acceptedRequests->count();

        foreach ($acceptedRequests as $request) {
            try {
                $deadline = Carbon::parse($request->sla_quote_deadline);
                $setting = VendorSLASetting::find($request->sla_setting_id) ?? VendorSLASetting::getDefault();

                // Check if deadline passed
                if (now()->isAfter($deadline)) {
                    $this->recordQuoteViolation($request, $setting);
                    $results['violations_detected']++;
                }
                // Check if in warning zone
                elseif ($setting->isInWarningZone($deadline)) {
                    $this->sendQuoteWarning($request, $deadline);
                    $results['warnings_sent']++;
                }
            } catch (\Exception $e) {
                Log::error("Error checking quote deadline for QuoteRequest {$request->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Record acceptance violation
     */
    protected function recordAcceptanceViolation(QuoteRequest $request, VendorSLASetting $setting): void
    {
        $vendor = $request->vendor;
        if (!$vendor) {
            return;
        }

        $deadline = Carbon::parse($request->sla_acceptance_deadline);
        $actualTime = now();
        $delay = $setting->calculateDelay($deadline, $actualTime);

        // Get vendor's violation count
        $violationCount = VendorSLAViolation::forVendor($vendor->id)->thisMonth()->count();

        // Calculate severity and penalty
        $severity = $setting->calculateViolationSeverity($delay['hours'], $violationCount);
        $penaltyPoints = $setting->getPenaltyPoints($severity);

        // Create violation record
        $violation = VendorSLAViolation::create([
            'vendor_id' => $vendor->id,
            'sla_setting_id' => $setting->id,
            'violatable_type' => QuoteRequest::class,
            'violatable_id' => $request->id,
            'violation_type' => VendorSLAViolation::TYPE_ENQUIRY_ACCEPTANCE_LATE,
            'severity' => $severity,
            'deadline' => $deadline,
            'actual_time' => $actualTime,
            'delay_hours' => $delay['hours'],
            'delay_minutes' => $delay['minutes'],
            'expected_hours' => $setting->enquiry_acceptance_hours,
            'grace_period_hours' => $setting->grace_period_hours,
            'penalty_points' => $penaltyPoints,
            'status' => VendorSLAViolation::STATUS_PENDING,
            'violation_context' => [
                'quote_request_id' => $request->id,
                'notified_at' => $request->vendor_notified_at,
                'deadline' => $deadline->toDateTimeString(),
                'actual_time' => $actualTime->toDateTimeString(),
            ],
        ]);

        // Update quote request
        $request->update([
            'sla_acceptance_violated' => true,
        ]);

        // Auto-confirm and apply penalty if configured
        if ($setting->auto_mark_violated) {
            $violation->confirm();
        }

        // Send notifications
        if ($setting->auto_notify_vendor) {
            $this->sendViolationNotification($vendor, $violation);
        }

        if ($setting->auto_notify_admin && $severity === VendorSLAViolation::SEVERITY_CRITICAL) {
            $this->sendCriticalViolationNotification($violation);
        }

        // Auto-escalate critical violations
        if ($setting->auto_escalate_critical && $severity === VendorSLAViolation::SEVERITY_CRITICAL) {
            $violation->escalate();
        }

        Log::info("Acceptance violation recorded for vendor {$vendor->id}, QuoteRequest {$request->id}, severity: {$severity}");
    }

    /**
     * Record quote submission violation
     */
    protected function recordQuoteViolation(QuoteRequest $request, VendorSLASetting $setting): void
    {
        $vendor = $request->vendor;
        if (!$vendor) {
            return;
        }

        $deadline = Carbon::parse($request->sla_quote_deadline);
        $actualTime = now();
        $delay = $setting->calculateDelay($deadline, $actualTime);

        // Get vendor's violation count
        $violationCount = VendorSLAViolation::forVendor($vendor->id)->thisMonth()->count();

        // Calculate severity and penalty
        $severity = $setting->calculateViolationSeverity($delay['hours'], $violationCount);
        $penaltyPoints = $setting->getPenaltyPoints($severity);

        // Create violation record
        $violation = VendorSLAViolation::create([
            'vendor_id' => $vendor->id,
            'sla_setting_id' => $setting->id,
            'violatable_type' => QuoteRequest::class,
            'violatable_id' => $request->id,
            'violation_type' => VendorSLAViolation::TYPE_QUOTE_SUBMISSION_LATE,
            'severity' => $severity,
            'deadline' => $deadline,
            'actual_time' => $actualTime,
            'delay_hours' => $delay['hours'],
            'delay_minutes' => $delay['minutes'],
            'expected_hours' => $setting->quote_submission_hours,
            'grace_period_hours' => $setting->grace_period_hours,
            'penalty_points' => $penaltyPoints,
            'status' => VendorSLAViolation::STATUS_PENDING,
            'violation_context' => [
                'quote_request_id' => $request->id,
                'accepted_at' => $request->vendor_accepted_at,
                'deadline' => $deadline->toDateTimeString(),
                'actual_time' => $actualTime->toDateTimeString(),
            ],
        ]);

        // Update quote request
        $request->update([
            'sla_quote_violated' => true,
        ]);

        // Auto-confirm and apply penalty if configured
        if ($setting->auto_mark_violated) {
            $violation->confirm();
        }

        // Send notifications
        if ($setting->auto_notify_vendor) {
            $this->sendViolationNotification($vendor, $violation);
        }

        if ($setting->auto_notify_admin && $severity === VendorSLAViolation::SEVERITY_CRITICAL) {
            $this->sendCriticalViolationNotification($violation);
        }

        // Auto-escalate critical violations
        if ($setting->auto_escalate_critical && $severity === VendorSLAViolation::SEVERITY_CRITICAL) {
            $violation->escalate();
        }

        Log::info("Quote submission violation recorded for vendor {$vendor->id}, QuoteRequest {$request->id}, severity: {$severity}");
    }

    /**
     * Send acceptance warning
     */
    protected function sendAcceptanceWarning(QuoteRequest $request, Carbon $deadline): void
    {
        $vendor = $request->vendor;
        if (!$vendor) {
            return;
        }

        try {
            $vendor->notify(new SLAWarningNotification([
                'type' => 'acceptance',
                'quote_request_id' => $request->id,
                'deadline' => $deadline,
                'time_remaining' => $deadline->diffForHumans(),
            ]));

            Log::info("Acceptance warning sent to vendor {$vendor->id} for QuoteRequest {$request->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send acceptance warning: " . $e->getMessage());
        }
    }

    /**
     * Send quote warning
     */
    protected function sendQuoteWarning(QuoteRequest $request, Carbon $deadline): void
    {
        $vendor = $request->vendor;
        if (!$vendor) {
            return;
        }

        try {
            $vendor->notify(new SLAWarningNotification([
                'type' => 'quote_submission',
                'quote_request_id' => $request->id,
                'deadline' => $deadline,
                'time_remaining' => $deadline->diffForHumans(),
            ]));

            Log::info("Quote submission warning sent to vendor {$vendor->id} for QuoteRequest {$request->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send quote warning: " . $e->getMessage());
        }
    }

    /**
     * Send violation notification
     */
    protected function sendViolationNotification(User $vendor, VendorSLAViolation $violation): void
    {
        try {
            $vendor->notify(new SLAViolationNotification($violation));
        } catch (\Exception $e) {
            Log::error("Failed to send violation notification: " . $e->getMessage());
        }
    }

    /**
     * Send critical violation notification to admins
     */
    protected function sendCriticalViolationNotification(VendorSLAViolation $violation): void
    {
        try {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new SLACriticalViolationNotification($violation));
            }
        } catch (\Exception $e) {
            Log::error("Failed to send critical violation notification: " . $e->getMessage());
        }
    }

    /**
     * Process daily recovery for compliant vendors
     * Vendors with no violations in recovery period gain points back
     */
    public function processDailyRecovery(): void
    {
        // Get vendors eligible for recovery
        $vendors = User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])
            ->where('reliability_score', '<', 100)
            ->get();

        foreach ($vendors as $vendor) {
            try {
                // Get vendor's SLA setting
                $setting = VendorSLASetting::getForVendor($vendor);

                // Check if vendor has been clean for recovery period
                $lastViolation = VendorSLAViolation::forVendor($vendor->id)
                    ->latest('created_at')
                    ->first();

                if ($lastViolation && $lastViolation->created_at->diffInDays(now()) >= $setting->reliability_recovery_days) {
                    // Apply recovery
                    $newScore = min(100, $vendor->reliability_score + $setting->recovery_rate_per_day);
                    
                    $vendor->update([
                        'reliability_score' => $newScore,
                        'last_recovery_at' => now(),
                        'last_score_update_at' => now(),
                    ]);

                    $vendor->updateReliabilityTier();

                    Log::info("Applied recovery to vendor {$vendor->id}: +{$setting->recovery_rate_per_day} points, new score: {$newScore}");
                }
            } catch (\Exception $e) {
                Log::error("Error processing recovery for vendor {$vendor->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Set SLA deadlines when quote request is published
     */
    public function setSLADeadlinesForQuoteRequest(QuoteRequest $request): void
    {
        $vendor = $request->vendor;
        if (!$vendor) {
            return;
        }

        // Get applicable SLA setting
        $setting = VendorSLASetting::getForVendor($vendor);

        // Calculate acceptance deadline
        $notifiedAt = $request->vendor_notified_at ?? now();
        $acceptanceDeadline = $setting->calculateAcceptanceDeadline($notifiedAt);

        // Update quote request
        $request->update([
            'sla_setting_id' => $setting->id,
            'sla_acceptance_deadline' => $acceptanceDeadline,
            'vendor_notified_at' => $notifiedAt,
        ]);

        Log::info("SLA deadlines set for QuoteRequest {$request->id}, acceptance deadline: {$acceptanceDeadline}");
    }

    /**
     * Handle vendor acceptance of quote request
     */
    public function handleVendorAcceptance(QuoteRequest $request): void
    {
        $vendor = $request->vendor;
        if (!$vendor) {
            return;
        }

        $acceptedAt = now();
        $acceptanceDeadline = $request->sla_acceptance_deadline;

        // Update vendor stats
        $vendor->increment('enquiries_accepted_count');

        // Check if on time
        $onTime = $acceptanceDeadline ? $acceptedAt->lte(Carbon::parse($acceptanceDeadline)) : true;

        if ($onTime) {
            // Update on-time rate
            $this->updateOnTimeAcceptanceRate($vendor);
        }

        // Calculate response time
        if ($request->vendor_notified_at) {
            $responseHours = Carbon::parse($request->vendor_notified_at)->diffInHours($acceptedAt);
            $this->updateAverageAcceptanceTime($vendor, $responseHours);
        }

        // Calculate quote submission deadline
        $setting = VendorSLASetting::find($request->sla_setting_id) ?? VendorSLASetting::getForVendor($vendor);
        $quoteDeadline = $setting->calculateQuoteDeadline($acceptedAt);

        // Update quote request
        $request->update([
            'vendor_accepted_at' => $acceptedAt,
            'sla_quote_deadline' => $quoteDeadline,
        ]);

        Log::info("Vendor {$vendor->id} accepted QuoteRequest {$request->id}, quote deadline: {$quoteDeadline}");
    }

    /**
     * Handle vendor quote submission
     */
    public function handleQuoteSubmission(VendorQuote $quote): void
    {
        $vendor = $quote->vendor;
        $request = $quote->quoteRequest;

        if (!$vendor || !$request) {
            return;
        }

        $submittedAt = $quote->sent_at ?? now();
        $quoteDeadline = $request->sla_quote_deadline;

        // Update vendor stats
        $vendor->increment('quotes_submitted_count');

        // Check if on time
        $onTime = $quoteDeadline ? $submittedAt->lte(Carbon::parse($quoteDeadline)) : true;

        if ($onTime) {
            // Update on-time rate
            $this->updateOnTimeQuoteRate($vendor);
        }

        // Calculate submission time
        if ($request->vendor_accepted_at) {
            $submissionHours = Carbon::parse($request->vendor_accepted_at)->diffInHours($submittedAt);
            $this->updateAverageQuoteTime($vendor, $submissionHours);
        }

        // Set SLA deadline on quote
        $setting = VendorSLASetting::find($request->sla_setting_id) ?? VendorSLASetting::getForVendor($vendor);
        
        $quote->update([
            'sla_setting_id' => $setting->id,
            'sla_submission_deadline' => $quoteDeadline,
            'sla_violated' => !$onTime,
            'sla_violation_time' => !$onTime ? $submittedAt : null,
        ]);

        Log::info("Vendor {$vendor->id} submitted quote {$quote->id}, on time: " . ($onTime ? 'yes' : 'no'));
    }

    /**
     * Update vendor on-time acceptance rate
     */
    protected function updateOnTimeAcceptanceRate(User $vendor): void
    {
        $total = $vendor->enquiries_accepted_count;
        $onTime = QuoteRequest::where('vendor_id', $vendor->id)
            ->whereNotNull('vendor_accepted_at')
            ->whereNotNull('sla_acceptance_deadline')
            ->whereRaw('vendor_accepted_at <= sla_acceptance_deadline')
            ->count();

        $rate = $total > 0 ? ($onTime / $total) * 100 : 100;

        $vendor->update(['on_time_acceptance_rate' => round($rate, 2)]);
    }

    /**
     * Update vendor on-time quote rate
     */
    protected function updateOnTimeQuoteRate(User $vendor): void
    {
        $total = $vendor->quotes_submitted_count;
        $onTime = VendorQuote::where('vendor_id', $vendor->id)
            ->where('sla_violated', false)
            ->count();

        $rate = $total > 0 ? ($onTime / $total) * 100 : 100;

        $vendor->update(['on_time_quote_rate' => round($rate, 2)]);
    }

    /**
     * Update vendor average acceptance time
     */
    protected function updateAverageAcceptanceTime(User $vendor, float $newTime): void
    {
        $current = $vendor->avg_acceptance_time_hours ?? 0;
        $count = $vendor->enquiries_accepted_count;

        $avg = (($current * ($count - 1)) + $newTime) / $count;

        $vendor->update(['avg_acceptance_time_hours' => round($avg, 2)]);
    }

    /**
     * Update vendor average quote time
     */
    protected function updateAverageQuoteTime(User $vendor, float $newTime): void
    {
        $current = $vendor->avg_quote_time_hours ?? 0;
        $count = $vendor->quotes_submitted_count;

        $avg = (($current * ($count - 1)) + $newTime) / $count;

        $vendor->update(['avg_quote_time_hours' => round($avg, 2)]);
    }

    /**
     * Reset monthly violation counts
     * Called on 1st of each month
     */
    public function resetMonthlyViolationCounts(): void
    {
        User::whereIn('role', ['vendor', 'premium_vendor', 'verified_vendor'])
            ->update(['sla_violations_this_month' => 0]);

        Log::info("Monthly violation counts reset");
    }
}
