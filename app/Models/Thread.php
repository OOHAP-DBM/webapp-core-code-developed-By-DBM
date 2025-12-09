<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thread extends Model
{
    use HasFactory;

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
        'unread_count_customer' => 'integer',
        'unread_count_vendor' => 'integer',
    ];

    /**
     * Get the enquiry associated with the thread
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    /**
     * Get the customer associated with the thread
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the vendor associated with the thread
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get all messages in the thread
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message in the thread
     */
    public function latestMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ThreadMessage::class)->latestOfMany();
    }

    /**
     * Get unread messages for a specific user
     */
    public function unreadMessagesFor($userId)
    {
        return $this->messages()->where('sender_id', '!=', $userId)
            ->where(function($query) use ($userId) {
                // Check if user is customer or vendor and apply appropriate read flag
                $user = User::find($userId);
                if ($user && $user->role === 'customer') {
                    $query->where('is_read_customer', false);
                } elseif ($user && $user->role === 'vendor') {
                    $query->where('is_read_vendor', false);
                }
            });
    }

    /**
     * Mark all messages as read for a specific user
     */
    public function markAsReadFor($userId)
    {
        $user = User::find($userId);
        if (!$user) return;

        if ($user->role === 'customer') {
            $this->messages()
                ->where('sender_id', '!=', $userId)
                ->where('is_read_customer', false)
                ->update(['is_read_customer' => true, 'read_at' => now()]);
            $this->update(['unread_count_customer' => 0]);
        } elseif ($user->role === 'vendor') {
            $this->messages()
                ->where('sender_id', '!=', $userId)
                ->where('is_read_vendor', false)
                ->update(['is_read_vendor' => true, 'read_at' => now()]);
            $this->update(['unread_count_vendor' => 0]);
        }
    }

    /**
     * Get thread title based on context
     */
    public function getTitleAttribute()
    {
        if ($this->enquiry) {
            $hoardingTitle = $this->enquiry->hoarding ? $this->enquiry->hoarding->title : 'Hoarding';
            return "Enquiry #{$this->enquiry->id} - {$hoardingTitle}";
        }
        return "Thread #{$this->id}";
    }

    /**
     * Get thread participants
     */
    public function getParticipantsAttribute()
    {
        $participants = collect([$this->customer]);
        if ($this->vendor) {
            $participants->push($this->vendor);
        }
        return $participants;
    }

    /**
     * Scope: Active threads
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: For customer
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: For vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope: With unread messages for user
     */
    public function scopeWithUnread($query, $userId)
    {
        $user = User::find($userId);
        if (!$user) return $query;

        if ($user->role === 'customer') {
            return $query->where('unread_count_customer', '>', 0);
        } elseif ($user->role === 'vendor') {
            return $query->where('unread_count_vendor', '>', 0);
        }
        return $query;
    }

    /**
     * Get other participant (for current user)
     */
    public function getOtherParticipant($currentUserId)
    {
        if ($this->customer_id == $currentUserId) {
            return $this->vendor;
        }
        return $this->customer;
    }
}
