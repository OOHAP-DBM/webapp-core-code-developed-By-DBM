<?php

namespace Modules\Import\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class InventoryImportBatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_import_batches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'media_type',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
    ];

    /**
     * Get the staging records for this batch.
     */
    public function stagingRecords(): HasMany
    {
        return $this->hasMany(InventoryImportStaging::class, 'batch_id');
    }

    /**
     * Get vendor who initiated the import
     */
    public function vendor()
    {
        return $this->belongsTo(\App\Models\User::class, 'vendor_id');
    }

    /**
     * Scope to filter by status: uploaded
     */
    public function scopeUploaded(Builder $query): Builder
    {
        return $query->where('status', 'uploaded');
    }

    /**
     * Scope to filter by status: processing
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope to filter by status: processed
     */
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope to filter by status: approved
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to filter by status: completed
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter by status: failed
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to filter by media type
     */
    public function scopeByMediaType(Builder $query, string $mediaType): Builder
    {
        return $query->where('media_type', $mediaType);
    }

    /**
     * Scope to filter by vendor
     */
    public function scopeByVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Check if batch is being processed
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if batch processing is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if batch processing failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get error rate percentage
     */
    public function getErrorRatePercentage(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->invalid_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRatePercentage(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->valid_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Update batch status
     */
    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    /**
     * Mark batch as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
        ]);
    }

    /**
     * Mark batch as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
        ]);
    }

    /**
     * Mark batch as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
        ]);
    }

    /**
     * Mark batch as failed
     */
    public function markAsFailed(string $errorMessage = ''): void
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Update row counts
     */
    public function updateRowCounts(int $totalRows, int $validRows, int $invalidRows): void
    {
        $this->update([
            'total_rows' => $totalRows,
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
        ]);
    }
}
