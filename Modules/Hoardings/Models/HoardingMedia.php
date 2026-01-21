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
    public function getMimeTypeAttribute()
    {
        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        $imageExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        $videoExtensions = ['mp4', 'webm', 'mov'];

        if (in_array($extension, $imageExtensions)) {
            return 'image/' . $extension;
        }
        if (in_array($extension, $videoExtensions)) {
            return 'video/' . $extension;
        }
        return 'application/octet-stream';
    }
}
