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

    public function normalizedMimeType(): string
    {
        $raw = $this->media_type ?? $this->mime_type ?? '';
        if ($raw === 'video') {
            $ext = pathinfo($this->file_path, PATHINFO_EXTENSION);
            return $ext === 'webm' ? 'video/webm' : 'video/mp4';
        } elseif ($raw === 'image') {
            $ext = pathinfo($this->file_path, PATHINFO_EXTENSION);
            return match($ext) {
                'png'  => 'image/png',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
        }
        return $raw;
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->normalizedMimeType(), 'video');
    }
}
