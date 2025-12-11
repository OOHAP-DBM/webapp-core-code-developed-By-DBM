<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'overall_risk_score',
        'risk_level',
        'total_bookings',
        'cancelled_bookings',
        'successful_payments',
        'failed_payments',
        'disputed_transactions',
        'total_spent',
        'average_booking_value',
        'highest_booking_value',
        'fraud_alerts_count',
        'confirmed_fraud_count',
        'known_ip_addresses',
        'known_devices',
        'email_verified',
        'phone_verified',
        'gst_verified',
        'identity_verified',
        'account_age_days',
        'first_booking_at',
        'last_booking_at',
        'last_fraud_check_at',
        'is_blocked',
        'requires_manual_review',
        'block_reason',
        'blocked_at',
    ];

    protected $casts = [
        'overall_risk_score' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'average_booking_value' => 'decimal:2',
        'highest_booking_value' => 'decimal:2',
        'known_ip_addresses' => 'array',
        'known_devices' => 'array',
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'gst_verified' => 'boolean',
        'identity_verified' => 'boolean',
        'is_blocked' => 'boolean',
        'requires_manual_review' => 'boolean',
        'first_booking_at' => 'datetime',
        'last_booking_at' => 'datetime',
        'last_fraud_check_at' => 'datetime',
        'blocked_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate cancellation rate
     */
    public function getCancellationRateAttribute(): float
    {
        if ($this->total_bookings === 0) {
            return 0;
        }
        return ($this->cancelled_bookings / $this->total_bookings) * 100;
    }

    /**
     * Calculate payment failure rate
     */
    public function getPaymentFailureRateAttribute(): float
    {
        $totalAttempts = $this->successful_payments + $this->failed_payments;
        if ($totalAttempts === 0) {
            return 0;
        }
        return ($this->failed_payments / $totalAttempts) * 100;
    }

    /**
     * Check if user is high risk
     */
    public function isHighRisk(): bool
    {
        return in_array($this->risk_level, ['high', 'critical']) || $this->overall_risk_score >= 70;
    }

    /**
     * Update risk score based on current metrics
     */
    public function recalculateRiskScore(): void
    {
        $score = 0;

        // Cancellation rate impact (0-20 points)
        $cancellationRate = $this->cancellation_rate;
        if ($cancellationRate > 50) $score += 20;
        elseif ($cancellationRate > 30) $score += 15;
        elseif ($cancellationRate > 15) $score += 10;

        // Payment failure rate impact (0-25 points)
        $failureRate = $this->payment_failure_rate;
        if ($failureRate > 70) $score += 25;
        elseif ($failureRate > 50) $score += 20;
        elseif ($failureRate > 30) $score += 15;
        elseif ($failureRate > 15) $score += 10;

        // Fraud alerts impact (0-30 points)
        if ($this->confirmed_fraud_count > 0) $score += 30;
        elseif ($this->fraud_alerts_count > 5) $score += 25;
        elseif ($this->fraud_alerts_count > 2) $score += 15;
        elseif ($this->fraud_alerts_count > 0) $score += 10;

        // Verification status impact (0-15 points reduction)
        $verificationBonus = 0;
        if ($this->email_verified) $verificationBonus += 3;
        if ($this->phone_verified) $verificationBonus += 3;
        if ($this->gst_verified) $verificationBonus += 5;
        if ($this->identity_verified) $verificationBonus += 4;
        $score -= $verificationBonus;

        // Account age impact (0-10 points reduction for established accounts)
        if ($this->account_age_days > 365) $score -= 10;
        elseif ($this->account_age_days > 180) $score -= 5;
        elseif ($this->account_age_days > 90) $score -= 3;

        // Disputed transactions impact (0-10 points)
        if ($this->disputed_transactions > 3) $score += 10;
        elseif ($this->disputed_transactions > 1) $score += 5;

        // Ensure score is within 0-100 range
        $score = max(0, min(100, $score));

        // Determine risk level
        $riskLevel = 'low';
        if ($score >= 80) $riskLevel = 'critical';
        elseif ($score >= 60) $riskLevel = 'high';
        elseif ($score >= 30) $riskLevel = 'medium';

        $this->update([
            'overall_risk_score' => $score,
            'risk_level' => $riskLevel,
            'last_fraud_check_at' => now(),
        ]);
    }

    /**
     * Block user
     */
    public function blockUser(string $reason): void
    {
        $this->update([
            'is_blocked' => true,
            'block_reason' => $reason,
            'blocked_at' => now(),
        ]);
    }

    /**
     * Unblock user
     */
    public function unblockUser(): void
    {
        $this->update([
            'is_blocked' => false,
            'block_reason' => null,
            'blocked_at' => null,
        ]);
    }
}
