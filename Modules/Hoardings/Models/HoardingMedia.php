<?php

namespace Modules\Hoardings\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Hoarding;

class HoardingMedia extends Model
{
    protected $table = 'hoarding_media';
    protected $fillable = [
        'hoarding_id',
        'file_path',
        'media_type',
        'is_primary',
        'sort_order',
    ];

    public function hoarding()
    {
        return $this->belongsTo(Hoarding::class);
    }
}
