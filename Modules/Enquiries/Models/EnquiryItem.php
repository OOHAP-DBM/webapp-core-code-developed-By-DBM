<?php

namespace Modules\Enquiries\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Hoarding; 
use Modules\Enquiries\Models\Enquiry;
use  Modules\Hoardings\Models\HoardingPackage;   

class EnquiryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquiry_id',
        'hoarding_id',
        'hoarding_type',
        'package_id',
        'package_type',
        'package_label',
        'preferred_start_date',
        'preferred_end_date',
        'expected_duration',
        'duration_months',
        'amount',
        'services',
        'meta',
        'status',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'services'   => 'array',
        'meta'       => 'array',
    ];

    /**
     * Hoarding Type
     */
    const TYPE_OOH  = 'ooh';
    const TYPE_DOOH = 'dooh';

    /**
     * Status Flow (Offer & Quotation Lifecycle)
     */
    const STATUS_NEW                    = 'new';
    const STATUS_REJECTED               = 'rejected';
    const STATUS_RESEND                 = 'resend';
    const STATUS_OFFER_SENT             = 'offer_send';
    const STATUS_OFFER_REJECTED         = 'offer_reject';
    const STATUS_OFFER_SENT_AGAIN       = 'offer_send_again';
    const STATUS_OFFER_ACCEPTED         = 'offer_accept';
    const STATUS_QUOTATION_SENT         = 'quotation_send';
    const STATUS_QUOTATION_REJECTED     = 'quotation_reject';
    const STATUS_QUOTATION_SENT_AGAIN   = 'quotation_send_again';
    const STATUS_QUOTATION_ACCEPTED     = 'quotation_accepted';
    const STATUS_PO_SENT                = 'purchase_order_send';

    /**
     * Parent Enquiry
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    /**
     * Hoarding (parent table for OOH + DOOH)
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /**
     * Package Resolver (OOH / DOOH)
     * Polymorphism NOT required (cleaner this way)
     */
    public function package()
    {
        if ($this->hoarding_type === self::TYPE_OOH) {
            return $this->belongsTo(HoardingPackage::class, 'package_id');
        }

        return $this->belongsTo(\Modules\DOOH\Models\DOOHPackage::class, 'package_id');
    }

    /**
     * Helpers
     */
    public function isOOH(): bool
    {
        return $this->hoarding_type === self::TYPE_OOH;
    }

    public function isDOOH(): bool
    {
        return $this->hoarding_type === self::TYPE_DOOH;
    }

    public function getDurationInDays(): int
    {
        return $this->preferred_start_date->diffInDays($this->preferred_end_date);
    }
}
