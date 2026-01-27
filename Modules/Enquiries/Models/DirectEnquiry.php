<?php

namespace Modules\Enquiries\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectEnquiry extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'phone',
        'email',
        'location_city',
        'hoarding_type',
        'hoarding_location',
        'preferred_locations',
        'preferred_modes',
        'best_way_to_connect',
        'is_verified',
        'remarks',
        'is_email_verified',
        'is_phone_verified',
    ];

    protected $casts = [
        'preferred_locations' => 'array',
        'preferred_modes' => 'array',
        'is_email_verified' => 'boolean',
        'is_phone_verified' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /* ===================== RELATIONSHIPS ===================== */

    /**
     * Get the vendor (if this enquiry is for a specific vendor)
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /* ===================== SCOPES ===================== */

    /**
     * Scope to get verified enquiries only
     */
    public function scopeVerified($query)
    {
        return $query->where('is_email_verified', true)->where('is_phone_verified', true);
    }

    /* ===================== HELPERS ===================== */

    /**
     * Check if enquiry is fully verified
     */
    public function isFullyVerified(): bool
    {
        return $this->is_email_verified && $this->is_phone_verified;
    }
}

