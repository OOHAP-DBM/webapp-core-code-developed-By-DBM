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
    // Add this to DOOHScreenMedia model
    public function getMimeTypeAttribute(): string
    {
        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));

        $imageExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        $videoExtensions = ['mp4', 'webm', 'mov'];

        if (in_array($extension, $imageExtensions)) {
            return 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension);
        }
        if (in_array($extension, $videoExtensions)) {
            return $extension === 'mov' ? 'video/mp4' : 'video/' . $extension;
        }
        return 'application/octet-stream';
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
