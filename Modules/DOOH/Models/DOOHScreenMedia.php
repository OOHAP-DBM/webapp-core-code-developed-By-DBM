<?php

namespace Modules\DOOH\Models;

use Illuminate\Database\Eloquent\Model;

class DOOHScreenMedia extends Model
{
    protected $table = 'dooh_screen_media';
    protected $fillable = [
        'dooh_screen_id',
        'file_path',
        'media_type',
        'is_primary',
        'sort_order',
    ];

    public function screen()
    {
        return $this->belongsTo(DOOHScreen::class);
    }
}
