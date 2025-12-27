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
        'locality',
        'resolution',
        'screen_size',
        'width',
        'height',
        'measurement_unit',
        'calculated_area_sqft',
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
        'pincode',

        // Step 2 & 3 Pricing & Offering Fields
        'nagar_nigam_approved',
        'block_dates',
        'grace_period',
        'audience_types',
        'visible_from',
        'located_at',
        'hoarding_visibility',
        'visibility_details',
        'display_price_per_30s',
        'video_length',
        'base_monthly_price', // Added to match form
        'monthly_price',
        'weekly_price',
        'offer_discount',
        'long_term_offers',   // Added for DOOH Campaign Packages
        'services_included',
        'graphics_included',  // Added to match form logic
        'graphics_price',     // Added to match form logic
        'survey_charge',      // Added to match form logic
        'commission_percent',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'nagar_nigam_approved' => 'boolean',
        'block_dates' => 'array',
        'grace_period' => 'boolean',
        'audience_types' => 'array',
        'visible_from' => 'array',
        'located_at' => 'array',
        'visibility_details' => 'array',
        'display_price_per_30s' => 'decimal:2',
        'video_length' => 'integer',
        'base_monthly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'offer_discount' => 'boolean',
        'long_term_offers' => 'array', // Crucial for storing dynamic packages
        'services_included' => 'array',
        'graphics_included' => 'boolean',
        'graphics_price' => 'decimal:2',
        'survey_charge' => 'decimal:2',
        'price_per_slot' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'minimum_booking_amount' => 'decimal:2',
        'allowed_formats' => 'array',
        'sync_metadata' => 'array',
        'last_synced_at' => 'datetime',
       
    ];

    // --- Status Constants ---
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const SYNC_STATUS_PENDING = 'pending';
    const SYNC_STATUS_SYNCED = 'synced';
    const SYNC_STATUS_FAILED = 'failed';

    // --- Relationships ---

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function brandLogos(): HasMany
    {
        return $this->hasMany(DOOHScreenBrandLogo::class, 'dooh_screen_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(DOOHSlot::class, 'dooh_screen_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(DOOHPackage::class, 'dooh_screen_id');
    }
    public function bookings(): HasMany
    {
        return $this->hasMany(DOOHBooking::class, 'dooh_screen_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(DOOHScreenMedia::class, 'dooh_screen_id');
    }

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    // --- Logic & Helpers ---

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} - {$this->city}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUSPENDED => 'Suspended',
            default => 'Unknown',
        };
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
}