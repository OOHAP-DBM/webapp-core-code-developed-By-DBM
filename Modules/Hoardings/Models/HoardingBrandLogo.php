<?php
// Modules/Hoardings/Models/HoardingBrandLogo.php

namespace Modules\Hoardings\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Hoarding;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Hoardings\Models\OOHHoarding;

class HoardingBrandLogo extends Model
{
    protected $table = 'hoarding_brand_logos';
    protected $fillable = [
        'hoarding_id',
        'brand_name',
        'file_path',
        'sort_order',
    ];

    public function oohHoarding(): BelongsTo
    {
        return $this->belongsTo(OOHHoarding::class, 'hoarding_id', 'id');
    }
    public function brandable()
    {
        return $this->morphTo();
    }

  
}
