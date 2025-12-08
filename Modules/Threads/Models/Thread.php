<?php

namespace Modules\Threads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Enquiries\Models\Enquiry;
use App\Models\User;

class Thread extends Model
{
    protected $fillable = [
        'enquiry_id',
        'customer_id',
        'vendor_id',
        'is_multi_vendor',
        'status',
        'last_message_at',
        'unread_count_customer',
        'unread_count_vendor',
    ];

    protected $casts = [
        'is_multi_vendor' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Relationships
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(ThreadMessage::class)->latest();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Helper methods
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isMultiVendor(): bool
    {
        return $this->is_multi_vendor;
    }

    public function hasUnreadForCustomer(): bool
    {
        return $this->unread_count_customer > 0;
    }

    public function hasUnreadForVendor(): bool
    {
        return $this->unread_count_vendor > 0;
    }

    public function incrementUnread(string $userType): void
    {
        if ($userType === 'customer') {
            $this->increment('unread_count_customer');
        } elseif ($userType === 'vendor') {
            $this->increment('unread_count_vendor');
        }
    }

    public function resetUnread(string $userType): void
    {
        if ($userType === 'customer') {
            $this->update(['unread_count_customer' => 0]);
        } elseif ($userType === 'vendor') {
            $this->update(['unread_count_vendor' => 0]);
        }
    }
}
