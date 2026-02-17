<?php

namespace Modules\Enquiries\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class DirectEnquiry extends Model
{
    protected $table = 'direct_web_enquiries';

    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'is_phone_verified',
        'hoarding_type',
        'location_city',
        'preferred_locations',
        'remarks',
        'preferred_modes',
        'status',
        'source',
        'assigned_to',
        'contacted_at',
        'quote_sent_at',
        'admin_notes',
    ];

    protected $casts = [
        'preferred_locations' => 'array',
        'preferred_modes' => 'array',
        'is_phone_verified' => 'boolean',
        'contacted_at' => 'datetime',
        'quote_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'hoarding_types_array',
        'formatted_phone',
        'status_badge',
        'age_in_hours'
    ];

    /**
     * Get hoarding types as array
     */
    public function getHoardingTypesArrayAttribute(): array
    {
        return explode(',', $this->hoarding_type);
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute(): string
    {
        return '+91 ' . $this->phone;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'new' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">New</span>',
            'contacted' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Contacted</span>',
            'quote_sent' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Quote Sent</span>',
            'negotiating' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Negotiating</span>',
            'confirmed' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>',
            'rejected' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>',
            'expired' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Expired</span>',
        ];

        return $badges[$this->status] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
    }

    /**
     * Get enquiry age in hours
     */
    public function getAgeInHoursAttribute(): int
    {
        return $this->created_at->diffInHours(now());
    }

    /**
     * Check if enquiry is fresh (less than 24 hours old)
     */
    public function isFresh(): bool
    {
        return $this->age_in_hours < 24;
    }

    /**
     * Check if enquiry is urgent (new status and more than 2 hours old)
     */
    public function isUrgent(): bool
    {
        return $this->status === 'new' && $this->age_in_hours > 2;
    }

    /**
     * Get assigned admin/manager
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get vendors assigned to this enquiry
     */
    public function assignedVendors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enquiry_vendor', 'enquiry_id', 'vendor_id')
            ->withPivot('has_viewed', 'viewed_at', 'response_status', 'vendor_notes')
            ->withTimestamps();
    }

    /**
     * Scope: Get fresh enquiries
     */
    public function scopeFresh($query)
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }

    /**
     * Scope: Get urgent enquiries
     */
    public function scopeUrgent($query)
    {
        return $query->where('status', 'new')
            ->where('created_at', '<=', now()->subHours(2));
    }

    /**
     * Scope: Filter by city
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('location_city', 'like', "%{$city}%");
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Search enquiries
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('location_city', 'like', "%{$search}%");
        });
    }

    /**
     * Mark as contacted
     */
    public function markAsContacted(): void
    {
        $this->update([
            'status' => 'contacted',
            'contacted_at' => now()
        ]);
    }

    /**
     * Mark quote as sent
     */
    public function markQuoteSent(): void
    {
        $this->update([
            'status' => 'quote_sent',
            'quote_sent_at' => now()
        ]);
    }
}