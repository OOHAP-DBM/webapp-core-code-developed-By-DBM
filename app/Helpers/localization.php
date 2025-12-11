<?php

/**
 * Multi-Language Support Helper Functions
 * PROMPT 80
 */

if (!function_exists('get_locale')) {
    /**
     * Get current locale
     */
    function get_locale()
    {
        return app()->getLocale();
    }
}

if (!function_exists('set_locale')) {
    /**
     * Set application locale
     */
    function set_locale($locale)
    {
        $service = app(\App\Services\LocalizationService::class);
        return $service->setLocale($locale);
    }
}

if (!function_exists('supported_locales')) {
    /**
     * Get all supported locales
     */
    function supported_locales()
    {
        return config('app.supported_locales', ['en' => 'English']);
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Check if current language is RTL
     */
    function is_rtl($locale = null)
    {
        $service = app(\App\Services\LocalizationService::class);
        return $service->isRTL($locale);
    }
}

if (!function_exists('text_direction')) {
    /**
     * Get text direction (ltr/rtl)
     */
    function text_direction($locale = null)
    {
        $service = app(\App\Services\LocalizationService::class);
        return $service->getDirection($locale);
    }
}

if (!function_exists('translate')) {
    /**
     * Translate with fallback
     */
    function translate($key, $locale = null, $group = 'common', $fallback = null)
    {
        $service = app(\App\Services\LocalizationService::class);
        return $service->translate($key, $locale, $group, $fallback);
    }
}

if (!function_exists('__t')) {
    /**
     * Shorthand translation function
     * Usage: __t('customer.welcome')
     */
    function __t($key, $replace = [], $locale = null)
    {
        // Split key into group and actual key
        $parts = explode('.', $key, 2);
        
        if (count($parts) === 2) {
            $group = $parts[0];
            $actualKey = $parts[1];
            
            $service = app(\App\Services\LocalizationService::class);
            $translation = $service->translate($actualKey, $locale, $group);
        } else {
            // Fallback to Laravel's trans()
            $translation = trans($key, $replace, $locale);
        }

        // Replace placeholders
        if (!empty($replace)) {
            foreach ($replace as $placeholder => $value) {
                $translation = str_replace(':'.$placeholder, $value, $translation);
            }
        }

        return $translation;
    }
}

if (!function_exists('get_native_language_name')) {
    /**
     * Get native language name
     */
    function get_native_language_name($locale)
    {
        $service = app(\App\Services\LocalizationService::class);
        return $service->getNativeName($locale);
    }
}

if (!function_exists('available_languages')) {
    /**
     * Get all available languages
     */
    function available_languages()
    {
        $service = app(\App\Services\LocalizationService::class);
        return $service->getActiveLanguages();
    }
}

if (!function_exists('locale_url')) {
    /**
     * Generate URL with locale parameter
     */
    function locale_url($path, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $url = url($path);
        
        return $url . (strpos($url, '?') ? '&' : '?') . 'lang=' . $locale;
    }
}

if (!function_exists('trans_model')) {
    /**
     * Get translated model attribute
     * Usage: trans_model($hoarding, 'description')
     */
    function trans_model($model, $attribute, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        // Check if translation exists
        $translation = \App\Models\ModelTranslation::where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->where('locale', $locale)
            ->where('field', $attribute)
            ->value('value');
        
        // Return translation or original value
        return $translation ?? $model->$attribute;
    }
}

if (!function_exists('set_model_translation')) {
    /**
     * Set model attribute translation
     */
    function set_model_translation($model, $attribute, $value, $locale)
    {
        return \App\Models\ModelTranslation::updateOrCreate(
            [
                'translatable_type' => get_class($model),
                'translatable_id' => $model->id,
                'locale' => $locale,
                'field' => $attribute,
            ],
            [
                'value' => $value,
            ]
        );
    }
}
