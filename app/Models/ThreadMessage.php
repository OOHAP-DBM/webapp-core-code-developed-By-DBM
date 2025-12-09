<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreadMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'sender_id',
        'sender_type',
        'message_type',
        'message',
        'attachments',
        'offer_id',
        'quotation_id',
        'is_read_customer',
        'is_read_vendor',
        'read_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read_customer' => 'boolean',
        'is_read_vendor' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the thread that owns the message
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Get the sender of the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the offer associated with the message (if any)
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Get the quotation associated with the message (if any)
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Check if message is read by a specific user
     */
    public function isReadBy($userId)
    {
        $user = User::find($userId);
        if (!$user) return false;

        if ($user->role === 'customer') {
            return $this->is_read_customer;
        } elseif ($user->role === 'vendor') {
            return $this->is_read_vendor;
        }
        return false;
    }

    /**
     * Mark message as read by a specific user
     */
    public function markAsReadBy($userId)
    {
        $user = User::find($userId);
        if (!$user) return;

        if ($user->role === 'customer' && !$this->is_read_customer) {
            $this->update([
                'is_read_customer' => true,
                'read_at' => now()
            ]);
        } elseif ($user->role === 'vendor' && !$this->is_read_vendor) {
            $this->update([
                'is_read_vendor' => true,
                'read_at' => now()
            ]);
        }
    }

    /**
     * Scope: Text messages only
     */
    public function scopeTextMessages($query)
    {
        return $query->where('message_type', 'text');
    }

    /**
     * Scope: System messages only
     */
    public function scopeSystemMessages($query)
    {
        return $query->where('message_type', 'system');
    }

    /**
     * Scope: Unread for customer
     */
    public function scopeUnreadForCustomer($query)
    {
        return $query->where('is_read_customer', false);
    }

    /**
     * Scope: Unread for vendor
     */
    public function scopeUnreadForVendor($query)
    {
        return $query->where('is_read_vendor', false);
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if message has attachments
     */
    public function hasAttachments()
    {
        return !empty($this->attachments);
    }

    /**
     * Get attachment URLs
     */
    public function getAttachmentUrls()
    {
        if (!$this->hasAttachments()) {
            return [];
        }

        return collect($this->attachments)->map(function ($attachment) {
            return [
                'name' => $attachment['name'] ?? 'Attachment',
                'url' => asset('storage/' . $attachment['path']),
                'size' => $attachment['size'] ?? null,
                'type' => $attachment['type'] ?? null,
            ];
        })->toArray();
    }

    /**
     * Boot method to handle events
     */
    protected static function boot()
    {
        parent::boot();

        // After creating a message, update thread's last_message_at and unread counts
        static::created(function ($message) {
            $thread = $message->thread;
            
            $updateData = ['last_message_at' => now()];

            // Increment unread count for the recipient
            if ($message->sender_type === 'customer') {
                $updateData['unread_count_vendor'] = $thread->unread_count_vendor + 1;
            } elseif ($message->sender_type === 'vendor') {
                $updateData['unread_count_customer'] = $thread->unread_count_customer + 1;
            }

            $thread->update($updateData);
        });
    }
}
