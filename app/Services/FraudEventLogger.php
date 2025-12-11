<?php

namespace App\Services;

use App\Models\FraudEvent;
use Illuminate\Support\Facades\Log;

class FraudEventLogger
{
    /**
     * Log a booking attempt
     */
    public function logBookingAttempt($user, $booking, array $additionalData = []): FraudEvent
    {
        return $this->log([
            'event_type' => 'booking_attempt',
            'event_category' => 'booking',
            'user_id' => $user->id,
            'eventable' => $booking,
            'event_data' => array_merge([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'quotation_id' => $booking->quotation_id ?? null,
                'start_date' => $booking->start_date ?? null,
                'end_date' => $booking->end_date ?? null,
            ], $additionalData),
        ]);
    }

    /**
     * Log a payment attempt
     */
    public function logPaymentAttempt($user, $transaction, bool $isSuccess, array $additionalData = []): FraudEvent
    {
        return $this->log([
            'event_type' => $isSuccess ? 'payment_success' : 'payment_failure',
            'event_category' => 'payment',
            'user_id' => $user->id,
            'eventable' => $transaction,
            'event_data' => array_merge([
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'payment_method' => $transaction->payment_method ?? null,
                'status' => $transaction->status,
                'gateway_response' => $transaction->gateway_response ?? null,
            ], $additionalData),
            'is_suspicious' => !$isSuccess,
        ]);
    }

    /**
     * Log profile update
     */
    public function logProfileUpdate($user, array $changes, array $additionalData = []): FraudEvent
    {
        return $this->log([
            'event_type' => 'profile_update',
            'event_category' => 'profile',
            'user_id' => $user->id,
            'eventable' => $user,
            'event_data' => array_merge([
                'changed_fields' => array_keys($changes),
                'changes' => $changes,
            ], $additionalData),
        ]);
    }

    /**
     * Log authentication event
     */
    public function logAuthentication($user, string $eventType, bool $isSuccess, array $additionalData = []): FraudEvent
    {
        return $this->log([
            'event_type' => $eventType, // login_attempt, login_success, login_failure, etc.
            'event_category' => 'authentication',
            'user_id' => $user->id ?? null,
            'eventable' => $user,
            'event_data' => array_merge([
                'success' => $isSuccess,
                'timestamp' => now()->toDateTimeString(),
            ], $additionalData),
            'is_suspicious' => !$isSuccess,
        ]);
    }

    /**
     * Log quotation submission
     */
    public function logQuotationSubmission($user, $quotation, array $additionalData = []): FraudEvent
    {
        return $this->log([
            'event_type' => 'quotation_submission',
            'event_category' => 'quotation',
            'user_id' => $user->id,
            'eventable' => $quotation,
            'event_data' => array_merge([
                'quotation_id' => $quotation->id,
                'grand_total' => $quotation->grand_total,
                'payment_mode' => $quotation->payment_mode ?? null,
                'has_milestones' => $quotation->milestones()->count() > 0,
            ], $additionalData),
        ]);
    }

    /**
     * Log GST verification attempt
     */
    public function logGSTVerification($user, string $gstNumber, string $status, array $additionalData = []): FraudEvent
    {
        return $this->log([
            'event_type' => 'gst_verification',
            'event_category' => 'verification',
            'user_id' => $user->id,
            'eventable' => $user,
            'event_data' => array_merge([
                'gst_number' => $gstNumber,
                'status' => $status,
                'timestamp' => now()->toDateTimeString(),
            ], $additionalData),
            'is_suspicious' => $status === 'mismatch',
        ]);
    }

    /**
     * Log cancellation event
     */
    public function logCancellation($user, $booking, array $additionalData = []): FraudEvent
    {
        return $this->log([
            'event_type' => 'booking_cancellation',
            'event_category' => 'booking',
            'user_id' => $user->id,
            'eventable' => $booking,
            'event_data' => array_merge([
                'booking_id' => $booking->id,
                'original_amount' => $booking->total_amount,
                'refund_amount' => $booking->refund_amount ?? null,
                'cancellation_reason' => $booking->cancellation_reason ?? null,
            ], $additionalData),
        ]);
    }

