<?php
// Modules/Hoardings/Models/HoardingBrandLogo.php

namespace Modules\Hoardings\Models;

use Illuminate\Database\Eloquent\Model;

class HoardingBrandLogo extends Model
{
    protected $table = 'hoarding_brand_logos';
    protected $fillable = [
        'brandable_id',
        'brandable_type',
        'file_path',
        'sort_order',
    ];

    public function brandable()
    {
        return $this->morphTo();
    }
}
