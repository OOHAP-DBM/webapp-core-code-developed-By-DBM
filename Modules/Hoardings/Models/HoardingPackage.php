<?php

namespace Modules\Hoardings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Hoarding;
use App\Models\User;

class HoardingPackage extends Model
{
    use HasFactory;

    protected $table = 'hoarding_packages';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'hoarding_id',
        'vendor_id',
        'package_code',
        'package_name',
        'discount_percent',
        'base_price_per_month',
        'min_booking_duration',
        'duration_unit',
        'start_date',
        'end_date',
        'services_included',
        'is_active',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'discount_percent'      => 'float',
        'base_price_per_month' => 'float',
        'min_booking_duration' => 'integer',
        'is_active'             => 'boolean',
        'services_included'     => 'array',
        'start_date'            => 'date',
        'end_date'              => 'date',
    ];

    /* ---------------------------------------------
     | Relationships
     |---------------------------------------------*/

    public function hoarding()
    {
        return $this->belongsTo(Hoarding::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /* ---------------------------------------------
     | Scopes (Very Important for Market Ready)
     |---------------------------------------------*/

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', now());
        })->where(function ($q) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now());
        });
    }

    /* ---------------------------------------------
     | Helpers
     |---------------------------------------------*/

    /**
     * Final price after discount
     */
    public function getFinalPriceAttribute()
    {
        if ($this->discount_percent > 0) {
            return round(
                $this->base_price_per_month * (1 - ($this->discount_percent / 100)),
                2
            );
        }

        return $this->base_price_per_month;
    }
}
