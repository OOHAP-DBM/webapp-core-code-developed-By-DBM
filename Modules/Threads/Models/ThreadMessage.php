<?php

namespace Modules\Threads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Offer;
use Modules\Quotations\Models\Quotation;

class ThreadMessage extends Model
{
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

    // Sender type constants
    const SENDER_CUSTOMER = 'customer';
    const SENDER_VENDOR = 'vendor';
    const SENDER_ADMIN = 'admin';

    // Message type constants
    const TYPE_TEXT = 'text';
    const TYPE_OFFER = 'offer';
    const TYPE_QUOTATION = 'quotation';
    const TYPE_SYSTEM = 'system';

    /**
     * Relationships
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Scopes
     */
    public function scopeUnreadByCustomer($query)
    {
        return $query->where('is_read_customer', false);
    }

    public function scopeUnreadByVendor($query)
    {
        return $query->where('is_read_vendor', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('message_type', $type);
    }

    /**
     * Helper methods
     */
    public function isText(): bool
    {
        return $this->message_type === self::TYPE_TEXT;
    }

    public function isOffer(): bool
    {
        return $this->message_type === self::TYPE_OFFER;
    }

    public function isQuotation(): bool
    {
        return $this->message_type === self::TYPE_QUOTATION;
    }

    public function isSystem(): bool
    {
        return $this->message_type === self::TYPE_SYSTEM;
    }

    public function isSentByCustomer(): bool
    {
        return $this->sender_type === self::SENDER_CUSTOMER;
    }

    public function isSentByVendor(): bool
    {
        return $this->sender_type === self::SENDER_VENDOR;
    }

    public function isSentByAdmin(): bool
    {
        return $this->sender_type === self::SENDER_ADMIN;
    }

    public function markAsRead(string $userType): void
    {
        $updates = ['read_at' => now()];
        
        if ($userType === 'customer') {
            $updates['is_read_customer'] = true;
        } elseif ($userType === 'vendor') {
            $updates['is_read_vendor'] = true;
        }

        $this->update($updates);
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function getAttachmentCount(): int
    {
        return count($this->attachments ?? []);
    }
}
