<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag_icon',
        'direction',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get translations for this language
     */
    public function translations()
    {
        return $this->hasMany(Translation::class, 'locale', 'code');
    }

    /**
     * Get usage logs for this language
     */
    public function usageLogs()
    {
        return $this->hasMany(LanguageUsageLog::class, 'locale', 'code');
    }

    /**
     * Get translation requests for this language
     */
    public function translationRequests()
    {
        return $this->hasMany(TranslationRequest::class, 'locale', 'code');
    }

    /**
     * Scope to get only active languages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default language
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first() 
            ?? static::where('code', 'en')->first();
    }

    /**
     * Set as default language
     */
    public function setAsDefault()
    {
        // Remove default flag from all languages
        static::query()->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);

        return $this;
    }
}
