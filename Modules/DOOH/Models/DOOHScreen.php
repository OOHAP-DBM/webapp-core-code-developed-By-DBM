<?php

namespace Modules\DOOH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DOOHScreen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dooh_screens';

    protected $fillable = [
        'vendor_id',
        'external_screen_id',
        'name',
        'description',
        'screen_type',
        'address',
        'city',
        'state',
        'country',
        'lat',
        'lng',
        'resolution',
        'screen_size',
        'width',
        'height',
        'slot_duration_seconds',
        'loop_duration_seconds',
        'slots_per_loop',
        'min_slots_per_day',
        'price_per_slot',
        'price_per_month',
        'minimum_booking_amount',
        'total_slots_per_day',
        'available_slots_per_day',
        'allowed_formats',
        'max_file_size_mb',
        'status',
        'sync_status',
        'last_synced_at',
        'sync_metadata',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'price_per_slot' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'minimum_booking_amount' => 'decimal:2',
        'allowed_formats' => 'array',
        'sync_metadata' => 'array',
        'last_synced_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    // Sync status constants
    const SYNC_STATUS_PENDING = 'pending';
    const SYNC_STATUS_SYNCED = 'synced';
    const SYNC_STATUS_FAILED = 'failed';

    /**
     * Get the vendor that owns the screen
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get packages for this screen
     */
    public function packages(): HasMany
    {
        return $this->hasMany(DOOHPackage::class, 'dooh_screen_id');
    }

    /**
     * Get bookings for this screen
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(DOOHBooking::class, 'dooh_screen_id');
    }

    /**
     * Get active packages
     */
    public function activePackages(): HasMany
    {
        return $this->packages()->where('is_active', true);
    }

    /**
     * Scope: Active screens
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: By vendor
     */
    public function scopeByVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope: By city
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope: Synced screens
     */
    public function scopeSynced($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_SYNCED);
    }

    /**
     * Check if screen is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if screen is synced
     */
    public function isSynced(): bool
    {
        return $this->sync_status === self::SYNC_STATUS_SYNCED;
    }

    /**
     * Get available slots for a specific date range
     */
    public function getAvailableSlots(string $startDate, string $endDate): int
    {
        // Calculate total slots already booked in this period
        $bookedSlots = $this->bookings()
            ->where('status', '!=', DOOHBooking::STATUS_CANCELLED)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->sum('slots_per_day');

        return max(0, $this->available_slots_per_day - $bookedSlots);
    }

    /**
     * Calculate slots per loop based on duration
     */
    public function calculateSlotsPerLoop(): int
    {
        if ($this->slot_duration_seconds > 0) {
            return intval($this->loop_duration_seconds / $this->slot_duration_seconds);
        }
        return 0;
    }

    /**
     * Get screen display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' - ' . $this->city;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUSPENDED => 'Suspended',
            default => 'Unknown',
        };
    }
}
