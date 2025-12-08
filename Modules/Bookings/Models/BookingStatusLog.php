<?php

namespace Modules\Bookings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusLog extends Model
{
    const UPDATED_AT = null; // Only created_at

    protected $fillable = [
        'booking_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Helpers
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    public function getFormattedStatus(): string
    {
        $from = $this->from_status ? ucfirst(str_replace('_', ' ', $this->from_status)) : 'N/A';
        $to = ucfirst(str_replace('_', ' ', $this->to_status));
        
        return "{$from} → {$to}";
    }
}
