<?php

namespace App\Models;

use App\Traits\HasSnapshots;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use \Modules\DOOH\Models\DOOHScreen;    
class Hoarding extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasSnapshots, Auditable, InteractsWithMedia;

    protected $table = 'hoardings';

    protected $snapshotType = 'price_update';
    protected $snapshotOnCreate = false;
    protected $snapshotOnUpdate = true;

    protected $auditModule = 'hoarding';

    /**
     * ONLY parent-level price fields
     */
    protected $priceFields = [
        'base_monthly_price',
        'monthly_price',
        'weekly_price',
        'commission_percent',
    ];

    /* ===================== FILLABLE ===================== */

    protected $fillable = [

        /* Ownership */
        'vendor_id',

        /* Identity */
        'title',
        'slug',
        'name',
        'description',
        'hoarding_type',
        'category',

        /* Location (shared by OOH & DOOH) */
        'address',
        'city',
        'state',
        'locality',
        'pincode',
        'country',
        'latitude',
        'longitude',
        'geolocation_verified',
        'geolocation_source',
        'landmark',

        /* Visibility & Traffic */
        'visibility_start',
        'visibility_end',
        'facing_direction',
        'road_type',
        'traffic_type',
        'hoarding_visibility',
        'visibility_details',

        /* Audience */
        'expected_footfall',
        'expected_eyeball',
        'audience_types',

        /* Pricing (base only) */
        'base_monthly_price',
        'monthly_price',
        'weekly_price',
        'enable_weekly_booking',
        'commission_percent',
        'currency',

        /* Booking rules */
        'grace_period_days',
        'min_booking_months',
        'max_booking_months',
        'available_from',
        'available_to',
        'block_dates',

        /* Legal */
        'nagar_nigam_approved',
        'permit_number',
        'permit_valid_till',

        /* Workflow */
        'status',
        'current_step',
        'is_featured',

        /* Analytics */
        'view_count',
        'bookings_count',
        'last_booked_at',
    ];

    /* ===================== CASTS ===================== */

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'geolocation_verified' => 'boolean',

        'visibility_start' => 'datetime:H:i',
        'visibility_end' => 'datetime:H:i',
        'visibility_details' => 'array',

        'audience_types' => 'array',

        'base_monthly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'enable_weekly_booking' => 'boolean',

        'grace_period_days' => 'integer',
        'block_dates' => 'array',
        'available_from' => 'date',
        'available_to' => 'date',

        'nagar_nigam_approved' => 'boolean',
        'permit_valid_till' => 'date',

        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'bookings_count' => 'integer',
        'last_booked_at' => 'datetime',
    ];

    /* ===================== CONSTANTS ===================== */

    const TYPE_OOH  = 'ooh';
    const TYPE_DOOH = 'dooh';

    const STATUS_DRAFT            = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_ACTIVE           = 'active';
    const STATUS_INACTIVE         = 'inactive';
    const STATUS_SUSPENDED        = 'suspended';

    /* ===================== RELATIONSHIPS ===================== */

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function ooh(): HasOne
    {
        return $this->hasOne(OOHHoarding::class, 'hoarding_id');
    }

    public function doohScreen(): HasOne
    {
        return $this->hasOne(DOOHScreen::class, 'hoarding_id');
    }

    /* ===================== SCOPES ===================== */

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('hoarding_type', $type);
    }

    /* ===================== HELPERS ===================== */

    public function getGracePeriodDays(): int
    {
        return $this->grace_period_days ?? (int) config('booking.grace_period_days', 2);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
