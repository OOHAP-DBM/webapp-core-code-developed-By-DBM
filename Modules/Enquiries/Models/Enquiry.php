<?php

namespace Modules\Enquiries\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Hoarding;
use Modules\Enquiries\Models\EnquiryItem;
use App\Models\User;

class Enquiry extends Model
{
    use HasFactory;

    protected $table = 'enquiries';

    protected $fillable = [
        'customer_id',
        'source',
        'status',
        'customer_note',
        'contact_number',
    ];

    /* ===================== CASTS ===================== */

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ===================== CONSTANTS ===================== */

    const STATUS_DRAFT     = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_RESPONDED = 'responded';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING   = 'pending';
    const STATUS_ACCEPTED  = 'accepted';
    const STATUS_REJECTED  = 'rejected';

    /* ===================== RELATIONSHIPS ===================== */

    /**
     * Customer who raised the enquiry
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'customer_id');
    }
   public function hoarding()
    {
        return $this->belongsTo(Hoarding::class, 'hoarding_id');
    }
    /**
     * Enquiry items (OOH / DOOH selections)
     */
    public function items(): HasMany
    {
        return $this->hasMany(EnquiryItem::class);
    }

    /**
     * Offers created against this enquiry
     */
    public function offers(): HasMany
    {
        return $this->hasMany(\App\Models\Offer::class);
    }

    /**
     * Communication thread
     */
    public function thread(): HasOne
    {
        return $this->hasOne(\Modules\Threads\Models\Thread::class);
    }

    /* ===================== SCOPES ===================== */

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeResponded($query)
    {
        return $query->where('status', self::STATUS_RESPONDED);
    }

    /* ===================== HELPERS ===================== */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Total hoardings/screens in enquiry
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }
}

