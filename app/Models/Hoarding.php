<?php

namespace App\Models;

use App\Traits\HasSnapshots;
use App\Traits\Auditable;
use App\Traits\HasDOOHSlots;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hoarding extends Model
{
    use HasFactory, SoftDeletes, HasSnapshots, Auditable, HasDOOHSlots;
    
    protected $snapshotType = 'price_update';
    protected $snapshotOnCreate = false; // Don't snapshot on create
    protected $snapshotOnUpdate = true;  // Only snapshot on update (for price changes)
    
    protected $auditModule = 'hoarding';
    protected $priceFields = ['weekly_price', 'monthly_price'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'title',
        'description',
        'address',
        'lat',
        'lng',
        'weekly_price',
        'monthly_price',
        'enable_weekly_booking',
        'type',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'weekly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'enable_weekly_booking' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Hoarding types
     */
    const TYPE_BILLBOARD = 'billboard';
    const TYPE_DIGITAL = 'digital';
    const TYPE_TRANSIT = 'transit';
    const TYPE_STREET_FURNITURE = 'street_furniture';
    const TYPE_WALLSCAPE = 'wallscape';
    const TYPE_MOBILE = 'mobile';

    /**
     * Hoarding statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Get the vendor that owns the hoarding.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the geo fence for this hoarding.
     */
    public function geo(): HasOne
    {
        return $this->hasOne(HoardingGeo::class);
    }

    /**
     * Scope a query to only include active hoardings.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to filter by vendor.
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to search by title or address.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by location radius (basic distance filter).
     */
    public function scopeNearLocation($query, $lat, $lng, $radiusKm = 10)
    {
        // Simple bounding box calculation (not precise but fast)
        $latDelta = $radiusKm / 111; // 1 degree latitude ≈ 111km
        $lngDelta = $radiusKm / (111 * cos(deg2rad($lat)));

        return $query->whereBetween('lat', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('lng', [$lng - $lngDelta, $lng + $lngDelta]);
    }

    /**
     * Get all available hoarding types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_BILLBOARD => 'Billboard',
            self::TYPE_DIGITAL => 'Digital Screen',
            self::TYPE_TRANSIT => 'Transit Advertising',
            self::TYPE_STREET_FURNITURE => 'Street Furniture',
            self::TYPE_WALLSCAPE => 'Wallscape',
            self::TYPE_MOBILE => 'Mobile Billboard',
        ];
    }

    /**
     * Get all available hoarding statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    /**
     * Get the formatted type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * Get the formatted status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Check if hoarding is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if hoarding supports weekly booking.
     */
    public function supportsWeeklyBooking(): bool
    {
        return $this->enable_weekly_booking && $this->weekly_price !== null;
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
}
