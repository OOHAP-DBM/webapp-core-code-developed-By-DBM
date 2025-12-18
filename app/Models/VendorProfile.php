<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorProfile extends Model

{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'onboarding_status',
        'onboarding_step',
        'onboarding_completed_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
        
        // Company Details
        'company_name',
        'company_registration_number',
        'company_type',
        'gstin',
        'pan',
        'registered_address',
        'city',
        'state',
        'pincode',
        'website',
        
        // Business Information
        'year_established',
        'total_hoardings',
        'service_cities',
        'hoarding_types',
        'business_description',
        'contact_person_name',
        'contact_person_designation',
        'contact_person_phone',
        'contact_person_email',
        
        // KYC Documents
        'pan_card_document',
        'gst_certificate',
        'company_registration_certificate',
        'address_proof',
        'cancelled_cheque',
        'owner_id_proof',
        'other_documents',
        'kyc_verified',
        'kyc_verified_at',
        
        // Bank Details
        'bank_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'branch_name',
        'account_type',
        'bank_verified',
        
        // Terms & Agreement
        'terms_accepted',
        'terms_accepted_at',
        'terms_ip_address',
        'commission_agreement_accepted',
        'commission_percentage',
        'special_terms',
    ];

    protected $casts = [
        'service_cities' => 'array',
        'hoarding_types' => 'array',
        'other_documents' => 'array',
        'kyc_verified' => 'boolean',
        'bank_verified' => 'boolean',
        'terms_accepted' => 'boolean',
        'commission_agreement_accepted' => 'boolean',
        'onboarding_completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'commission_percentage' => 'decimal:2',
    ];


    /**
     * Get the vendor metrics (one-to-one)
     */
    public function vendorMetric()
    {
        return $this->hasOne(VendorMetric::class, 'vendor_id');
    }

    // --- Backward compatibility accessors for vendor metrics ---
    // If vendorMetric exists, use it; else fallback to users table
    public function getReliabilityScoreAttribute()
    {
        return optional($this->vendorMetric)->reliability_score ?? $this->user->reliability_score;
    }
    public function getReliabilityTierAttribute()
    {
        return optional($this->vendorMetric)->reliability_tier ?? $this->user->reliability_tier;
    }
    public function getSlaViolationsCountAttribute()
    {
        return optional($this->vendorMetric)->sla_violations_count ?? $this->user->sla_violations_count;
    }
    public function getSlaViolationsThisMonthAttribute()
    {
        return optional($this->vendorMetric)->sla_violations_this_month ?? $this->user->sla_violations_this_month;
    }
    public function getTotalPenaltyPointsAttribute()
    {
        return optional($this->vendorMetric)->total_penalty_points ?? $this->user->total_penalty_points;
    }
    public function getEnquiriesAcceptedCountAttribute()
    {
        return optional($this->vendorMetric)->enquiries_accepted_count ?? $this->user->enquiries_accepted_count;
    }
    public function getQuotesSubmittedCountAttribute()
    {
        return optional($this->vendorMetric)->quotes_submitted_count ?? $this->user->quotes_submitted_count;
    }
    public function getQuotesAcceptedCountAttribute()
    {
        return optional($this->vendorMetric)->quotes_accepted_count ?? $this->user->quotes_accepted_count;
    }
    public function getAvgAcceptanceTimeHoursAttribute()
    {
        return optional($this->vendorMetric)->avg_acceptance_time_hours ?? $this->user->avg_acceptance_time_hours;
    }
    public function getAvgQuoteTimeHoursAttribute()
    {
        return optional($this->vendorMetric)->avg_quote_time_hours ?? $this->user->avg_quote_time_hours;
    }
    public function getOnTimeAcceptanceRateAttribute()
    {
        return optional($this->vendorMetric)->on_time_acceptance_rate ?? $this->user->on_time_acceptance_rate;
    }
    public function getOnTimeQuoteRateAttribute()
    {
        return optional($this->vendorMetric)->on_time_quote_rate ?? $this->user->on_time_quote_rate;
    }
    public function getQuoteWinRateAttribute()
    {
        return optional($this->vendorMetric)->quote_win_rate ?? $this->user->quote_win_rate;
    }
    public function getLastSlaViolationAtAttribute()
    {
        return optional($this->vendorMetric)->last_sla_violation_at ?? $this->user->last_sla_violation_at;
    }
    public function getLastScoreUpdateAtAttribute()
    {
        return optional($this->vendorMetric)->last_score_update_at ?? $this->user->last_score_update_at;
    }
    public function getLastRecoveryAtAttribute()
    {
        return optional($this->vendorMetric)->last_recovery_at ?? $this->user->last_recovery_at;
    }
    public function getVendorSlaSettingIdAttribute()
    {
        return optional($this->vendorMetric)->vendor_sla_setting_id ?? $this->user->vendor_sla_setting_id;
    }
    /**
     * Get the user that owns the vendor profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved this vendor
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if onboarding is complete
     */
    public function isOnboardingComplete(): bool
    {
        return $this->onboarding_step === 5 && 
               $this->terms_accepted && 
               !empty($this->onboarding_completed_at);
    }

    /**
     * Check if vendor is approved
     */
    public function isApproved(): bool
    {
        return $this->onboarding_status === 'approved';
    }

    /**
     * Check if vendor is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this->onboarding_status === 'pending_approval';
    }

    /**
     * Check if vendor is rejected
     */
    public function isRejected(): bool
    {
        return $this->onboarding_status === 'rejected';
    }

    /**
     * Check if vendor is suspended
     */
    public function isSuspended(): bool
    {
        return $this->onboarding_status === 'suspended';
    }

    /**
     * Get onboarding progress percentage
     */
    public function getOnboardingProgress(): int
    {
        return ($this->onboarding_step / 5) * 100;
    }

    /**
     * Get next onboarding step
     */
    public function getNextStep(): int
    {
        return min($this->onboarding_step + 1, 5);
    }

    /**
     * Can proceed to next step
     */
    public function canProceedToNextStep(): bool
    {
        return $this->validateCurrentStep();
    }

    /**
     * Validate current step is complete
     */
    public function validateCurrentStep(): bool
    {
        return match($this->onboarding_step) {
            1 => $this->validateCompanyDetails(),
            2 => $this->validateBusinessInformation(),
            3 => $this->validateKYCDocuments(),
            4 => $this->validateBankDetails(),
            5 => $this->validateTermsAcceptance(),
            default => false,
        };
    }

    /**
     * Validate Step 1: Company Details
     */
    protected function validateCompanyDetails(): bool
    {
        return !empty($this->company_name) &&
               !empty($this->company_type) &&
               !empty($this->gstin) &&
               !empty($this->pan) &&
               !empty($this->registered_address) &&
               !empty($this->city) &&
               !empty($this->state) &&
               !empty($this->pincode);
    }

    /**
     * Validate Step 2: Business Information
     */
    protected function validateBusinessInformation(): bool
    {
        return !empty($this->year_established) &&
               !empty($this->service_cities) &&
               !empty($this->hoarding_types) &&
               !empty($this->contact_person_name) &&
               !empty($this->contact_person_phone) &&
               !empty($this->contact_person_email);
    }

    /**
     * Validate Step 3: KYC Documents
     */
    protected function validateKYCDocuments(): bool
    {
        return !empty($this->pan_card_document) &&
               !empty($this->gst_certificate) &&
               !empty($this->company_registration_certificate) &&
               !empty($this->address_proof) &&
               !empty($this->cancelled_cheque);
    }

    /**
     * Validate Step 4: Bank Details
     */
    protected function validateBankDetails(): bool
    {
        return !empty($this->bank_name) &&
               !empty($this->account_holder_name) &&
               !empty($this->account_number) &&
               !empty($this->ifsc_code) &&
               !empty($this->branch_name) &&
               !empty($this->account_type);
    }

    /**
     * Validate Step 5: Terms Acceptance
     */
    protected function validateTermsAcceptance(): bool
    {
        return $this->terms_accepted === true &&
               $this->commission_agreement_accepted === true;
    }

    /**
     * Mark onboarding as complete
     */
    public function completeOnboarding(): void
    {
        $this->update([
            'onboarding_step' => 5,
            'onboarding_completed_at' => now(),
            'onboarding_status' => 'pending_approval',
        ]);
    }

    /**
     * Approve vendor
     */
    public function approve(User $admin): void
    {
        $this->update([
            'onboarding_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);
        
        // Update user status
        $this->user->update(['status' => 'active']);
    }

    /**
     * Reject vendor
     */
    public function reject(string $reason): void
    {
        $this->update([
            'onboarding_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Suspend vendor
     */
    public function suspend(): void
    {
        $this->update(['onboarding_status' => 'suspended']);
        $this->user->update(['status' => 'suspended']);
    }
}
