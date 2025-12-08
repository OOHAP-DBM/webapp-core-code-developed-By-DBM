<?php

namespace Modules\Bookings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BookingProof extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The table associated with the model.
     */
    protected $table = 'booking_proofs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'booking_id',
        'uploaded_by',
        'media_id',
        'latitude',
        'longitude',
        'distance_from_hoarding',
        'type',
        'file_path',
        'file_size',
        'status',
        'uploaded_at',
        'verified_at',
        'verified_by',
        'verified_notes',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'array',
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('proof')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'video/mp4', 'video/quicktime'])
            ->maxFilesize(50 * 1024 * 1024); // 50MB max
    }

    /**
     * Get the booking this proof belongs to
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who uploaded the proof
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who verified the proof
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope: Pending proofs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Approved proofs
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Rejected proofs
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if proof is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if proof is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if proof is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the proof
     */
    public function approve(int $verifiedBy, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
            'verified_notes' => $notes,
        ]);
    }

    /**
     * Reject the proof
     */
    public function reject(int $verifiedBy, string $notes): void
    {
        $this->update([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
            'verified_notes' => $notes,
        ]);
    }

    /**
     * Check if location is within acceptable radius
     */
    public function isWithinRadius(int $maxDistanceMeters): bool
    {
        return $this->distance_from_hoarding !== null 
            && $this->distance_from_hoarding <= $maxDistanceMeters;
    }

    /**
     * Get proof URL
     */
    public function getProofUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('proof');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get thumbnail URL (for videos)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('proof');
        return $media ? $media->getUrl('thumb') : null;
    }
}

