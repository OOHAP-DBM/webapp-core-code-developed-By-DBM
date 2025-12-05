<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Facades\Crypt;

class VendorKYC extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     */
    protected $table = 'vendor_kyc';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vendor_id',
        'business_type',
        'business_name',
        'gst_number',
        'pan_number',
        'legal_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address',
        'city',
        'state',
        'pincode',
        'account_holder_name',
        'account_number',
        'ifsc',
        'bank_name',
        'account_type',
        'verification_status',
        'verification_details',
        'submitted_at',
        'verified_at',
        'verified_by',
        'razorpay_subaccount_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'verification_details' => 'array',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden.
     */
    protected $hidden = [
        'account_number',
    ];

    /**
     * Spatie Media Library Collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pan_card')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
            ->maxFilesize(5 * 1024 * 1024); // 5MB

        $this->addMediaCollection('aadhar_card')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
            ->maxFilesize(5 * 1024 * 1024);

        $this->addMediaCollection('gst_certificate')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
            ->maxFilesize(5 * 1024 * 1024);

        $this->addMediaCollection('business_proof')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
            ->maxFilesize(5 * 1024 * 1024);

        $this->addMediaCollection('cancelled_cheque')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
            ->maxFilesize(5 * 1024 * 1024);
    }

    /**
     * Relationships
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Accessors & Mutators
     */
    public function setAccountNumberAttribute($value): void
    {
        $this->attributes['account_number'] = Crypt::encryptString($value);
    }

    public function getAccountNumberAttribute($value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get masked account number for display
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $accountNumber = $this->account_number;
        if (!$accountNumber) {
            return 'N/A';
        }
        
        $length = strlen($accountNumber);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        return str_repeat('*', $length - 4) . substr($accountNumber, -4);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('verification_status', 'under_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    public function scopeResubmissionRequired($query)
    {
        return $query->where('verification_status', 'resubmission_required');
    }

    /**
     * Status checks
     */
    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function isUnderReview(): bool
    {
        return $this->verification_status === 'under_review';
    }

    public function isApproved(): bool
    {
        return $this->verification_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    public function requiresResubmission(): bool
    {
        return $this->verification_status === 'resubmission_required';
    }

    /**
     * Action methods
     */
    public function markAsSubmitted(): void
    {
        $this->update([
            'verification_status' => 'under_review',
            'submitted_at' => now(),
        ]);
    }

    public function approve(int $verifiedBy): void
    {
        $this->update([
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
        ]);
    }

    public function reject(int $verifiedBy, string $reason): void
    {
        $this->update([
            'verification_status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
            'verification_details' => array_merge($this->verification_details ?? [], [
                'rejection_reason' => $reason,
                'rejected_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    public function requestResubmission(int $verifiedBy, array $remarks): void
    {
        $this->update([
            'verification_status' => 'resubmission_required',
            'verified_by' => $verifiedBy,
            'verification_details' => array_merge($this->verification_details ?? [], [
                'resubmission_remarks' => $remarks,
                'requested_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Get KYC completion status
     */
    public function getCompletionStatusAttribute(): array
    {
        $requiredDocs = ['pan_card', 'cancelled_cheque'];
        $optionalDocs = ['aadhar_card', 'gst_certificate', 'business_proof'];
        
        $requiredComplete = true;
        $requiredCount = 0;
        foreach ($requiredDocs as $doc) {
            if ($this->hasMedia($doc)) {
                $requiredCount++;
            } else {
                $requiredComplete = false;
            }
        }
        
        $optionalCount = 0;
        foreach ($optionalDocs as $doc) {
            if ($this->hasMedia($doc)) {
                $optionalCount++;
            }
        }
        
        $totalDocs = count($requiredDocs) + count($optionalDocs);
        $completedDocs = $requiredCount + $optionalCount;
        
        return [
            'required_complete' => $requiredComplete,
            'required_count' => $requiredCount,
            'optional_count' => $optionalCount,
            'total_count' => $completedDocs,
            'percentage' => round(($completedDocs / $totalDocs) * 100, 2),
            'can_submit' => $requiredComplete && !empty($this->business_name) && !empty($this->pan_number),
        ];
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->verification_status) {
            'pending' => 'bg-secondary',
            'under_review' => 'bg-info',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'resubmission_required' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->verification_status) {
            'pending' => 'Pending',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'resubmission_required' => 'Resubmission Required',
            default => ucfirst(str_replace('_', ' ', $this->verification_status)),
        };
    }
}
