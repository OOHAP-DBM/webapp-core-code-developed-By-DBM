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
use Illuminate\Support\Facades\DB;


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
    protected $appends = ['enquiry_no'];


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
   
    public function getFormattedIdAttribute(): string
    {
        $vendorIds = $this->items()
            ->with('hoarding:id,vendor_id')
            ->get()
            ->pluck('hoarding.vendor_id')
            ->filter()        
            ->unique();    
        $vendorCount = $vendorIds->count();
        $prefix = $vendorCount <= 1 ? 'SV' : 'MV';
        return $prefix . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }


    public function getEnquiryDetails()
    {
        // LOAD ALL RELATIONS (THIS IS THE FIX)
        $this->load([
            'items.hoarding.vendor',
            'items.hoarding.doohScreen'
        ]);

        foreach ($this->items as $item) {

            $item->image_url = null;

            if (!$item->hoarding) {
                continue;
            }

            /* ================= OOH IMAGE ================= */

            if ($item->hoarding_type === 'ooh') {

                $media = DB::table('hoarding_media')
                    ->where('hoarding_id', $item->hoarding->id)
                    ->where('is_primary', 1)
                    ->first();

                if ($media) {
                    $item->image_url = asset('storage/' . $media->file_path);
                }
            }

            /* ================= DOOH IMAGE (FIXED) ================= */

            if ($item->hoarding_type === 'dooh') {

                $doohScreenId = optional($item->hoarding->doohScreen)->id;

                if ($doohScreenId) {

                    $media = DB::table('dooh_screen_media')
                        ->where('dooh_screen_id', $doohScreenId)
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort_order', 'asc')
                        ->first();

                    if ($media) {
                        $item->image_url = asset('storage/' . $media->file_path);
                    }
                }
            }

            /* ================= PACKAGE ================= */

            $item->package_name = '-';
            $item->discount_percent = '-';

            if ($item->hoarding_type === 'ooh' && $item->package_id) {
                $package = DB::table('hoarding_packages')->find($item->package_id);
                if ($package) {
                    $item->package_name = $package->package_name;
                    $item->discount_percent = $package->discount_percent;
                }
            }

            if ($item->hoarding_type === 'dooh' && $item->package_id) {
                $package = DB::table('dooh_packages')->find($item->package_id);
                if ($package) {
                    $item->package_name = $package->package_name;
                    $item->discount_percent = $package->discount_percent;
                }
            }

            /* ================= PRICE ================= */

            $item->final_price = \App\Services\EnquiryPriceCalculator::calculate($item);
        }

        return $this;
    }

}

