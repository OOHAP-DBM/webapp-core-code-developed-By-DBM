<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Hoarding;

class POSBookingHoarding extends Model
{
    use SoftDeletes;

    protected $table = 'pos_booking_hoardings';

    protected $fillable = [
        'pos_booking_id',
        'hoarding_id',
        'hoarding_price',
        'hoarding_discount',
        'hoarding_tax',
        'hoarding_total',
        'start_date',
        'end_date',
        'duration_days',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hoarding_price' => 'decimal:2',
        'hoarding_discount' => 'decimal:2',
        'hoarding_tax' => 'decimal:2',
        'hoarding_total' => 'decimal:2',
    ];

    /**
     * Get the POS booking
     */
    public function posBooking(): BelongsTo
    {
        return $this->belongsTo(POSBooking::class, 'pos_booking_id');
    }

    /**
     * Get the hoarding
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /**
     * Get hoarding image URL (reuse from VendorPosController)
     */
    public function getImageUrl(): ?string
    {
        try {
            if ($this->hoarding->hoarding_type === 'ooh') {
                $media = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $this->hoarding_id)
                    ->where('is_primary', true)
                    ->orderBy('sort_order')
                    ->first();

                if ($media && $media->file_path) {
                    return $media->file_path;
                }

                $media = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $this->hoarding_id)
                    ->orderBy('sort_order')
                    ->first();

                return $media ? $media->file_path : null;
            }

            if ($this->hoarding->hoarding_type === 'dooh' && $this->hoarding->doohScreen) {
                $media = $this->hoarding->doohScreen->getFirstMedia('hero_image');
                if ($media) return $media->getUrl();

                $galleryMedia = $this->hoarding->doohScreen->getFirstMedia('gallery');
                if ($galleryMedia) return $galleryMedia->getUrl();
            }

            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Error getting hoarding image URL', [
                'hoarding_id' => $this->hoarding_id,
                'type' => $this->hoarding->hoarding_type ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}