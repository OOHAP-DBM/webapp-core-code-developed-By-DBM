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
        'width',
        'height',
        'image_name',
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
        'width' => 'decimal:2',
        'height' => 'decimal:2',
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
