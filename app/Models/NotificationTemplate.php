<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'event_type',
        'channel',
        'description',
        'subject',
        'body',
        'html_body',
        'metadata',
        'available_placeholders',
        'is_active',
        'is_system_default',
        'priority',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'available_placeholders' => 'array',
        'is_active' => 'boolean',
        'is_system_default' => 'boolean',
    ];

    // Event types
    public const EVENT_OTP = 'otp';
    public const EVENT_ENQUIRY_RECEIVED = 'enquiry_received';
    public const EVENT_OFFER_CREATED = 'offer_created';
    public const EVENT_OFFER_ACCEPTED = 'offer_accepted';
    public const EVENT_OFFER_REJECTED = 'offer_rejected';
    public const EVENT_QUOTATION_CREATED = 'quotation_created';
    public const EVENT_QUOTATION_REJECTED = 'quotation_rejected';
    public const EVENT_PAYMENT_COMPLETE = 'payment_complete';
    public const EVENT_SURVEY_BOOKED = 'survey_booked';
    public const EVENT_MOUNTING_SCHEDULED = 'mounting_scheduled';
    public const EVENT_CAMPAIGN_STARTED = 'campaign_started';
    public const EVENT_BOOKING_COMPLETED = 'booking_completed';
    public const EVENT_REFUND_ISSUED = 'refund_issued';

    // Channels
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_WHATSAPP = 'whatsapp';
    public const CHANNEL_WEB = 'web';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name . ' ' . $template->channel);
            }
        });
    }

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeSystemDefaults($query)
    {
        return $query->where('is_system_default', true);
    }

    /**
     * Get all available event types
     */
    public static function getEventTypes(): array
    {
        return [
            self::EVENT_OTP => 'OTP',
            self::EVENT_ENQUIRY_RECEIVED => 'Enquiry Received',
            self::EVENT_OFFER_CREATED => 'Offer Created',
            self::EVENT_OFFER_ACCEPTED => 'Offer Accepted',
            self::EVENT_OFFER_REJECTED => 'Offer Rejected',
            self::EVENT_QUOTATION_CREATED => 'Quotation Created',
            self::EVENT_QUOTATION_REJECTED => 'Quotation Rejected',
            self::EVENT_PAYMENT_COMPLETE => 'Payment Complete',
            self::EVENT_SURVEY_BOOKED => 'Survey Booked',
            self::EVENT_MOUNTING_SCHEDULED => 'Mounting Scheduled',
            self::EVENT_CAMPAIGN_STARTED => 'Campaign Started',
            self::EVENT_BOOKING_COMPLETED => 'Booking Completed',
            self::EVENT_REFUND_ISSUED => 'Refund Issued',
        ];
    }

    /**
     * Get all available channels
     */
    public static function getChannels(): array
    {
        return [
            self::CHANNEL_EMAIL => 'Email',
            self::CHANNEL_SMS => 'SMS',
            self::CHANNEL_WHATSAPP => 'WhatsApp',
            self::CHANNEL_WEB => 'Web Notification',
        ];
    }

    /**
     * Get default placeholders for each event type
     */
    public static function getDefaultPlaceholders(string $eventType): array
    {
        $common = [
            '{{app_name}}' => 'Application name',
            '{{app_url}}' => 'Application URL',
            '{{current_date}}' => 'Current date',
            '{{current_time}}' => 'Current time',
        ];

        $eventSpecific = match ($eventType) {
            self::EVENT_OTP => [
                '{{otp_code}}' => 'OTP code',
                '{{user_name}}' => 'User name',
                '{{expiry_minutes}}' => 'OTP expiry minutes',
            ],
            self::EVENT_ENQUIRY_RECEIVED => [
                '{{enquiry_id}}' => 'Enquiry ID',
                '{{customer_name}}' => 'Customer name',
                '{{vendor_name}}' => 'Vendor name',
                '{{property_name}}' => 'Property name',
                '{{enquiry_date}}' => 'Enquiry date',
                '{{enquiry_url}}' => 'Enquiry detail URL',
            ],
            self::EVENT_OFFER_CREATED => [
                '{{offer_id}}' => 'Offer ID',
                '{{vendor_name}}' => 'Vendor name',
                '{{customer_name}}' => 'Customer name',
                '{{offer_amount}}' => 'Offer amount',
                '{{property_name}}' => 'Property name',
                '{{offer_url}}' => 'Offer detail URL',
                '{{expiry_date}}' => 'Offer expiry date',
            ],
            self::EVENT_OFFER_ACCEPTED => [
                '{{offer_id}}' => 'Offer ID',
                '{{customer_name}}' => 'Customer name',
                '{{vendor_name}}' => 'Vendor name',
                '{{offer_amount}}' => 'Offer amount',
                '{{acceptance_date}}' => 'Acceptance date',
                '{{offer_url}}' => 'Offer detail URL',
            ],
            self::EVENT_OFFER_REJECTED => [
                '{{offer_id}}' => 'Offer ID',
                '{{customer_name}}' => 'Customer name',
                '{{vendor_name}}' => 'Vendor name',
                '{{rejection_reason}}' => 'Rejection reason',
                '{{rejection_date}}' => 'Rejection date',
            ],
            self::EVENT_QUOTATION_CREATED => [
                '{{quotation_id}}' => 'Quotation ID',
                '{{customer_name}}' => 'Customer name',
                '{{vendor_name}}' => 'Vendor name',
                '{{quotation_amount}}' => 'Quotation amount',
                '{{quotation_url}}' => 'Quotation detail URL',
                '{{validity_date}}' => 'Quotation validity date',
            ],
            self::EVENT_QUOTATION_REJECTED => [
                '{{quotation_id}}' => 'Quotation ID',
                '{{customer_name}}' => 'Customer name',
                '{{rejection_reason}}' => 'Rejection reason',
            ],
            self::EVENT_PAYMENT_COMPLETE => [
                '{{payment_id}}' => 'Payment ID',
                '{{booking_id}}' => 'Booking ID',
                '{{customer_name}}' => 'Customer name',
                '{{payment_amount}}' => 'Payment amount',
                '{{payment_date}}' => 'Payment date',
                '{{payment_method}}' => 'Payment method',
                '{{invoice_url}}' => 'Invoice URL',
            ],
            self::EVENT_SURVEY_BOOKED => [
                '{{survey_id}}' => 'Survey ID',
                '{{booking_id}}' => 'Booking ID',
                '{{customer_name}}' => 'Customer name',
                '{{vendor_name}}' => 'Vendor name',
                '{{survey_date}}' => 'Survey date',
                '{{survey_time}}' => 'Survey time',
                '{{property_address}}' => 'Property address',
            ],
            self::EVENT_MOUNTING_SCHEDULED => [
                '{{mounting_id}}' => 'Mounting ID',
                '{{booking_id}}' => 'Booking ID',
                '{{customer_name}}' => 'Customer name',
                '{{vendor_name}}' => 'Vendor name',
                '{{mounting_date}}' => 'Mounting date',
                '{{mounting_time}}' => 'Mounting time',
                '{{property_address}}' => 'Property address',
            ],
            self::EVENT_CAMPAIGN_STARTED => [
                '{{campaign_id}}' => 'Campaign ID',
                '{{booking_id}}' => 'Booking ID',
                '{{customer_name}}' => 'Customer name',
                '{{campaign_start_date}}' => 'Campaign start date',
                '{{campaign_end_date}}' => 'Campaign end date',
                '{{property_name}}' => 'Property name',
            ],
            self::EVENT_BOOKING_COMPLETED => [
                '{{booking_id}}' => 'Booking ID',
                '{{customer_name}}' => 'Customer name',
                '{{vendor_name}}' => 'Vendor name',
                '{{completion_date}}' => 'Completion date',
                '{{property_name}}' => 'Property name',
                '{{booking_url}}' => 'Booking detail URL',
            ],
            self::EVENT_REFUND_ISSUED => [
                '{{refund_id}}' => 'Refund ID',
                '{{booking_id}}' => 'Booking ID',
                '{{customer_name}}' => 'Customer name',
                '{{refund_amount}}' => 'Refund amount',
                '{{refund_date}}' => 'Refund date',
                '{{refund_reason}}' => 'Refund reason',
                '{{refund_method}}' => 'Refund method',
            ],
            default => [],
        };

        return array_merge($common, $eventSpecific);
    }

    /**
     * Render template with placeholders
     */
    public function render(array $data): array
    {
        $subject = $this->subject;
        $body = $this->body;
        $htmlBody = $this->html_body;

        foreach ($data as $placeholder => $value) {
            // Ensure placeholder has correct format
            $placeholder = $this->formatPlaceholder($placeholder);
            
            $subject = str_replace($placeholder, $value ?? '', $subject ?? '');
            $body = str_replace($placeholder, $value ?? '', $body);
            $htmlBody = str_replace($placeholder, $value ?? '', $htmlBody ?? '');
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'html_body' => $htmlBody,
        ];
    }

    /**
     * Format placeholder to ensure correct syntax
     */
    protected function formatPlaceholder(string $placeholder): string
    {
        if (!str_starts_with($placeholder, '{{')) {
            $placeholder = '{{' . $placeholder;
        }
        if (!str_ends_with($placeholder, '}}')) {
            $placeholder = $placeholder . '}}';
        }
        return $placeholder;
    }

    /**
     * Get channel badge color
     */
    public function getChannelColorAttribute(): string
    {
        return match ($this->channel) {
            self::CHANNEL_EMAIL => 'primary',
            self::CHANNEL_SMS => 'success',
            self::CHANNEL_WHATSAPP => 'success',
            self::CHANNEL_WEB => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get event type label
     */
    public function getEventTypeLabelAttribute(): string
    {
        return self::getEventTypes()[$this->event_type] ?? $this->event_type;
    }

    /**
     * Get channel label
     */
    public function getChannelLabelAttribute(): string
    {
        return self::getChannels()[$this->channel] ?? $this->channel;
    }

    /**
     * Check if template can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->is_system_default;
    }

    /**
     * Duplicate template
     */
    public function duplicate(string $newName = null): self
    {
        $copy = $this->replicate();
        $copy->name = $newName ?? ($this->name . ' (Copy)');
        $copy->slug = null; // Will be auto-generated
        $copy->is_system_default = false;
        $copy->save();

        return $copy;
    }
}
