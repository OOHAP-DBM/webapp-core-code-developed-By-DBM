<?php
// Modules/DOOH/Models/DOOHScreenBrandLogo.php

namespace Modules\DOOH\Models;

use Illuminate\Database\Eloquent\Model;

class DOOHScreenBrandLogo extends Model
{
    protected $table = 'dooh_screen_brand_logos';
    protected $fillable = [
        'dooh_screen_id',
        'file_path',
        'sort_order',
    ];

    public function screen()
    {
        return $this->belongsTo(DOOHScreen::class, 'dooh_screen_id');
    }
}
