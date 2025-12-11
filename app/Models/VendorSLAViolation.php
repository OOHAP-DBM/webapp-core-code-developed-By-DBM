<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * VendorSLAViolation Model
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Tracks individual SLA violations by vendors and manages their impact
 * on reliability scores.
 */
class VendorSLAViolation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'sla_setting_id',
        'violatable_type',
        'violatable_id',
        'violation_type',
        'severity',
        'deadline',
        'actual_time',
        'delay_hours',
        'delay_minutes',
        'expected_hours',
        'grace_period_hours',
        'penalty_points',
        'reliability_score_before',
        'reliability_score_after',
        'status',
        'confirmed_at',
        'resolved_at',
        'waived_at',
        'waived_by',
        'waiver_reason',
        'vendor_explanation',
        'vendor_responded_at',
        'vendor_dispute_status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
        'auto_notification_sent',
        'notification_sent_at',
        'escalated_to_admin',
        'escalated_at',
        'violation_count_this_month',
        'violation_count_total',
        'business_impact',
        'customer_impact_notes',
        'violation_context',
        'metadata',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'actual_time' => 'datetime',
        'delay_hours' => 'integer',
        'delay_minutes' => 'integer',
        'expected_hours' => 'integer',
        'grace_period_hours' => 'integer',
        'penalty_points' => 'decimal:2',
        'reliability_score_before' => 'decimal:2',
        'reliability_score_after' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'waived_at' => 'datetime',
        'vendor_responded_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'auto_notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
        'escalated_to_admin' => 'boolean',
        'escalated_at' => 'datetime',
        'violation_count_this_month' => 'integer',
        'violation_count_total' => 'integer',
        'business_impact' => 'decimal:2',
        'violation_context' => 'array',
        'metadata' => 'array',
    ];

    // Violation Types
    const TYPE_ENQUIRY_ACCEPTANCE_LATE = 'enquiry_acceptance_late';
    const TYPE_QUOTE_SUBMISSION_LATE = 'quote_submission_late';
    const TYPE_QUOTE_REVISION_LATE = 'quote_revision_late';
    const TYPE_NO_RESPONSE = 'no_response';
    const TYPE_MISSED_DEADLINE = 'missed_deadline';

    // Severity Levels
    const SEVERITY_MINOR = 'minor';
    const SEVERITY_MAJOR = 'major';
    const SEVERITY_CRITICAL = 'critical';

    // Status
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DISPUTED = 'disputed';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_WAIVED = 'waived';
    const STATUS_ESCALATED = 'escalated';

    // Dispute Status
    const DISPUTE_NOT_DISPUTED = 'not_disputed';
    const DISPUTE_DISPUTED = 'disputed';
    const DISPUTE_ACCEPTED = 'accepted';
    const DISPUTE_REJECTED = 'rejected';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($violation) {
            // Set violation counts
            $violation->violation_count_total = self::where('vendor_id', $violation->vendor_id)
                ->count() + 1;
            
            $violation->violation_count_this_month = self::where('vendor_id', $violation->vendor_id)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count() + 1;
        });

        static::created(function ($violation) {
            // Update vendor violation counts
            $vendor = $violation->vendor;
            if ($vendor) {
                $vendor->increment('sla_violations_count');
                $vendor->increment('sla_violations_this_month');
                $vendor->last_sla_violation_at = now();
                $vendor->save();
            }
        });
    }

    /**
     * Confirm violation and apply penalty
     */
    public function confirm(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \Exception('Only pending violations can be confirmed');
        }

        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        // Apply penalty to vendor reliability score
        $this->applyPenalty();
    }

    /**
     * Apply penalty to vendor reliability score
     */
    public function applyPenalty(): void
    {
        $vendor = $this->vendor;
        if (!$vendor) {
            return;
        }

        $this->reliability_score_before = $vendor->reliability_score;
        
        // Deduct penalty points
        $newScore = max(0, $vendor->reliability_score - $this->penalty_points);
        
        $vendor->update([
            'reliability_score' => $newScore,
            'total_penalty_points' => $vendor->total_penalty_points + $this->penalty_points,
            'last_score_update_at' => now(),
        ]);

        // Update reliability tier
        $vendor->updateReliabilityTier();

        $this->update([
            'reliability_score_after' => $newScore,
        ]);
    }

    /**
     * Waive violation (admin action)
     */
    public function waive(User $admin, string $reason): void
    {
        if ($this->status === self::STATUS_WAIVED) {
            throw new \Exception('Violation already waived');
        }

        // If penalty was already applied, reverse it
        if ($this->reliability_score_after !== null && $this->status === self::STATUS_CONFIRMED) {
            $vendor = $this->vendor;
            if ($vendor) {
                $vendor->update([
                    'reliability_score' => min(100, $vendor->reliability_score + $this->penalty_points),
                    'total_penalty_points' => max(0, $vendor->total_penalty_points - $this->penalty_points),
                    'last_score_update_at' => now(),
                ]);
                $vendor->updateReliabilityTier();
            }
        }

        $this->update([
            'status' => self::STATUS_WAIVED,
            'waived_at' => now(),
            'waived_by' => $admin->id,
            'waiver_reason' => $reason,
        ]);
    }

    /**
     * Vendor disputes violation
     */
    public function dispute(string $explanation): void
    {
        if ($this->status === self::STATUS_WAIVED) {
            throw new \Exception('Cannot dispute waived violation');
        }

        $this->update([
            'status' => self::STATUS_DISPUTED,
            'vendor_dispute_status' => self::DISPUTE_DISPUTED,
            'vendor_explanation' => $explanation,
            'vendor_responded_at' => now(),
        ]);
    }

    /**
     * Admin resolves dispute
     */
    public function resolveDispute(User $admin, bool $accepted, string $notes): void
    {
        if ($this->status !== self::STATUS_DISPUTED) {
            throw new \Exception('Violation is not disputed');
        }

        $disputeStatus = $accepted ? self::DISPUTE_ACCEPTED : self::DISPUTE_REJECTED;

        $this->update([
            'status' => $accepted ? self::STATUS_WAIVED : self::STATUS_CONFIRMED,
            'vendor_dispute_status' => $disputeStatus,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);

        // If dispute accepted, waive the violation
        if ($accepted) {
            $this->waive($admin, "Dispute accepted: " . $notes);
        } elseif ($this->status === self::STATUS_CONFIRMED && !$this->confirmed_at) {
            // If dispute rejected and not yet confirmed, apply penalty
            $this->confirm();
        }
    }

    /**
     * Escalate to admin
     */
    public function escalate(): void
    {
        $this->update([
            'status' => self::STATUS_ESCALATED,
            'escalated_to_admin' => true,
            'escalated_at' => now(),
        ]);
    }

    /**
     * Mark as resolved
     */
    public function resolve(string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Get delay in human-readable format
     */
    public function getDelayFormatted(): string
    {
        if ($this->delay_hours == 0) {
            return "{$this->delay_minutes} minutes";
        }

        if ($this->delay_minutes == 0) {
            return "{$this->delay_hours} hours";
        }

        return "{$this->delay_hours} hours {$this->delay_minutes} minutes";
    }

    /**
     * Check if violation is within grace period
     */
    public function isWithinGracePeriod(): bool
    {
        return $this->delay_hours <= $this->grace_period_hours;
    }

    /**
     * Check if critical
     */
    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    /**
     * Get severity color
     */
    public function getSeverityColor(): string
    {
        return match ($this->severity) {
            self::SEVERITY_CRITICAL => 'danger',
            self::SEVERITY_MAJOR => 'warning',
            self::SEVERITY_MINOR => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'danger',
            self::STATUS_DISPUTED => 'info',
            self::STATUS_RESOLVED => 'success',
            self::STATUS_WAIVED => 'secondary',
            self::STATUS_ESCALATED => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Scope: Recent violations
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Scope: This month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }

    /**
     * Scope: By severity
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: By vendor
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope: Pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Disputed
     */
    public function scopeDisputed($query)
    {
        return $query->where('status', self::STATUS_DISPUTED);
    }

    /**
     * Scope: Critical
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    /**
     * Relationships
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function slaSetting(): BelongsTo
    {
        return $this->belongsTo(VendorSLASetting::class, 'sla_setting_id');
    }

    public function violatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
