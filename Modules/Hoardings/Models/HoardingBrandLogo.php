<?php
// Modules/Hoardings/Models/HoardingBrandLogo.php

namespace Modules\Hoardings\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Hoarding;

class HoardingBrandLogo extends Model
{
    protected $table = 'hoarding_brand_logos';
    protected $fillable = [
        'hoarding_id',
        'brand_name',
        'file_path',
        'sort_order',
    ];

    public function hoarding()
    {
        return $this->belongsTo(Hoarding::class);
    }
    public function brandable()
    {
        return $this->morphTo();
    }

  
}
