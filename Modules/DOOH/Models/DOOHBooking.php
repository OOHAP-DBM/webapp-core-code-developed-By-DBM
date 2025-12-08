<?php

namespace Modules\DOOH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Carbon\Carbon;

class DOOHBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dooh_bookings';

    protected $fillable = [
        'dooh_screen_id',
        'dooh_package_id',
        'customer_id',
        'vendor_id',
        'booking_number',
        'start_date',
        'end_date',
        'duration_months',
        'duration_days',
        'slots_per_day',
        'total_slots',
        'slot_frequency_minutes',
        'content_files',
        'content_status',
        'content_rejection_reason',
        'content_approved_at',
        'content_approved_by',
        'package_price',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'payment_status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_authorized_at',
        'payment_captured_at',
        'hold_expiry_at',
        'refund_id',
        'refund_amount',
        'refunded_at',
        'refund_reason',
        'status',
        'confirmed_at',
        'campaign_started_at',
        'campaign_ended_at',
        'cancelled_at',
        'cancellation_reason',
        'booking_snapshot',
        'customer_notes',
        'vendor_notes',
        'admin_notes',
        'survey_required',
        'survey_status',
        'survey_completed_at',
        'survey_data',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'content_files' => 'array',
        'booking_snapshot' => 'array',
        'survey_data' => 'array',
        'package_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'content_approved_at' => 'datetime',
        'payment_authorized_at' => 'datetime',
        'payment_captured_at' => 'datetime',
        'hold_expiry_at' => 'datetime',
        'refunded_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'campaign_started_at' => 'datetime',
        'campaign_ended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'survey_completed_at' => 'datetime',
        'survey_required' => 'boolean',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PAYMENT_PENDING = 'payment_pending';
    const STATUS_PAYMENT_AUTHORIZED = 'payment_authorized';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CONTENT_PENDING = 'content_pending';
    const STATUS_CONTENT_APPROVED = 'content_approved';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Payment status constants
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_AUTHORIZED = 'authorized';
    const PAYMENT_STATUS_CAPTURED = 'captured';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    // Content status constants
    const CONTENT_STATUS_PENDING = 'pending';
    const CONTENT_STATUS_APPROVED = 'approved';
    const CONTENT_STATUS_REJECTED = 'rejected';

    // Survey status constants
    const SURVEY_STATUS_NOT_REQUIRED = 'not_required';
    const SURVEY_STATUS_PENDING = 'pending';
    const SURVEY_STATUS_COMPLETED = 'completed';

    /**
     * Get the screen for this booking
     */
    public function screen(): BelongsTo
    {
        return $this->belongsTo(DOOHScreen::class, 'dooh_screen_id');
    }

    /**
     * Get the package for this booking
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(DOOHPackage::class, 'dooh_package_id');
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the vendor
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the user who approved content
     */
    public function contentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_approved_by');
    }

    /**
     * Scope: By customer
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: By vendor
     */
    public function scopeByVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope: Active bookings
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope: Payment authorized
     */
    public function scopePaymentAuthorized($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_AUTHORIZED);
    }

    /**
     * Check if payment hold has expired
     */
    public function isPaymentHoldExpired(): bool
    {
        if (!$this->hold_expiry_at) {
            return false;
        }
        
        return Carbon::now()->greaterThan($this->hold_expiry_at);
    }

    /**
     * Check if booking is within cancellation window (30 min)
     */
    public function isWithinCancellationWindow(): bool
    {
        if (!$this->payment_captured_at) {
            return false;
        }
        
        $minutesElapsed = Carbon::now()->diffInMinutes($this->payment_captured_at);
        return $minutesElapsed <= 30;
    }

    /**
     * Check if content is approved
     */
    public function isContentApproved(): bool
    {
        return $this->content_status === self::CONTENT_STATUS_APPROVED;
    }

    /**
     * Check if campaign is active
     */
    public function isCampaignActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE 
            && Carbon::now()->between($this->start_date, $this->end_date);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PAYMENT_PENDING => 'Payment Pending',
            self::STATUS_PAYMENT_AUTHORIZED => 'Payment Authorized',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CONTENT_PENDING => 'Content Pending',
            self::STATUS_CONTENT_APPROVED => 'Content Approved',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'Pending',
            self::PAYMENT_STATUS_AUTHORIZED => 'Authorized',
            self::PAYMENT_STATUS_CAPTURED => 'Captured',
            self::PAYMENT_STATUS_FAILED => 'Failed',
            self::PAYMENT_STATUS_REFUNDED => 'Refunded',
            default => 'Unknown',
        };
    }

    /**
     * Generate unique booking number
     */
    public static function generateBookingNumber(): string
    {
        $prefix = 'DOOH';
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        
        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get value from booking snapshot
     */
    public function getSnapshotValue(string $key, $default = null)
    {
        return data_get($this->booking_snapshot, $key, $default);
    }
}
