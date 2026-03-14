<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSBookingReminder extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'pos_booking_reminders';

    protected $fillable = [
        'pos_booking_id',
        'scheduled_at',
        'status',
        'sent_at',
        'created_by',
        'updated_by',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(POSBooking::class, 'pos_booking_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
