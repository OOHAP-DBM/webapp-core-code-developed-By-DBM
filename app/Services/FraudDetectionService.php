<?php

namespace App\Services;

use App\Models\FraudAlert;
use App\Models\FraudEvent;
use App\Models\RiskProfile;
use App\Models\User;
use App\Models\Booking;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FraudDetectionService
{
    /**
     * Risk thresholds configuration
     */
    private const RISK_THRESHOLDS = [
        'high_value_booking' => 50000, // ₹50,000+
        'booking_velocity_time_window' => 24, // hours
        'booking_velocity_count' => 3, // bookings
        'payment_failure_threshold' => 5, // failed attempts
        'payment_failure_time_window' => 24, // hours
        'suspicious_amount_spike_percent' => 300, // 3x average
    ];

    /**
     * Run comprehensive fraud checks on a booking
     */
    public function checkBooking(Booking $booking): array
    {
        $alerts = [];
        $user = $booking->customer;

        if (!$user) {
            return $alerts;
        }

        // Ensure risk profile exists
        $riskProfile = $this->getOrCreateRiskProfile($user);

        // Run all fraud checks
        $alerts = array_merge($alerts, $this->checkHighValueFrequency($user, $booking));
        $alerts = array_merge($alerts, $this->checkGSTMismatch($user, $booking));
        $alerts = array_merge($alerts, $this->checkPaymentFailures($user));
        $alerts = array_merge($alerts, $this->checkSuspiciousPatterns($user, $booking));
        $alerts = array_merge($alerts, $this->checkVelocityAnomaly($user, $booking));
        $alerts = array_merge($alerts, $this->checkAmountSpike($user, $booking));

        // Log the event
        $this->logEvent([
            'event_type' => 'booking_fraud_check',
            'event_category' => 'booking',
            'user_id' => $user->id,
            'eventable' => $booking,
            'event_data' => [
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'alerts_triggered' => count($alerts),
                'risk_profile_score' => $riskProfile->overall_risk_score,
            ],
            'is_suspicious' => count($alerts) > 0,
            'risk_score' => $this->calculateEventRiskScore($alerts),
        ]);

        // Update risk profile
        $riskProfile->recalculateRiskScore();

        return $alerts;
    }

    /**
     * Check for multiple high-value bookings in short time
     */
    private function checkHighValueFrequency(User $user, Booking $booking): array
    {
        $alerts = [];
        $threshold = self::RISK_THRESHOLDS['high_value_booking'];
        $timeWindow = self::RISK_THRESHOLDS['booking_velocity_time_window'];
        $countThreshold = self::RISK_THRESHOLDS['booking_velocity_count'];

        // Check if current booking is high value
        if ($booking->total_amount >= $threshold) {
            // Count recent high-value bookings
            $recentHighValueCount = Booking::where('customer_id', $user->id)
                ->where('total_amount', '>=', $threshold)
                ->where('created_at', '>=', now()->subHours($timeWindow))
                ->count();

            if ($recentHighValueCount >= $countThreshold) {
                $totalAmount = Booking::where('customer_id', $user->id)
                    ->where('total_amount', '>=', $threshold)
                    ->where('created_at', '>=', now()->subHours($timeWindow))
                    ->sum('total_amount');

                $alert = $this->createAlert([
                    'alert_type' => 'high_value_frequency',
                    'severity' => 'high',
                    'alertable' => $booking,
                    'user_id' => $user->id,
                    'user_type' => 'customer',
                    'user_email' => $user->email,
                    'user_phone' => $user->phone ?? null,
                    'description' => "User created {$recentHighValueCount} high-value bookings (₹{$threshold}+) in {$timeWindow} hours totaling ₹" . number_format($totalAmount, 2),
                    'metadata' => [
                        'booking_count' => $recentHighValueCount,
                        'total_amount' => $totalAmount,
                        'time_window_hours' => $timeWindow,
                        'threshold' => $threshold,
                    ],
                    'risk_score' => 75,
                    'confidence_level' => 85,
                ]);

                $alerts[] = $alert;

                // Auto-block if critical
                if ($recentHighValueCount > 5) {
                    $this->autoBlockUser($user, $alert);
                }
            }
        }

        return $alerts;
    }

    /**
     * Check for GST number mismatches
     */
    private function checkGSTMismatch(User $user, Booking $booking): array
    {
        $alerts = [];

        // Check if user has GST number
        if (!empty($user->gst_number)) {
            // Get latest GST verification
            $gstVerification = DB::table('gst_verifications')
                ->where('user_id', $user->id)
                ->where('gst_number', $user->gst_number)
                ->latest()
                ->first();

            if ($gstVerification && $gstVerification->status === 'mismatch') {
                $alert = $this->createAlert([
                    'alert_type' => 'gst_mismatch',
                    'severity' => 'medium',
                    'alertable' => $booking,
                    'user_id' => $user->id,
                    'user_type' => 'customer',
                    'user_email' => $user->email,
                    'user_phone' => $user->phone ?? null,
                    'description' => 'GST number verification failed - registered name or address does not match user profile',
                    'metadata' => [
                        'gst_number' => $user->gst_number,
                        'name_mismatch' => $gstVerification->name_mismatch,
                        'address_mismatch' => $gstVerification->address_mismatch,
                        'mismatch_details' => $gstVerification->mismatch_details,
                    ],
                    'risk_score' => 60,
                    'confidence_level' => 90,
                ]);

                $alerts[] = $alert;
            }

            // Check for multiple different GST numbers
            $gstCount = DB::table('gst_verifications')
                ->where('user_id', $user->id)
                ->distinct('gst_number')
                ->count();

            if ($gstCount > 2) {
                $alert = $this->createAlert([
                    'alert_type' => 'multiple_gst_numbers',
                    'severity' => 'medium',
                    'alertable' => $booking,
                    'user_id' => $user->id,
                    'user_type' => 'customer',
                    'user_email' => $user->email,
                    'user_phone' => $user->phone ?? null,
                    'description' => "User has attempted verification with {$gstCount} different GST numbers",
                    'metadata' => [
                        'gst_count' => $gstCount,
                    ],
                    'risk_score' => 55,
                    'confidence_level' => 80,
                ]);

                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    /**
     * Check for repeated failed payment attempts
     */
    private function checkPaymentFailures(User $user): array
    {
        $alerts = [];
        $threshold = self::RISK_THRESHOLDS['payment_failure_threshold'];
        $timeWindow = self::RISK_THRESHOLDS['payment_failure_time_window'];

        // Count recent failed payments
        $failedCount = DB::table('payment_transactions')
            ->where('user_id', $user->id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subHours($timeWindow))
            ->count();

        if ($failedCount >= $threshold) {
            // Get anomaly details
            $failedAmounts = DB::table('payment_transactions')
                ->where('user_id', $user->id)
                ->where('status', 'failed')
                ->where('created_at', '>=', now()->subHours($timeWindow))
                ->pluck('amount');

            // Log payment anomaly
            DB::table('payment_anomalies')->insert([
                'user_id' => $user->id,
                'anomaly_type' => 'repeated_failure',
                'amount' => $failedAmounts->sum(),
                'status' => 'failed',
                'failure_count_24h' => $failedCount,
                'context' => json_encode([
                    'failed_amounts' => $failedAmounts->toArray(),
                ]),
                'flagged_for_review' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $alert = $this->createAlert([
                'alert_type' => 'repeated_payment_failures',
                'severity' => $failedCount > 10 ? 'high' : 'medium',
                'alertable' => $user,
                'user_id' => $user->id,
                'user_type' => 'customer',
                'user_email' => $user->email,
                'user_phone' => $user->phone ?? null,
                'description' => "User experienced {$failedCount} failed payment attempts in {$timeWindow} hours",
                'metadata' => [
                    'failure_count' => $failedCount,
                    'time_window_hours' => $timeWindow,
                    'total_attempted_amount' => $failedAmounts->sum(),
                ],
                'risk_score' => min(90, 50 + ($failedCount * 5)),
                'confidence_level' => 95,
            ]);

            $alerts[] = $alert;

            // Flag for manual review if excessive
            if ($failedCount > 10) {
                $riskProfile = $this->getOrCreateRiskProfile($user);
                $riskProfile->update(['requires_manual_review' => true]);
            }
        }

        return $alerts;
    }

    /**
     * Check for suspicious booking patterns
     */
    private function checkSuspiciousPatterns(User $user, Booking $booking): array
    {
        $alerts = [];

        // Pattern 1: New account with high-value booking
        $accountAgeDays = $user->created_at->diffInDays(now());
        if ($accountAgeDays < 7 && $booking->total_amount > 30000) {
            $alert = $this->createAlert([
                'alert_type' => 'new_account_high_value',
                'severity' => 'medium',
                'alertable' => $booking,
                'user_id' => $user->id,
                'user_type' => 'customer',
                'user_email' => $user->email,
                'user_phone' => $user->phone ?? null,
                'description' => "New account ({$accountAgeDays} days old) attempting high-value booking of ₹" . number_format($booking->total_amount, 2),
                'metadata' => [
                    'account_age_days' => $accountAgeDays,
                    'booking_amount' => $booking->total_amount,
                ],
                'risk_score' => 65,
                'confidence_level' => 75,
            ]);

            $alerts[] = $alert;
        }

        // Pattern 2: Unverified user with large booking
        if (!$user->email_verified_at && $booking->total_amount > 20000) {
            $alert = $this->createAlert([
                'alert_type' => 'unverified_high_value',
                'severity' => 'medium',
                'alertable' => $booking,
                'user_id' => $user->id,
                'user_type' => 'customer',
                'user_email' => $user->email,
                'user_phone' => $user->phone ?? null,
                'description' => 'Unverified email account attempting high-value booking of ₹' . number_format($booking->total_amount, 2),
                'metadata' => [
                    'booking_amount' => $booking->total_amount,
                    'email_verified' => false,
                ],
                'risk_score' => 70,
                'confidence_level' => 85,
            ]);

            $alerts[] = $alert;
        }

        // Pattern 3: Same-day registration and booking
        if ($user->created_at->isToday() && $booking->total_amount > 15000) {
            $alert = $this->createAlert([
                'alert_type' => 'same_day_registration_booking',
                'severity' => 'high',
                'alertable' => $booking,
                'user_id' => $user->id,
                'user_type' => 'customer',
                'user_email' => $user->email,
                'user_phone' => $user->phone ?? null,
                'description' => 'User registered and created high-value booking on same day (₹' . number_format($booking->total_amount, 2) . ')',
                'metadata' => [
                    'registration_time' => $user->created_at->toDateTimeString(),
                    'booking_time' => $booking->created_at->toDateTimeString(),
                    'booking_amount' => $booking->total_amount,
                ],
                'risk_score' => 80,
                'confidence_level' => 90,
            ]);

            $alerts[] = $alert;
        }

        return $alerts;
    }

    /**
     * Check booking velocity (too many bookings too fast)
     */
    private function checkVelocityAnomaly(User $user, Booking $booking): array
    {
        $alerts = [];

        // Count bookings in last 1 hour
        $bookingsLastHour = Booking::where('customer_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($bookingsLastHour > 3) {
            $alert = $this->createAlert([
                'alert_type' => 'booking_velocity_anomaly',
                'severity' => 'high',
                'alertable' => $booking,
                'user_id' => $user->id,
                'user_type' => 'customer',
                'user_email' => $user->email,
                'user_phone' => $user->phone ?? null,
                'description' => "User created {$bookingsLastHour} bookings in the last hour",
                'metadata' => [
                    'bookings_count' => $bookingsLastHour,
                    'time_window' => '1 hour',
                ],
                'risk_score' => 85,
                'confidence_level' => 95,
            ]);

            $alerts[] = $alert;
        }

        // Count bookings in last 24 hours
        $bookingsLast24h = Booking::where('customer_id', $user->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($bookingsLast24h > 10) {
            $alert = $this->createAlert([
                'alert_type' => 'excessive_booking_frequency',
                'severity' => 'critical',
                'alertable' => $booking,
                'user_id' => $user->id,
                'user_type' => 'customer',
                'user_email' => $user->email,
                'user_phone' => $user->phone ?? null,
                'description' => "User created {$bookingsLast24h} bookings in the last 24 hours",
                'metadata' => [
                    'bookings_count' => $bookingsLast24h,
                    'time_window' => '24 hours',
                ],
                'risk_score' => 95,
                'confidence_level' => 98,
            ]);

            $alerts[] = $alert;

            // Auto-block
            $this->autoBlockUser($user, $alert);
        }

        return $alerts;
    }

    /**
     * Check for unusual amount spikes
     */
    private function checkAmountSpike(User $user, Booking $booking): array
    {
        $alerts = [];
        $riskProfile = $this->getOrCreateRiskProfile($user);

        // Only check if user has booking history
        if ($riskProfile->total_bookings > 0 && $riskProfile->average_booking_value > 0) {
            $averageBooking = $riskProfile->average_booking_value;
            $currentAmount = $booking->total_amount;

            // Calculate deviation percentage
            $deviationPercent = (($currentAmount - $averageBooking) / $averageBooking) * 100;

            if ($deviationPercent > self::RISK_THRESHOLDS['suspicious_amount_spike_percent']) {
                $alert = $this->createAlert([
                    'alert_type' => 'amount_spike_anomaly',
                    'severity' => 'medium',
                    'alertable' => $booking,
                    'user_id' => $user->id,
                    'user_type' => 'customer',
                    'user_email' => $user->email,
                    'user_phone' => $user->phone ?? null,
                    'description' => "Booking amount (₹{$currentAmount}) is " . round($deviationPercent) . "% higher than user's average (₹{$averageBooking})",
                    'metadata' => [
                        'current_amount' => $currentAmount,
                        'average_amount' => $averageBooking,
                        'deviation_percent' => round($deviationPercent, 2),
                    ],
                    'risk_score' => min(90, 50 + ($deviationPercent / 10)),
                    'confidence_level' => 80,
                ]);

                $alerts[] = $alert;

                // Log anomaly
                DB::table('payment_anomalies')->insert([
                    'user_id' => $user->id,
                    'anomaly_type' => 'amount_spike',
                    'amount' => $currentAmount,
                    'status' => 'pending',
                    'amount_deviation_percent' => $deviationPercent,
                    'context' => json_encode([
                        'average_booking' => $averageBooking,
                        'current_booking' => $currentAmount,
                    ]),
                    'flagged_for_review' => $deviationPercent > 500,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $alerts;
    }

    /**
     * Create fraud alert
     */
    private function createAlert(array $data): FraudAlert
    {
        $alert = FraudAlert::create($data);

        // Increment fraud alert count in risk profile
        $riskProfile = $this->getOrCreateRiskProfile(User::find($data['user_id']));
        $riskProfile->increment('fraud_alerts_count');

        // Log critical alerts
        if ($alert->isCritical()) {
            Log::warning('Critical fraud alert created', [
                'alert_id' => $alert->id,
                'alert_type' => $alert->alert_type,
                'user_id' => $alert->user_id,
                'risk_score' => $alert->risk_score,
            ]);
        }

        return $alert;
    }

    /**
     * Log fraud event
     */
    public function logEvent(array $data): FraudEvent
    {
        // Add IP and user agent if not provided
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = request()->ip();
        }
        if (!isset($data['user_agent'])) {
            $data['user_agent'] = request()->userAgent();
        }
        if (!isset($data['session_id'])) {
            $data['session_id'] = session()->getId();
        }

        return FraudEvent::create($data);
    }

    /**
     * Get or create risk profile for user
     */
    public function getOrCreateRiskProfile(User $user): RiskProfile
    {
        return RiskProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'overall_risk_score' => 0,
                'risk_level' => 'low',
                'account_age_days' => $user->created_at->diffInDays(now()),
                'email_verified' => !is_null($user->email_verified_at),
                'phone_verified' => !is_null($user->phone_verified_at ?? null),
            ]
        );
    }

    /**
     * Update risk profile with booking data
     */
    public function updateRiskProfileFromBooking(User $user, Booking $booking): void
    {
        $riskProfile = $this->getOrCreateRiskProfile($user);

        // Update statistics
        $riskProfile->increment('total_bookings');
        
        if ($booking->status === 'cancelled') {
            $riskProfile->increment('cancelled_bookings');
        }

        // Update financial metrics
        $totalSpent = Booking::where('customer_id', $user->id)
            ->where('status', 'confirmed')
            ->sum('total_amount');

        $avgBookingValue = Booking::where('customer_id', $user->id)
            ->avg('total_amount');

        $highestBooking = Booking::where('customer_id', $user->id)
            ->max('total_amount');

        $riskProfile->update([
            'total_spent' => $totalSpent,
            'average_booking_value' => $avgBookingValue ?? 0,
            'highest_booking_value' => $highestBooking ?? 0,
            'last_booking_at' => now(),
        ]);

        if (!$riskProfile->first_booking_at) {
            $riskProfile->update(['first_booking_at' => now()]);
        }

        // Recalculate risk score
        $riskProfile->recalculateRiskScore();
    }

    /**
     * Auto-block user for critical fraud
     */
    private function autoBlockUser(User $user, FraudAlert $alert): void
    {
        $riskProfile = $this->getOrCreateRiskProfile($user);
        $riskProfile->blockUser("Auto-blocked due to {$alert->alert_type}");

        $alert->update([
            'user_blocked' => true,
            'automatic_block' => true,
            'action_taken' => 'User automatically blocked',
        ]);

        Log::critical('User auto-blocked for fraud', [
            'user_id' => $user->id,
            'alert_id' => $alert->id,
            'alert_type' => $alert->alert_type,
        ]);
    }

    /**
     * Calculate overall risk score for an event
     */
    private function calculateEventRiskScore(array $alerts): float
    {
        if (empty($alerts)) {
            return 0;
        }

        // Use highest alert risk score
        return collect($alerts)->max('risk_score') ?? 0;
    }

    /**
     * Verify GST number (mock - integrate with actual GST API)
     */
    public function verifyGST(User $user, string $gstNumber): array
    {
        // Mock GST verification - replace with actual API integration
        // Example: GSTIN API, MasterIndia, etc.
        
        $verification = [
            'status' => 'pending',
            'registered_name' => null,
            'registered_address' => null,
            'registered_state' => null,
            'name_mismatch' => false,
            'address_mismatch' => false,
            'mismatch_details' => null,
        ];

        // Store verification attempt
        DB::table('gst_verifications')->insert([
            'user_id' => $user->id,
            'gst_number' => $gstNumber,
            'status' => $verification['status'],
            'user_provided_name' => $user->name,
            'user_provided_address' => $user->address ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $verification;
    }
}