    /**
     * Log refund request
     */
    public function logRefundRequest($user, $booking, float $refundAmount, array $additionalData = []): FraudEvent
    {
        return $this->log([
            'event_type' => 'refund_request',
            'event_category' => 'payment',
            'user_id' => $user->id,
            'eventable' => $booking,
            'event_data' => array_merge([
                'booking_id' => $booking->id,
                'refund_amount' => $refundAmount,
                'original_amount' => $booking->total_amount,
                'timestamp' => now()->toDateTimeString(),
            ], $additionalData),
        ]);
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity($user, string $activityType, array $details = []): FraudEvent
    {
        Log::warning('Suspicious activity detected', [
            'user_id' => $user->id ?? null,
            'activity_type' => $activityType,
            'details' => $details,
        ]);

        return $this->log([
            'event_type' => $activityType,
            'event_category' => 'suspicious',
            'user_id' => $user->id ?? null,
            'eventable' => $user,
            'event_data' => $details,
            'is_suspicious' => true,
            'risk_score' => $details['risk_score'] ?? 75,
        ]);
    }

    /**
     * Base log method
     */
    private function log(array $data): FraudEvent
    {
        // Add request context if not provided
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = request()->ip();
        }
        if (!isset($data['user_agent'])) {
            $data['user_agent'] = request()->userAgent();
        }
        if (!isset($data['session_id'])) {
            $data['session_id'] = session()->getId();
        }

        // Calculate risk score if not provided
        if (!isset($data['risk_score'])) {
            $data['risk_score'] = $data['is_suspicious'] ?? false ? 50 : 0;
        }

        // Create event
        $event = FraudEvent::create($data);

        // Log high-risk events
        if ($event->risk_score >= 70 || $event->is_suspicious) {
            Log::info('Fraud event logged', [
                'event_id' => $event->id,
                'event_type' => $event->event_type,
                'user_id' => $event->user_id,
                'risk_score' => $event->risk_score,
                'is_suspicious' => $event->is_suspicious,
            ]);
        }

        return $event;
    }

    /**
     * Get recent events for a user
     */
    public function getUserEvents(int $userId, int $hours = 24): \Illuminate\Support\Collection
    {
        return FraudEvent::where('user_id', $userId)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get suspicious events
     */
    public function getSuspiciousEvents(int $hours = 24): \Illuminate\Support\Collection
    {
        return FraudEvent::suspicious()
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('risk_score', 'desc')
            ->get();
    }

    /**
     * Get events by category
     */
    public function getEventsByCategory(string $category, int $limit = 100): \Illuminate\Support\Collection
    {
        return FraudEvent::byCategory($category)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get payment failure events for user
     */
    public function getPaymentFailures(int $userId, int $hours = 24): \Illuminate\Support\Collection
    {
        return FraudEvent::where('user_id', $userId)
            ->where('event_type', 'payment_failure')
            ->where('created_at', '>=', now()->subHours($hours))
            ->get();
    }

    /**
     * Check if IP address has suspicious activity
     */
    public function checkIPActivity(string $ipAddress, int $hours = 24): array
    {
        $events = FraudEvent::where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subHours($hours))
            ->get();

        $suspiciousCount = $events->where('is_suspicious', true)->count();
        $totalEvents = $events->count();

        return [
            'total_events' => $totalEvents,
            'suspicious_count' => $suspiciousCount,
            'suspicion_rate' => $totalEvents > 0 ? ($suspiciousCount / $totalEvents) * 100 : 0,
            'is_high_risk' => $suspiciousCount > 5 || ($totalEvents > 0 && ($suspiciousCount / $totalEvents) > 0.5),
        ];
    }
}
