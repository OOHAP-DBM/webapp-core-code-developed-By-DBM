<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Modules\Enquiries\Models\DirectEnquiry;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $guard_name = 'web';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'otp',
        'otp_expires_at',
        'status',
        'avatar',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        // GST and company details (PROMPT 64)
        'gstin',
        'company_name',
        'pan',
        'customer_type',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_state_code',
        'billing_pincode',
        // Multi-role switching (PROMPT 96)
        'active_role',
        'previous_role',
        'last_role_switch_at',
        // Notification preferences
        'notification_email',
        'notification_push',
        'notification_whatsapp',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_role_switch_at' => 'datetime',
            'password' => 'hashed',
            'notification_email' => 'boolean',
            'notification_push' => 'boolean',
            'notification_whatsapp' => 'boolean',
        ];
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if OTP is valid
     */
    public function isOTPValid(string $otp): bool
    {
        return $this->otp === $otp
            && $this->otp_expires_at
            && $this->otp_expires_at->isFuture();
    }

    /**
     * Generate OTP
     */
    public function generateOTP(): string
    {
        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        // $otp =1234;

        $this->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }

    /**
     * Clear OTP
     */
    public function clearOTP(): void
    {
        $this->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get user's primary role name
     */
    public function getPrimaryRole(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * Get vendor profile
     */
    public function vendorProfile()
    {
        return $this->hasOne(VendorProfile::class);
    }

    /**
     * Check if user is a vendor
     */
    public function isVendor(): bool
    {
        return $this->hasRole('vendor');
    }

    /**
     * Check if user is a customer
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Check if vendor onboarding is complete
     */
    public function hasCompletedVendorOnboarding(): bool
    {
        if (!$this->isVendor()) {
            return false;
        }

        return $this->vendorProfile && $this->vendorProfile->isOnboardingComplete();
    }

    /**
     * Check if vendor is approved
     */
    public function isVendorApproved(): bool
    {
        if (!$this->isVendor()) {
            return false;
        }

        return $this->vendorProfile && $this->vendorProfile->isApproved();
    }

    /**
     * Get vendor onboarding status
     */
    public function getVendorOnboardingStatus(): ?string
    {
        if (!$this->isVendor() || !$this->vendorProfile) {
            return null;
        }

        return $this->vendorProfile->onboarding_status;
    }

    /**
     * Get current onboarding step for vendor
     */
    public function getCurrentOnboardingStep(): int
    {
        if (!$this->isVendor() || !$this->vendorProfile) {
            return 1;
        }

        return $this->vendorProfile->onboarding_step;
    }

    /**
     * Get user's dashboard route based on role
     */
    public function getDashboardRoute(): string
    {
        // Use active role if available (PROMPT 96)
        $role = $this->active_role ?? $this->getPrimaryRole();

        return match ($role) {
            'super_admin', 'admin' => 'admin.dashboard',
            'vendor', 'subvendor' => 'vendor.dashboard',
            'staff' => 'staff.dashboard',
            default => 'customer.dashboard',
        };
    }

    /**
     * Get user's active role (PROMPT 96)
     */
    public function getActiveRole(): ?string
    {
        return $this->active_role ?? $this->getPrimaryRole();
    }

    /**
     * Get layout for current active role (PROMPT 96)
     */
    public function getActiveLayout(): string
    {
        $role = $this->getActiveRole();

        return match ($role) {
            'super_admin', 'admin' => 'layouts.admin',
            'vendor', 'subvendor' => 'layouts.vendor',
            'staff' => 'layouts.staff',
            default => 'layouts.customer',
        };
    }

    /**
     * Check if user can switch roles (PROMPT 96)
     */
    public function canSwitchRoles(): bool
    {
        $allRoles = $this->roles()->pluck('name')->toArray();

        // Customer cannot switch
        if (in_array('customer', $allRoles) && count($allRoles) === 1) {
            return false;
        }

        // Only admins with multiple roles can switch
        $hasAdmin = in_array('admin', $allRoles) || in_array('super_admin', $allRoles);

        return $hasAdmin && count($allRoles) > 1;
    }

    /**
     * Get available roles for switching (PROMPT 96)
     */
    public function getAvailableRoles(): array
    {
        if (!$this->canSwitchRoles()) {
            return [];
        }

        $allRoles = $this->roles()->pluck('name')->toArray();
        $hasAdmin = in_array('admin', $allRoles) || in_array('super_admin', $allRoles);
        $hasVendor = in_array('vendor', $allRoles) || in_array('subvendor', $allRoles);

        if ($hasAdmin && $hasVendor) {
            // Admin with vendor role - can switch between both
            return array_values(array_intersect($allRoles, ['super_admin', 'admin', 'vendor', 'subvendor']));
        }

        if ($hasAdmin) {
            // Admin without vendor role - can switch between admin types only
            return array_values(array_intersect($allRoles, ['super_admin', 'admin']));
        }

        return [];
    }

    /**
     * Get the hoardings owned by the vendor.
     */
    public function hoardings(): HasMany
    {
        return $this->hasMany(Hoarding::class, 'vendor_id');
    }

    /**
     * Get bookings where user is the vendor (PROMPT 48)
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vendor_id');
    }

    /**
     * Get bookings where user is the customer (PROMPT 48)
     */
    public function customerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    /**
     * Get tasks assigned to vendor (PROMPT 48)
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'vendor_id');
    }

    /**
     * Get the vendor's KYC record
     */
    public function vendorKYC()
    {
        return $this->hasOne(VendorKYC::class, 'vendor_id');
    }

    /**
     * Get the vendor's ledger entries
     */
    public function ledgerEntries()
    {
        return $this->hasMany(VendorLedger::class, 'vendor_id');
    }

    /**
     * Get the user's wishlist/shortlist items (PROMPT 50)
     */
    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the user's invoices (PROMPT 64)
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(\App\Models\Invoice::class, 'customer_id');
    }

    /**
     * Check if user has wishlisted a hoarding (PROMPT 50)
     */
    public function hasWishlisted(int $hoardingId): bool
    {
        return $this->wishlist()->where('hoarding_id', $hoardingId)->exists();
    }

    /**
     * Get wishlist count (PROMPT 50)
     */
    public function wishlistCount(): int
    {
        return $this->wishlist()->count();
    }

    /**
     * Get vendor's SLA violations (PROMPT 68)
     */
    public function slaViolations(): HasMany
    {
        return $this->hasMany(\App\Models\VendorSLAViolation::class, 'vendor_id');
    }

    /**
     * Get vendor's custom SLA setting (PROMPT 68)
     */
    public function customSLASetting()
    {
        return $this->belongsTo(\App\Models\VendorSLASetting::class, 'vendor_sla_setting_id');
    }

    /**
     * Update reliability tier based on current score (PROMPT 68)
     */
    public function updateReliabilityTier(): void
    {
        $score = $this->reliability_score;

        $tier = match (true) {
            $score >= 90 => 'excellent',
            $score >= 75 => 'good',
            $score >= 60 => 'average',
            $score >= 40 => 'poor',
            default => 'critical',
        };

        $this->update(['reliability_tier' => $tier]);
    }

    /**
     * Get reliability tier color (PROMPT 68)
     */
    public function getReliabilityTierColor(): string
    {
        return match ($this->reliability_tier) {
            'excellent' => 'success',
            'good' => 'info',
            'average' => 'warning',
            'poor' => 'danger',
            'critical' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get reliability score percentage (PROMPT 68)
     */
    public function getReliabilityScorePercentage(): float
    {
        return round($this->reliability_score ?? 100.00, 2);
    }

    /**
     * Check if vendor is reliable (score >= 75) (PROMPT 68)
     */
    public function isReliable(): bool
    {
        return $this->reliability_score >= 75;
    }

    /**
     * Check if vendor has critical reliability (score < 40) (PROMPT 68)
     */
    public function hasCriticalReliability(): bool
    {
        return $this->reliability_score < 40;
    }

    /**
     * Get vendor's monthly violation count (PROMPT 68)
     */
    public function getMonthlyViolationCount(): int
    {
        return $this->sla_violations_this_month ?? 0;
    }

    /**
     * Get vendor's total violation count (PROMPT 68)
     */
    public function getTotalViolationCount(): int
    {
        return $this->sla_violations_count ?? 0;
    }

    /**
     * Check if vendor is at risk of critical status (PROMPT 68)
     */
    public function isAtRisk(): bool
    {
        // At risk if:
        // - Score is between 40-60 (poor tier)
        // - OR has 2+ violations this month
        // - OR has 5+ total violations
        return $this->reliability_score < 60 && $this->reliability_score >= 40
            || $this->sla_violations_this_month >= 2
            || $this->sla_violations_count >= 5;
    }

    /**
     * Get vendor's on-time performance summary (PROMPT 68)
     */
    public function getPerformanceSummary(): array
    {
        return [
            'reliability_score' => $this->reliability_score ?? 100.00,
            'reliability_tier' => $this->reliability_tier ?? 'excellent',
            'total_violations' => $this->sla_violations_count ?? 0,
            'monthly_violations' => $this->sla_violations_this_month ?? 0,
            'on_time_acceptance_rate' => $this->on_time_acceptance_rate ?? 100.00,
            'on_time_quote_rate' => $this->on_time_quote_rate ?? 100.00,
            'avg_acceptance_time' => $this->avg_acceptance_time_hours ?? 0,
            'avg_quote_time' => $this->avg_quote_time_hours ?? 0,
            'last_violation' => $this->last_sla_violation_at,
        ];
    }


    /**
     * Get IDs of hoardings owned by the user (vendor, vendor_staff, agency, agency_staff)
     */
    public function getOwnedHoardingIds(): array
    {
        // If user is vendor or vendor_staff, get hoardings where vendor_id = user id
        if ($this->hasRole('vendor') || $this->hasRole('vendor_staff')) {
            return $this->hoardings()->pluck('id')->toArray();
        }
        // If user is agency or agency_staff, get hoardings where agency_id = user id (if such a relation exists)
        if ($this->hasRole('agency') || $this->hasRole('agency_staff')) {
            if (method_exists($this, 'agencyHoardings')) {
                return $this->agencyHoardings()->pluck('id')->toArray();
            }
            // fallback: try to get hoardings where agency_id = user id
            return \App\Models\Hoarding::where('agency_id', $this->id)->pluck('id')->toArray();
        }
        return [];
    }

    /**
     * Get all emails for this vendor (primary + additional verified emails)
     */
    public function getAllEmailsAttribute(): array
    {
        $emails = [$this->email];

        if ($this->vendorProfile) {
            $emails = array_merge($emails, $this->vendorProfile->verified_emails);
        }

        return array_unique($emails);
    }

    /**
     * Get all emails that should receive notifications
     */
    public function getNotificationEmailsAttribute(): array
    {
        if (!$this->notification_email) {
            return [];
        }

        if ($this->vendorProfile) {
            return $this->vendorProfile->notification_emails;
        }

        return [$this->email];
    }


    /**
     * Send a notification to all enabled vendor emails if global preference is enabled
     */

    public function notifyVendorEmails(Mailable $mailable)
    {
        if (!$this->notification_email) {
            return;
        }

        $emails = $this->vendorProfile?->notification_emails ?? [$this->email];

        foreach ($emails as $email) {
            Mail::to($email)->send($mailable);
        }
    }


    /**
     * Get active hoardings only
     */
    public function activeHoardings(): HasMany
    {
        return $this->hasMany(Hoarding::class, 'vendor_id')
            ->where('status', 'active');
    }

    /**
     * Get available hoardings (active and not on hold)
     */
    public function availableHoardings(): HasMany
    {
        return $this->hasMany(Hoarding::class, 'vendor_id')
            ->available();
    }

    /**
     * Get enquiries assigned to this vendor
     */
    public function assignedEnquiries(): BelongsToMany
    {
        return $this->belongsToMany(DirectEnquiry::class, 'enquiry_vendor', 'vendor_id', 'enquiry_id')
            ->withPivot('has_viewed', 'viewed_at', 'response_status', 'vendor_notes')
            ->withTimestamps();
    }

    /**
     * Get new unviewed enquiries for vendor
     */
    public function newEnquiries(): BelongsToMany
    {
        return $this->belongsToMany(DirectEnquiry::class, 'enquiry_vendor', 'vendor_id', 'enquiry_id')
            ->wherePivot('has_viewed', false)
            ->withPivot('has_viewed', 'viewed_at', 'response_status', 'vendor_notes')
            ->withTimestamps();
    }


    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->active_role, ['admin', 'superadmin']);
    }

    /**
     * Get total hoardings count for vendor
     */
    public function getTotalHoardingsAttribute(): int
    {
        return $this->hoardings()->count();
    }

    /**
     * Get active hoardings count for vendor
     */
    public function getActiveHoardingsCountAttribute(): int
    {
        return $this->activeHoardings()->count();
    }

    /**
     * Get pending enquiries count for vendor
     */
    public function getPendingEnquiriesCountAttribute(): int
    {
        return $this->assignedEnquiries()
            ->where('status', 'new')
            ->count();
    }
    /**
     * Send vendor emails using a Mailable instance
     * @param \Illuminate\Mail\Mailable $mailable
     */
    public function sendVendorEmails($mailable)
    {
        \Mail::to($this->email)->send($mailable);
    }
}
