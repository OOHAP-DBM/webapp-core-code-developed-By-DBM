<?php

namespace Modules\DOOH\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Support\Facades\Storage;

/**
 * DOOH Creative Model
 * PROMPT 67: Manages digital creative assets for DOOH campaigns
 */
class DOOHCreative extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dooh_creatives';

    protected $fillable = [
        'customer_id',
        'booking_id',
        'dooh_screen_id',
        'creative_name',
        'description',
        'creative_type',
        'file_path',
        'file_url',
        'original_filename',
        'mime_type',
        'file_size_bytes',
        'resolution',
        'width_pixels',
        'height_pixels',
        'duration_seconds',
        'fps',
        'codec',
        'bitrate_kbps',
        'validation_status',
        'rejection_reason',
        'validation_notes',
        'validated_by',
        'validated_at',
        'validation_results',
        'format_valid',
        'resolution_valid',
        'duration_valid',
        'file_size_valid',
        'content_policy_valid',
        'is_active',
        'total_schedules',
        'first_scheduled_at',
        'last_scheduled_at',
        'processing_status',
        'processing_error',
        'thumbnail_path',
        'preview_url',
        'metadata',
        'tags',
        'status',
        'uploaded_at',
        'archived_at',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'width_pixels' => 'integer',
        'height_pixels' => 'integer',
        'duration_seconds' => 'integer',
        'fps' => 'decimal:2',
        'bitrate_kbps' => 'integer',
        'validated_at' => 'datetime',
        'validation_results' => 'array',
        'format_valid' => 'boolean',
        'resolution_valid' => 'boolean',
        'duration_valid' => 'boolean',
        'file_size_valid' => 'boolean',
        'content_policy_valid' => 'boolean',
        'is_active' => 'boolean',
        'total_schedules' => 'integer',
        'first_scheduled_at' => 'datetime',
        'last_scheduled_at' => 'datetime',
        'metadata' => 'array',
        'tags' => 'array',
        'uploaded_at' => 'datetime',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants
    const TYPE_VIDEO = 'video';
    const TYPE_IMAGE = 'image';
    const TYPE_HTML5 = 'html5';
    const TYPE_GIF = 'gif';

    const VALIDATION_PENDING = 'pending';
    const VALIDATION_VALIDATING = 'validating';
    const VALIDATION_APPROVED = 'approved';
    const VALIDATION_REJECTED = 'rejected';
    const VALIDATION_REVISION_REQUIRED = 'revision_required';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_DELETED = 'deleted';

    const PROCESSING_PENDING = 'pending';
    const PROCESSING_PROCESSING = 'processing';
    const PROCESSING_COMPLETED = 'completed';
    const PROCESSING_FAILED = 'failed';

    // Validation rules (configurable via settings)
    const MAX_FILE_SIZE_MB = 500;
    const MAX_VIDEO_DURATION = 60; // seconds
    const MIN_VIDEO_DURATION = 5;
    const ALLOWED_VIDEO_FORMATS = ['mp4', 'mov', 'avi', 'webm'];
    const ALLOWED_IMAGE_FORMATS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    const STANDARD_RESOLUTIONS = [
        '1920x1080', '1080x1920', // Full HD
        '3840x2160', '2160x3840', // 4K
        '1280x720', '720x1280',   // HD
        '2560x1440', '1440x2560', // 2K
    ];

    /**
     * Relationships
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function doohScreen(): BelongsTo
    {
        return $this->belongsTo(DOOHScreen::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DOOHCreativeSchedule::class, 'creative_id');
    }

    public function activeSchedules(): HasMany
    {
        return $this->hasMany(DOOHCreativeSchedule::class, 'creative_id')
            ->whereIn('status', [
                DOOHCreativeSchedule::STATUS_APPROVED,
                DOOHCreativeSchedule::STATUS_ACTIVE
            ]);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('is_active', true);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeApproved($query)
    {
        return $query->where('validation_status', self::VALIDATION_APPROVED);
    }

    public function scopePendingValidation($query)
    {
        return $query->where('validation_status', self::VALIDATION_PENDING);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('creative_type', $type);
    }

    public function scopeForScreen($query, int $screenId)
    {
        return $query->where('dooh_screen_id', $screenId);
    }

    /**
     * Helper methods
     */
    public function isApproved(): bool
    {
        return $this->validation_status === self::VALIDATION_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->validation_status === self::VALIDATION_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->validation_status === self::VALIDATION_REJECTED;
    }

    public function isVideo(): bool
    {
        return $this->creative_type === self::TYPE_VIDEO;
    }

    public function isImage(): bool
    {
        return $this->creative_type === self::TYPE_IMAGE;
    }

    public function isProcessed(): bool
    {
        return $this->processing_status === self::PROCESSING_COMPLETED;
    }

    public function canBeScheduled(): bool
    {
        return $this->isApproved() 
            && $this->isProcessed() 
            && $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get file size in human-readable format
     */
    public function getFileSizeAttribute(): string
    {
        $bytes = $this->file_size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get full file URL
     */
    public function getFullUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }

    /**
     * Get validation status badge color
     */
    public function getValidationStatusColorAttribute(): string
    {
        return match($this->validation_status) {
            self::VALIDATION_APPROVED => 'success',
            self::VALIDATION_PENDING => 'warning',
            self::VALIDATION_VALIDATING => 'info',
            self::VALIDATION_REJECTED => 'danger',
            self::VALIDATION_REVISION_REQUIRED => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get validation status label
     */
    public function getValidationStatusLabelAttribute(): string
    {
        return match($this->validation_status) {
            self::VALIDATION_APPROVED => 'Approved',
            self::VALIDATION_PENDING => 'Pending Review',
            self::VALIDATION_VALIDATING => 'Validating',
            self::VALIDATION_REJECTED => 'Rejected',
            self::VALIDATION_REVISION_REQUIRED => 'Revision Required',
            default => 'Unknown',
        };
    }

    /**
     * Validate creative format
     */
    public function validateFormat(): bool
    {
        $extension = strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
        
        if ($this->isVideo()) {
            return in_array($extension, self::ALLOWED_VIDEO_FORMATS);
        } elseif ($this->isImage()) {
            return in_array($extension, self::ALLOWED_IMAGE_FORMATS);
        }
        
        return false;
    }

    /**
     * Validate file size
     */
    public function validateFileSize(): bool
    {
        $maxBytes = self::MAX_FILE_SIZE_MB * 1024 * 1024;
        return $this->file_size_bytes <= $maxBytes;
    }

    /**
     * Validate video duration
     */
    public function validateDuration(): bool
    {
        if (!$this->isVideo()) {
            return true; // Images don't have duration
        }
        
        return $this->duration_seconds >= self::MIN_VIDEO_DURATION 
            && $this->duration_seconds <= self::MAX_VIDEO_DURATION;
    }

    /**
     * Validate resolution
     */
    public function validateResolution(?DOOHScreen $screen = null): bool
    {
        if ($screen && $screen->resolution) {
            // Check if creative matches screen resolution
            return $this->resolution === $screen->resolution;
        }
        
        // Check if resolution is in standard list
        return in_array($this->resolution, self::STANDARD_RESOLUTIONS);
    }

    /**
     * Run all validations
     */
    public function runValidations(?DOOHScreen $screen = null): array
    {
        $results = [
            'format_valid' => $this->validateFormat(),
            'file_size_valid' => $this->validateFileSize(),
            'duration_valid' => $this->validateDuration(),
            'resolution_valid' => $this->validateResolution($screen),
            'overall_valid' => false,
            'errors' => [],
        ];
        
        if (!$results['format_valid']) {
            $results['errors'][] = 'Invalid file format. Allowed: ' . implode(', ', 
                $this->isVideo() ? self::ALLOWED_VIDEO_FORMATS : self::ALLOWED_IMAGE_FORMATS);
        }
        
        if (!$results['file_size_valid']) {
            $results['errors'][] = 'File size exceeds maximum limit of ' . self::MAX_FILE_SIZE_MB . 'MB';
        }
        
        if (!$results['duration_valid']) {
            $results['errors'][] = sprintf(
                'Video duration must be between %d and %d seconds',
                self::MIN_VIDEO_DURATION,
                self::MAX_VIDEO_DURATION
            );
        }
        
        if (!$results['resolution_valid']) {
            $results['errors'][] = $screen 
                ? "Resolution must match screen resolution: {$screen->resolution}"
                : 'Resolution must be one of: ' . implode(', ', self::STANDARD_RESOLUTIONS);
        }
        
        $results['overall_valid'] = empty($results['errors']);
        
        return $results;
    }

    /**
     * Approve creative
     */
    public function approve(?int $validatorId = null, ?string $notes = null): bool
    {
        $this->update([
            'validation_status' => self::VALIDATION_APPROVED,
            'validated_by' => $validatorId ?? auth()->id(),
            'validated_at' => now(),
            'validation_notes' => $notes,
            'status' => self::STATUS_ACTIVE,
        ]);
        
        return true;
    }

    /**
     * Reject creative
     */
    public function reject(string $reason, ?int $validatorId = null): bool
    {
        $this->update([
            'validation_status' => self::VALIDATION_REJECTED,
            'rejection_reason' => $reason,
            'validated_by' => $validatorId ?? auth()->id(),
            'validated_at' => now(),
        ]);
        
        return true;
    }

    /**
     * Archive creative
     */
    public function archive(): bool
    {
        $this->update([
            'status' => self::STATUS_ARCHIVED,
            'archived_at' => now(),
            'is_active' => false,
        ]);
        
        return true;
    }

    /**
     * Update schedule count
     */
    public function updateScheduleCount(): void
    {
        $this->update([
            'total_schedules' => $this->activeSchedules()->count(),
        ]);
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
        
        if ($this->thumbnail_path && Storage::exists($this->thumbnail_path)) {
            Storage::delete($this->thumbnail_path);
        }
        
        return true;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($creative) {
            // Delete file when creative is deleted
            $creative->deleteFile();
        });
    }
}
