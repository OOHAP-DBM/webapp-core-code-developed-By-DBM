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
use \Modules\Hoardings\Models\OOHHoarding;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Hoardings\Models\HoardingPackage;
use Modules\Hoardings\Models\HoardingBrandLogo;

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
        'graphics_charge',
        'survey_charge',
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
        'survey_charge ',
        'graphics_charge ',
        'graphics_included',


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

        // 'visibility_start' => 'datetime:H:i',
        // 'visibility_end' => 'datetime:H:i',
        'visibility_details' => 'array',

        'audience_types' => 'array',

        'base_monthly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'enable_weekly_booking' => 'boolean',
        'survey_charge' => 'decimal:2',
        'graphics_charge' => 'decimal:2',
        'graphics_included' => 'boolean',

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

    public function scopeByType($query, string $type)
    {
        return $query->where('hoarding_type', $type);
    }

    /* ===================== HELPERS ===================== */

  
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
    public function doohScreens()
    {
        return $this->hasMany(\Modules\DOOH\Models\DOOHScreen::class);
    }
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'hoarding_id');
    }
    public function oohPackages()
    {
        return $this->hasMany(HoardingPackage::class);
    }

    const TYPE_BILLBOARD = 'billboard';
    const TYPE_DIGITAL = 'digital';
    const TYPE_TRANSIT = 'transit';
    const TYPE_STREET_FURNITURE = 'street_furniture';
    const TYPE_WALLSCAPE = 'wallscape';
    const TYPE_MOBILE = 'mobile';

    /**
     * Get the geo fence for this hoarding.
     */
    public function geo(): HasOne
    {
        return $this->hasOne(HoardingGeo::class);
    }

    /**
     * Get all enquiries for this hoarding.
     */
    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }

    /**
     * Get all maintenance blocks for this hoarding (PROMPT 102)
     */
    public function maintenanceBlocks()
    {
        return $this->hasMany(MaintenanceBlock::class);
    }

    // /**
    //  * Scope a query to filter by vendor.
    //  */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

  

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if hoarding supports weekly booking.
     */
    public function supportsWeeklyBooking(): bool
    {
        return $this->enable_weekly_booking && $this->weekly_price !== null;
    }

    /**
     * Register media collections for hoarding images.
     */
    public function registerMediaCollections(): void
    {
        // Hero/Primary Image - Single file, auto-compress
        $this->addMediaCollection('hero_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
            ->maxFilesize(10 * 1024 * 1024) // 10MB
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(300)
                    ->height(200)
                    ->sharpen(10)
                    ->nonQueued();

                $this->addMediaConversion('preview')
                    ->width(800)
                    ->height(600)
                    ->sharpen(10)
                    ->nonQueued();

                $this->addMediaConversion('large')
                    ->width(1920)
                    ->height(1080)
                    ->sharpen(10)
                    ->nonQueued();
            });

        // Night View Image - Single file
        $this->addMediaCollection('night_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
            ->maxFilesize(10 * 1024 * 1024)
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(300)
                    ->height(200)
                    ->sharpen(10)
                    ->nonQueued();

                $this->addMediaConversion('preview')
                    ->width(800)
                    ->height(600)
                    ->sharpen(10)
                    ->nonQueued();
            });

        // Gallery/Angle Photos - Multiple files
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
            ->maxFilesize(10 * 1024 * 1024)
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(300)
                    ->height(200)
                    ->sharpen(10)
                    ->nonQueued();

                $this->addMediaConversion('preview')
                    ->width(800)
                    ->height(600)
                    ->sharpen(10)
                    ->nonQueued();
            });

        // Size/Dimension Overlay Image - Single file
        $this->addMediaCollection('size_overlay')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/svg+xml'])
            ->maxFilesize(5 * 1024 * 1024)
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(300)
                    ->height(200)
                    ->nonQueued();
            });
    }


    public function brandLogos()
    {
        return $this->hasMany(HoardingBrandLogo::class)
            ->orderBy('sort_order');
    }
    /**
     * Get hero image URL (with fallback to primary_image column if exists).
     */
    public function getHeroImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('hero_image');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get hero image thumbnail URL.
     */
    public function getHeroImageThumbAttribute(): ?string
    {
        $media = $this->getFirstMedia('hero_image');
        return $media ? $media->getUrl('thumb') : null;
    }

    /**
     * Get night image URL.
     */
    public function getNightImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('night_image');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get all gallery images.
     */
    public function getGalleryImagesAttribute()
    {
        return $this->getMedia('gallery');
    }

    /**
     * Get size overlay image URL.
     */
    public function getSizeOverlayUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('size_overlay');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Calculate Haversine distance to a point (in kilometers)
     */
    public function haversineDistance(float $lat, float $lng): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($this->lat);
        $lngFrom = deg2rad($this->lng);
        $latTo = deg2rad($lat);
        $lngTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * Get the grace period days for this hoarding
     * Returns vendor-specific grace period or admin default
     */
    public function getGracePeriodDays(): int
    {
        return $this->grace_period_days ?? (int) config('booking.grace_period_days', env('BOOKING_GRACE_PERIOD_DAYS', 2));
    }

    /**
     * Get the earliest allowed start date for campaigns
     * This enforces the grace period to prevent last-minute bookings
     */
    public function getEarliestAllowedStartDate(): \Carbon\Carbon
    {
        return \Carbon\Carbon::today()->addDays($this->getGracePeriodDays());
    }

    /**
     * Validate if a start date is within the allowed grace period
     */
    public function isStartDateAllowed(\Carbon\Carbon $startDate): bool
    {
        return $startDate->greaterThanOrEqualTo($this->getEarliestAllowedStartDate());
    }

    /**
     * Get validation message for invalid start dates
     */
    public function getGracePeriodValidationMessage(): string
    {
        $days = $this->getGracePeriodDays();
        $earliestDate = $this->getEarliestAllowedStartDate()->format('d M Y');

        return "Campaign start date must be at least {$days} day(s) from today. Earliest allowed date: {$earliestDate}";
    }

    /**
     * Attribute relationships
     */
    public function categoryAttribute()
    {
        return $this->belongsTo(\App\Models\HoardingAttribute::class, 'category_id');
    }

    public function materialAttribute()
    {
        return $this->belongsTo(\App\Models\HoardingAttribute::class, 'material_id');
    }

    public function lightingAttribute()
    {
        return $this->belongsTo(\App\Models\HoardingAttribute::class, 'lighting_id');
    }
}
