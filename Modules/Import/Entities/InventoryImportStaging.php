<?php

namespace Modules\Import\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InventoryImportStaging extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_import_staging';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'batch_id',
        'vendor_id',
        'media_type',
        'code',
        'city',
        'category',
        'address',
        'locality',
        'landmark',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'width',
        'height',
        'measurement_unit',
        'lighting_type',
        'screen_type',
        'image_name',
        'base_monthly_price',
        'monthly_price',
        'weekly_price_1',
        'weekly_price_2',
        'weekly_price_3',
        'price_per_slot',
        'slot_duration_seconds',
        'screen_run_time',
        'total_slots_per_day',
        'min_slots_per_day',
        'min_booking_duration',
        'minimum_booking_amount',
        'commission_percent',
        'graphics_charge',
        'survey_charge',
        'printing_charge',
        'mounting_charge',
        'remounting_charge',
        'lighting_charge',
        'discount_type',
        'discount_value',
        'availability',
        'currency',
        'available_from',
        'available_to',
        'extra_attributes',
        'status',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'base_monthly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'weekly_price_1' => 'decimal:2',
        'weekly_price_2' => 'decimal:2',
        'weekly_price_3' => 'decimal:2',
        'price_per_slot' => 'decimal:2',
        'minimum_booking_amount' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'graphics_charge' => 'decimal:2',
        'survey_charge' => 'decimal:2',
        'printing_charge' => 'decimal:2',
        'mounting_charge' => 'decimal:2',
        'remounting_charge' => 'decimal:2',
        'lighting_charge' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'slot_duration_seconds' => 'integer',
        'screen_run_time' => 'integer',
        'total_slots_per_day' => 'integer',
        'min_slots_per_day' => 'integer',
        'min_booking_duration' => 'integer',
        'available_from' => 'date',
        'available_to' => 'date',
        'extra_attributes' => 'array',
    ];

    /**
     * Get the batch that owns the staging record.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryImportBatch::class, 'batch_id');
    }

    /**
     * Get the vendor who created this record
     */
    public function vendor()
    {
        return $this->belongsTo(\App\Models\User::class, 'vendor_id');
    }

    /**
     * Scope to filter valid records
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', 'valid');
    }

    /**
     * Scope to filter invalid records
     */
    public function scopeInvalid(Builder $query): Builder
    {
        return $query->where('status', 'invalid');
    }

    /**
     * Scope to filter by batch
     */
    public function scopeByBatch(Builder $query, int $batchId): Builder
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope to filter by vendor
     */
    public function scopeByVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope to filter by media type
     */
    public function scopeByMediaType(Builder $query, string $mediaType): Builder
    {
        return $query->where('media_type', $mediaType);
    }

    /**
     * Scope to filter by code
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to filter by city
     */
    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to get records with errors
     */
    public function scopeWithErrors(Builder $query): Builder
    {
        return $query->invalid()->whereNotNull('error_message');
    }

    /**
     * Check if record is valid
     */
    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    /**
     * Check if record is invalid
     */
    public function isInvalid(): bool
    {
        return $this->status === 'invalid';
    }

    /**
     * Mark record as valid
     */
    public function markAsValid(): void
    {
        $this->update([
            'status' => 'valid',
            'error_message' => null,
        ]);
    }

    /**
     * Mark record as invalid with error message
     */
    public function markAsInvalid(string $errorMessage): void
    {
        $this->update([
            'status' => 'invalid',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get extra attributes with fallback
     */
    public function getExtraAttribute($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes) && $this->attributes[$key] !== null) {
            return $this->{$key};
        }

        $attributes = $this->extra_attributes ?? [];
        return $attributes[$key] ?? $default;
    }

    /**
     * Set extra attributes
     */
    public function setExtraAttribute($key, $value): void
    {
        $attributes = $this->extra_attributes ?? [];
        $attributes[$key] = $value;
        $this->extra_attributes = $attributes;
        $this->save();
    }

    /**
     * Get dimensions as formatted string
     */
    public function getDimensionsFormatted(): ?string
    {
        if ($this->width && $this->height) {
            return "{$this->width}x{$this->height}";
        }

        return null;
    }
}
