<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorEmail extends Model
{
    use HasFactory;

    protected $table = 'vendor_emails';

    protected $fillable = [
        'user_id',
        'email',
        'is_primary',
        'verified_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /* ===================== RELATIONSHIPS ===================== */

    /**
     * Get the vendor (user) that owns this email
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ===================== SCOPES ===================== */

    /**
     * Scope to get verified emails only
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope to get unverified emails only
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    /**
     * Scope to get primary email
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /* ===================== HELPERS ===================== */

    /**
     * Check if email is verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
