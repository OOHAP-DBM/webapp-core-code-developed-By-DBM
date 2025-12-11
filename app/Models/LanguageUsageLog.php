<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageUsageLog extends Model
{
    protected $fillable = [
        'locale',
        'user_type',
        'user_id',
        'ip_address',
        'user_agent',
        'country',
        'browser_language',
        'detection_method',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the language
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'locale', 'code');
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
