<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Models\User;
use App\Models\Hoarding;


use \App\Traits\Auditable;

class POSBooking extends Model
{
    use SoftDeletes, Auditable;

    /**
     * Audit module for logs
     */
    protected $auditModule = 'pos';

    protected $table = 'pos_bookings';

    protected $fillable = [
        'vendor_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_gstin',
        'booking_type',
        'hoarding_id',
        'dooh_slot_id',
        'start_date',
        'end_date',
        'duration_type',
        'duration_days',
        'base_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payment_mode',
        'payment_status',
        'paid_amount',
         'payment_received_at',
        'payment_reference',
        'payment_notes',
        'hold_expiry_at',
        'reminder_count',
        'last_reminder_at',
        'credit_note_number',
        'credit_note_date',
        'credit_note_due_date',
        'credit_note_status',
        'status',
        'invoice_number',
        'invoice_date',
        'invoice_path',
        'auto_approved',
        'approved_at',
        'approved_by',
        'booking_snapshot',
        'notes',
        'cancellation_reason',
        'confirmed_at',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'credit_note_date' => 'date',
        'credit_note_due_date' => 'date',
        'invoice_date' => 'date',
        'base_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'auto_approved' => 'boolean',
        'booking_snapshot' => 'array',
        'approved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Payment status constants
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_PARTIAL = 'partial';
    const PAYMENT_STATUS_CREDIT = 'credit';

    // Payment mode constants
    const PAYMENT_MODE_CASH = 'cash';
    const PAYMENT_MODE_CREDIT_NOTE = 'credit_note';
    const PAYMENT_MODE_ONLINE = 'online';
    const PAYMENT_MODE_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_MODE_CHEQUE = 'cheque';

    // Credit note status constants
    const CREDIT_NOTE_STATUS_ACTIVE = 'active';
    const CREDIT_NOTE_STATUS_CANCELLED = 'cancelled';
    const CREDIT_NOTE_STATUS_SETTLED = 'settled';

    /**
     * Get the vendor who created this booking
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the customer (if registered user)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the hoarding
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

        /**
     * Get all hoardings linked to this booking
     */
    public function hoardings()
    {
        return $this->belongsToMany(
            Hoarding::class,
            'pos_booking_hoardings',
            'pos_booking_id',
            'hoarding_id'
        )->withPivot(['hoarding_price', 'hoarding_discount', 'hoarding_tax', 'hoarding_total', 'start_date', 'end_date', 'duration_days', 'status'])
            ->withTimestamps();
    }

    /**
     * Get all booking hoarding records with details
     */
    public function bookingHoardings()
    {
        return $this->hasMany(POSBookingHoarding::class, 'pos_booking_id');
    }


    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if booking is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if payment is completed
     */
    public function isPaymentComplete(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Check if payment is via credit note
     */
    public function isCreditNote(): bool
    {
        return $this->payment_mode === self::PAYMENT_MODE_CREDIT_NOTE;
    }

    /**
     * Check if credit note is active
     */
    public function isCreditNoteActive(): bool
    {
        return $this->credit_note_status === self::CREDIT_NOTE_STATUS_ACTIVE;
    }

    /**
     * Get the balance amount due
     */
    public function getBalanceAmount(): float
    {
        return (float) ($this->total_amount - $this->paid_amount);
    }

    /**
     * Check if booking has invoice
     */
    public function hasInvoice(): bool
    {
        return !empty($this->invoice_number);
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'POS-INV-';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        
        return $prefix . $date . '-' . $random;
    }

    /**
     * Generate unique credit note number
     */
    public static function generateCreditNoteNumber(): string
    {
        $prefix = 'CN-';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        
        return $prefix . $date . '-' . $random;
    }

    /**
     * Scope: Filter by vendor
     */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by payment status
     */
    public function scopeByPaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope: Active bookings
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_ACTIVE]);
    }

    /**
     * Scope: Unpaid bookings
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', [self::PAYMENT_STATUS_UNPAID, self::PAYMENT_STATUS_PARTIAL]);
    }

    /**
     * Scope: Credit note bookings
     */
    public function scopeCreditNotes($query)
    {
        return $query->where('payment_mode', self::PAYMENT_MODE_CREDIT_NOTE)
            ->where('credit_note_status', self::CREDIT_NOTE_STATUS_ACTIVE);
    }
}
