<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'key',
        'locale',
        'value',
        'group',
        'type',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the language
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'locale', 'code');
    }

    /**
     * Get the user who created this translation
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this translation
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
