<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OOHHoarding extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ooh_hoardings';

    protected $fillable = [

        /* FK */
        'hoarding_id',

        /* Physical dimensions */
        'width',
        'height',
        'measurement_unit',
        'calculated_area_sqft',

        /* Structure */
        'lighting_type',        // frontlight, backlight, none
        'material_type',
        'mounting_type',

        /* Pricing – OOH only */
        'printing_included',
        'printing_charge',

        'mounting_included',
        'mounting_charge',

        'designing_included',
        'designing_charge',

        'remounting_charge',
        'survey_charge',
    ];

    protected $casts = [
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'calculated_area_sqft' => 'decimal:2',

        'printing_included' => 'boolean',
        'mounting_included' => 'boolean',
        'designing_included' => 'boolean',

        'printing_charge' => 'decimal:2',
        'mounting_charge' => 'decimal:2',
        'designing_charge' => 'decimal:2',
        'remounting_charge' => 'decimal:2',
        'survey_charge' => 'decimal:2',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class, 'hoarding_id');
    }

    /* ================= HELPERS ================= */

    public function getAreaAttribute(): float
    {
        return (float) $this->width * (float) $this->height;
    }

    public function getTotalOneTimeChargesAttribute(): float
    {
        $total = 0;

        if (!$this->printing_included) {
            $total += (float) $this->printing_charge;
        }

        if (!$this->mounting_included) {
            $total += (float) $this->mounting_charge;
        }

        if (!$this->designing_included) {
            $total += (float) $this->designing_charge;
        }

        return $total + (float) $this->survey_charge;
    }
}
