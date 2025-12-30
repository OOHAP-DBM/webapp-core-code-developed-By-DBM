<?php

namespace Modules\Hoardings\Models;

use Illuminate\Database\Eloquent\Model;

class HoardingPackage extends Model
{
    protected $table = 'hoarding_packages';
    protected $fillable = [
        'hoarding_id',
        'package_name',
        'min_booking_duration',
        'duration_unit',
        'discount_percent',
        'is_active',
        'price_per_month',
        'slots_per_day',
    ];

    public function hoarding()
    {
        return $this->belongsTo(Hoarding::class);
    }
}
