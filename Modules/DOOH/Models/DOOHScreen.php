<?php

namespace Modules\DOOH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Hoarding;
use App\Models\User;

class DOOHScreen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dooh_screens';

    protected $fillable = [

        /* FK */
        'hoarding_id',
        'vendor_id',

        /* External sync */
        'external_screen_id',
        'sync_status',
        'last_synced_at',
        'sync_metadata',

        /* Screen identity */
        'screen_type', // LED, LCD, Projection
        'height',
        'width',
        /* Resolution & size */
        'resolution_width',
        'resolution_height',
        'screen_size',
        'measurement_unit',

        /* Slot logic */
        'slot_duration_seconds',
        'loop_duration_seconds',
        'slots_per_loop',
        'total_slots_per_day',
        'available_slots_per_day',
        'min_slots_per_day',

        /* Pricing */
        'price_per_slot',
        // 'price_per_slot',
        'display_price_per_30s',
        'minimum_booking_amount',

        'base_monthly_price',
        'monthly_price',
        'weekly_price',

        /* Media constraints */
        'allowed_formats',
        'max_file_size_mb',
        'video_length',

        /* Offers */
        'offer_discount',
        'long_term_offers',
        'services_included',

        /* Graphics */
        'graphics_included',
        'graphics_price',

        /* Commission */
        'commission_percent',

        /* Workflow */
        'status',
    ];

    protected $casts = [
        'resolution_width' => 'integer',
        'resolution_height' => 'integer',
        'width' => 'integer',
        'height' => 'integer',

        'slot_duration_seconds' => 'integer',
        'loop_duration_seconds' => 'integer',
        'slots_per_loop' => 'integer',
        'total_slots_per_day' => 'integer',
        'available_slots_per_day' => 'integer',
        'min_slots_per_day' => 'integer',

        'price_per_slot' => 'decimal:2',
        'display_price_per_30s' => 'decimal:2',
        // 'price_per_slot' => 'decimal:2',
        'minimum_booking_amount' => 'decimal:2',

        'base_monthly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',

        'offer_discount' => 'boolean',
        'graphics_included' => 'boolean',

        'graphics_price' => 'decimal:2',
        'commission_percent' => 'decimal:2',

        'allowed_formats' => 'array',
        'long_term_offers' => 'array',
        'services_included' => 'array',

        'sync_metadata' => 'array',
        'last_synced_at' => 'datetime',
    ];

    /* ================= CONSTANTS ================= */

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    const SYNC_STATUS_PENDING = 'pending';
    const SYNC_STATUS_SYNCED = 'synced';
    const SYNC_STATUS_FAILED = 'failed';

    /* ================= RELATIONSHIPS ================= */

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class, 'hoarding_id');
    }

    // public function vendor(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'vendor_id');
    // }
    public function vendor()
    {
        return $this->hoarding?->vendor();
    }


    public function slots(): HasMany
    {
        return $this->hasMany(DOOHSlot::class, 'dooh_screen_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(DOOHPackage::class, 'dooh_screen_id');
    }
    public function doohPackages(): HasMany
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
    protected function mediaUrl(string $path): string
    {
        return asset(
            str_starts_with($path, 'storage/')
                ? $path
                : 'storage/' . ltrim($path, '/')
        );
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        $media = $this->media
            ->sortByDesc('is_primary')
            ->first();

        return $media ? $this->mediaUrl($media->file_path) : null;
    }

    public function brandLogos(): HasMany
    {
        return $this->hasMany(DOOHScreenBrandLogo::class, 'dooh_screen_id');
    }
    public function doohBrandLogos(): HasMany
    {
        return $this->hasMany(DOOHScreenBrandLogo::class, 'dooh_screen_id');
    }

    /* ================= HELPERS ================= */

    public function calculateSlotsPerLoop(): int
    {
        if ($this->slot_duration_seconds > 0) {
            return (int) floor($this->loop_duration_seconds / $this->slot_duration_seconds);
        }

        return 0;
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->screen_type})";
    }
    /**
     * Attribute relationships
     */
    public function categoryAttribute()
    {
        return $this->belongsTo(\Modules\Hoardings\Models\HoardingAttribute::class, 'category_id');
    }
}
