<?php

namespace Modules\POD\Models;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PODSubmission extends Model
{
    protected $table = 'pod_submissions';

    protected $fillable = [
        'booking_id',
        'submitted_by',
        'submission_date',
        'files',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'files' => 'array',
        'submission_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relationship: Submitted by (Mounter/User)
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relationship: Approved by (Vendor)
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: Rejected by (Vendor)
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Check if POD is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if POD is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if POD is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get photo files
     */
    public function getPhotosAttribute(): array
    {
        return collect($this->files)->where('type', 'photo')->all();
    }

    /**
     * Get video files
     */
    public function getVideosAttribute(): array
    {
        return collect($this->files)->where('type', 'video')->all();
    }

    /**
     * Get total files count
     */
    public function getFilesCountAttribute(): int
    {
        return count($this->files);
    }
}
