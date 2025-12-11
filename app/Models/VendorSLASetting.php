<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * VendorSLASetting Model
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Manages SLA (Service Level Agreement) configurations for vendors.
 * Defines timeframes, penalties, and auto-actions for SLA violations.
 */
class VendorSLASetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_default',
        'enquiry_acceptance_hours',
        'quote_submission_hours',
        'quote_revision_hours',
        'enquiry_response_hours',
        'warning_threshold_percentage',
        'grace_period_hours',
        'minor_violation_penalty',
        'major_violation_penalty',
        'critical_violation_penalty',
        'auto_mark_violated',
        'auto_notify_vendor',
        'auto_notify_admin',
        'auto_escalate_critical',
        'max_violations_per_month',
        'critical_violation_threshold',
        'reliability_recovery_days',
        'recovery_rate_per_day',
        'applies_to',
        'count_business_hours_only',
        'business_hours',
        'excluded_days',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'enquiry_acceptance_hours' => 'integer',
        'quote_submission_hours' => 'integer',
        'quote_revision_hours' => 'integer',
        'enquiry_response_hours' => 'integer',
        'warning_threshold_percentage' => 'integer',
        'grace_period_hours' => 'integer',
        'minor_violation_penalty' => 'decimal:2',
        'major_violation_penalty' => 'decimal:2',
        'critical_violation_penalty' => 'decimal:2',
        'auto_mark_violated' => 'boolean',
        'auto_notify_vendor' => 'boolean',
        'auto_notify_admin' => 'boolean',
        'auto_escalate_critical' => 'boolean',
        'max_violations_per_month' => 'integer',
        'critical_violation_threshold' => 'integer',
        'reliability_recovery_days' => 'integer',
        'recovery_rate_per_day' => 'decimal:2',
        'count_business_hours_only' => 'boolean',
        'business_hours' => 'array',
        'excluded_days' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get default SLA setting
     */
    public static function getDefault(): self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->firstOr(function () {
                return static::where('is_active', true)->first();
            });
    }

    /**
     * Get SLA setting for vendor
     */
    public static function getForVendor(User $vendor): self
    {
        // Check if vendor has custom SLA setting
        if ($vendor->vendor_sla_setting_id) {
            $custom = static::find($vendor->vendor_sla_setting_id);
            if ($custom && $custom->is_active) {
                return $custom;
            }
        }

        // Check vendor category-specific settings
        if ($vendor->hasRole('premium_vendor')) {
            $premium = static::where('applies_to', 'premium')
                ->where('is_active', true)
                ->first();
            if ($premium) {
                return $premium;
            }
        }

        if ($vendor->hasRole('verified_vendor')) {
            $verified = static::where('applies_to', 'verified')
                ->where('is_active', true)
                ->first();
            if ($verified) {
                return $verified;
            }
        }

        // Check if new vendor (created within last 30 days)
        if ($vendor->created_at && $vendor->created_at->greaterThan(now()->subDays(30))) {
            $new = static::where('applies_to', 'new')
                ->where('is_active', true)
                ->first();
            if ($new) {
                return $new;
            }
        }

        // Default SLA
        return static::getDefault();
    }

    /**
     * Calculate acceptance deadline from notification time
     */
    public function calculateAcceptanceDeadline(Carbon $notifiedAt): Carbon
    {
        if ($this->count_business_hours_only) {
            return $this->addBusinessHours($notifiedAt, $this->enquiry_acceptance_hours);
        }

        return $notifiedAt->copy()->addHours($this->enquiry_acceptance_hours);
    }

    /**
     * Calculate quote submission deadline from acceptance time
     */
    public function calculateQuoteDeadline(Carbon $acceptedAt): Carbon
    {
        if ($this->count_business_hours_only) {
            return $this->addBusinessHours($acceptedAt, $this->quote_submission_hours);
        }

        return $acceptedAt->copy()->addHours($this->quote_submission_hours);
    }

    /**
     * Calculate total response deadline from notification time
     */
    public function calculateResponseDeadline(Carbon $notifiedAt): Carbon
    {
        if ($this->count_business_hours_only) {
            return $this->addBusinessHours($notifiedAt, $this->enquiry_response_hours);
        }

        return $notifiedAt->copy()->addHours($this->enquiry_response_hours);
    }

    /**
     * Calculate warning time (when to send warning notification)
     */
    public function calculateWarningTime(Carbon $deadline): Carbon
    {
        $totalMinutes = now()->diffInMinutes($deadline);
        $warningMinutes = $totalMinutes * ($this->warning_threshold_percentage / 100);
        
        return now()->addMinutes($warningMinutes);
    }

    /**
     * Check if deadline is in warning zone
     */
    public function isInWarningZone(Carbon $deadline): bool
    {
        $totalMinutes = now()->diffInMinutes($deadline, false);
        if ($totalMinutes <= 0) {
            return false; // Already passed
        }

        $warningMinutes = $totalMinutes * ($this->warning_threshold_percentage / 100);
        
        return $totalMinutes <= $warningMinutes;
    }

    /**
     * Calculate delay hours
     */
    public function calculateDelay(Carbon $deadline, Carbon $actualTime): array
    {
        $diffInMinutes = $deadline->diffInMinutes($actualTime);
        
        return [
            'hours' => floor($diffInMinutes / 60),
            'minutes' => $diffInMinutes % 60,
            'total_minutes' => $diffInMinutes,
        ];
    }

    /**
     * Determine violation severity
     */
    public function calculateViolationSeverity(int $delayHours, int $vendorViolationCount): string
    {
        // Critical if beyond grace period and vendor has multiple violations
        if ($delayHours > $this->grace_period_hours && $vendorViolationCount >= $this->critical_violation_threshold) {
            return 'critical';
        }

        // Major if significantly beyond grace period
        if ($delayHours > ($this->grace_period_hours * 2)) {
            return 'major';
        }

        // Major if beyond grace period
        if ($delayHours > $this->grace_period_hours) {
            return 'major';
        }

        // Minor if within grace period
        return 'minor';
    }

    /**
     * Get penalty points for severity
     */
    public function getPenaltyPoints(string $severity): float
    {
        return match ($severity) {
            'critical' => (float) $this->critical_violation_penalty,
            'major' => (float) $this->major_violation_penalty,
            'minor' => (float) $this->minor_violation_penalty,
            default => 0.0,
        };
    }

    /**
     * Add business hours to timestamp
     */
    protected function addBusinessHours(Carbon $start, int $hours): Carbon
    {
        if (!$this->count_business_hours_only || empty($this->business_hours)) {
            return $start->copy()->addHours($hours);
        }

        $businessHours = $this->business_hours;
        $workingDays = $businessHours['days'] ?? [1, 2, 3, 4, 5]; // Mon-Fri
        $startTime = $businessHours['start'] ?? '09:00';
        $endTime = $businessHours['end'] ?? '18:00';

        $current = $start->copy();
        $remainingHours = $hours;

        while ($remainingHours > 0) {
            // Skip to next working day if not a working day
            while (!in_array($current->dayOfWeekIso, $workingDays) || $this->isExcludedDay($current)) {
                $current->addDay()->setTimeFromTimeString($startTime);
            }

            // Check if current time is outside business hours
            $dayStart = $current->copy()->setTimeFromTimeString($startTime);
            $dayEnd = $current->copy()->setTimeFromTimeString($endTime);

            if ($current->lt($dayStart)) {
                $current = $dayStart->copy();
            } elseif ($current->gte($dayEnd)) {
                $current->addDay()->setTimeFromTimeString($startTime);
                continue;
            }

            // Calculate available hours in current day
            $availableHours = $current->diffInHours($dayEnd, false);
            
            if ($availableHours >= $remainingHours) {
                $current->addHours($remainingHours);
                $remainingHours = 0;
            } else {
                $current->addDay()->setTimeFromTimeString($startTime);
                $remainingHours -= $availableHours;
            }
        }

        return $current;
    }

    /**
     * Check if day is excluded (holiday/weekend)
     */
    protected function isExcludedDay(Carbon $date): bool
    {
        if (empty($this->excluded_days)) {
            return false;
        }

        return in_array($date->format('Y-m-d'), $this->excluded_days);
    }

    /**
     * Relationships
     */
    public function violations(): HasMany
    {
        return $this->hasMany(VendorSLAViolation::class, 'sla_setting_id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(User::class, 'vendor_sla_setting_id');
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class, 'sla_setting_id');
    }

    public function vendorQuotes(): HasMany
    {
        return $this->hasMany(VendorQuote::class, 'sla_setting_id');
    }
}
