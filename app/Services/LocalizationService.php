<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Translation;
use App\Models\LanguageUsageLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LocalizationService
{
    /**
     * Get all active languages
     */
    public function getActiveLanguages()
    {
        return Cache::remember('active_languages', 3600, function () {
            return Language::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage()
    {
        return Cache::remember('default_language', 3600, function () {
            return Language::where('is_default', true)->first() 
                ?? Language::where('code', 'en')->first();
        });
    }

    /**
     * Get language by code
     */
    public function getLanguageByCode($code)
    {
        return Language::where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Detect user's preferred language
     */
    public function detectLanguage($request)
    {
        // Priority order:
        // 1. URL parameter (?lang=hi)
        // 2. Session
        // 3. User preference (if logged in)
        // 4. Browser Accept-Language header
        // 5. IP-based geolocation
        // 6. Default language

        $detectionMethod = 'manual';

        // 1. Check URL parameter
        if ($request->has('lang')) {
            $lang = $this->getLanguageByCode($request->get('lang'));
            if ($lang) {
                $detectionMethod = 'manual';
                $this->setLocale($lang->code, $detectionMethod);
                return $lang;
            }
        }

        // 2. Check session
        if (Session::has('locale')) {
            $lang = $this->getLanguageByCode(Session::get('locale'));
            if ($lang) {
                App::setLocale($lang->code);
                return $lang;
            }
        }

        // 3. Check user preference (if authenticated)
        if (auth()->check() && auth()->user()->preferred_language) {
            $lang = $this->getLanguageByCode(auth()->user()->preferred_language);
            if ($lang) {
                $detectionMethod = 'user_preference';
                $this->setLocale($lang->code, $detectionMethod);
                return $lang;
            }
        }

        // 4. Check browser Accept-Language header
        $browserLanguage = $this->detectBrowserLanguage($request);
        if ($browserLanguage) {
            $lang = $this->getLanguageByCode($browserLanguage);
            if ($lang) {
                $detectionMethod = 'browser';
                $this->setLocale($lang->code, $detectionMethod);
                return $lang;
            }
        }

        // 5. IP-based geolocation (simplified - you can integrate MaxMind or similar)
        $ipLanguage = $this->detectLanguageByIP($request->ip());
        if ($ipLanguage) {
            $lang = $this->getLanguageByCode($ipLanguage);
            if ($lang) {
                $detectionMethod = 'ip_location';
                $this->setLocale($lang->code, $detectionMethod);
                return $lang;
            }
        }

        // 6. Default language
        $defaultLang = $this->getDefaultLanguage();
        $this->setLocale($defaultLang->code, 'manual');
        return $defaultLang;
    }

    /**
     * Detect browser language from Accept-Language header
     */
    private function detectBrowserLanguage($request)
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        // Example: "en-US,en;q=0.9,hi;q=0.8,ta;q=0.7"
        $languages = explode(',', $acceptLanguage);
        $preferredLanguages = [];

        foreach ($languages as $lang) {
            $parts = explode(';', trim($lang));
            $code = strtolower(substr($parts[0], 0, 2)); // Get first 2 chars (en, hi, ta, etc.)
            $quality = 1.0;

            if (isset($parts[1]) && strpos($parts[1], 'q=') === 0) {
                $quality = (float) substr($parts[1], 2);
            }

            $preferredLanguages[$code] = $quality;
        }

        // Sort by quality (preference)
        arsort($preferredLanguages);

        // Return first supported language
        foreach (array_keys($preferredLanguages) as $code) {
            if ($this->getLanguageByCode($code)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Detect language by IP geolocation
     * This is a simplified version - integrate with MaxMind GeoIP2 or similar for production
     */
    private function detectLanguageByIP($ip)
    {
        // Skip local IPs
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return null;
        }

        // You can integrate with services like:
        // - MaxMind GeoIP2
        // - ip-api.com
        // - ipinfo.io
        // - ipstack.com

        // For now, return null (implement your preferred service)
        return null;
    }

    /**
     * Set application locale
     */
    public function setLocale($locale, $detectionMethod = 'manual')
    {
        // Validate locale
        $language = $this->getLanguageByCode($locale);
        
        if (!$language) {
            $locale = $this->getDefaultLanguage()->code;
            $language = $this->getDefaultLanguage();
        }

        // Set Laravel locale
        App::setLocale($locale);

        // Store in session
        Session::put('locale', $locale);
        Session::put('language_direction', $language->direction);

        // Update user preference (if authenticated)
        if (auth()->check() && auth()->user()->preferred_language !== $locale) {
            auth()->user()->update(['preferred_language' => $locale]);
        }

        // Log language usage
        $this->logLanguageUsage($locale, $detectionMethod);

        return $locale;
    }

    /**
     * Log language usage for analytics
     */
    private function logLanguageUsage($locale, $detectionMethod)
    {
        try {
            LanguageUsageLog::create([
                'locale' => $locale,
                'user_type' => auth()->check() ? (auth()->user()->role ?? 'customer') : 'guest',
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'browser_language' => request()->header('Accept-Language'),
                'detection_method' => $detectionMethod,
                'used_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break the app for logging issues
            \Log::error('Language usage log failed: ' . $e->getMessage());
        }
    }

    /**
     * Get translation with fallback
     */
    public function translate($key, $locale = null, $group = 'common', $fallback = null)
    {
        $locale = $locale ?? App::getLocale();
        
        // Try Laravel's built-in trans() first
        $translation = trans("{$group}.{$key}");
        
        // If not found in files, check database
        if ($translation === "{$group}.{$key}") {
            $dbTranslation = Translation::where('key', $key)
                ->where('locale', $locale)
                ->where('group', $group)
                ->value('value');
            
            if ($dbTranslation) {
                return $dbTranslation;
            }

            // Fallback to default language
            $defaultLocale = $this->getDefaultLanguage()->code;
            if ($locale !== $defaultLocale) {
                $dbTranslation = Translation::where('key', $key)
                    ->where('locale', $defaultLocale)
                    ->where('group', $group)
                    ->value('value');
                
                if ($dbTranslation) {
                    return $dbTranslation;
                }
            }

            // Return fallback or key
            return $fallback ?? $key;
        }

        return $translation;
    }

    /**
     * Add or update translation
     */
    public function setTranslation($key, $value, $locale, $group = 'common', $type = 'string')
    {
        return Translation::updateOrCreate(
            [
                'key' => $key,
                'locale' => $locale,
                'group' => $group,
            ],
            [
                'value' => $value,
                'type' => $type,
                'updated_by' => auth()->id(),
            ]
        );
    }

    /**
     * Get all translations for a locale
     */
    public function getTranslations($locale, $group = null)
    {
        $query = Translation::where('locale', $locale);
        
        if ($group) {
            $query->where('group', $group);
        }

        return $query->pluck('value', 'key')->toArray();
    }

    /**
     * Import translations from array
     */
    public function importTranslations(array $translations, $locale, $group = 'common')
    {
        $count = 0;

        foreach ($translations as $key => $value) {
            $this->setTranslation($key, $value, $locale, $group);
            $count++;
        }

        // Clear cache
        Cache::forget("translations_{$locale}_{$group}");

        return $count;
    }

    /**
     * Export translations to array
     */
    public function exportTranslations($locale, $group = null)
    {
        return $this->getTranslations($locale, $group);
    }

    /**
     * Get language usage statistics
     */
    public function getLanguageStatistics($days = 30)
    {
        $startDate = now()->subDays($days);

        return LanguageUsageLog::where('used_at', '>=', $startDate)
            ->selectRaw('locale, COUNT(*) as usage_count, user_type')
            ->groupBy('locale', 'user_type')
            ->orderByDesc('usage_count')
            ->get();
    }

    /**
     * Get most popular language
     */
    public function getMostPopularLanguage($days = 30)
    {
        $startDate = now()->subDays($days);

        $result = LanguageUsageLog::where('used_at', '>=', $startDate)
            ->selectRaw('locale, COUNT(*) as usage_count')
            ->groupBy('locale')
            ->orderByDesc('usage_count')
            ->first();

        return $result ? $result->locale : $this->getDefaultLanguage()->code;
    }

    /**
     * Clear language cache
     */
    public function clearCache()
    {
        Cache::forget('active_languages');
        Cache::forget('default_language');
        
        // Clear all translation caches
        $languages = Language::pluck('code');
        foreach ($languages as $locale) {
            Cache::forget("translations_{$locale}_common");
            Cache::forget("translations_{$locale}_customer");
            Cache::forget("translations_{$locale}_vendor");
            Cache::forget("translations_{$locale}_admin");
        }
    }

    /**
     * Get language direction (ltr/rtl)
     */
    public function getDirection($locale = null)
    {
        $locale = $locale ?? App::getLocale();
        
        $language = $this->getLanguageByCode($locale);
        
        return $language ? $language->direction : 'ltr';
    }

    /**
     * Check if locale is RTL (Right-to-Left)
     */
    public function isRTL($locale = null)
    {
        return $this->getDirection($locale) === 'rtl';
    }

    /**
     * Get native language name
     */
    public function getNativeName($locale)
    {
        $language = $this->getLanguageByCode($locale);
        
        return $language ? $language->native_name : $locale;
    }
}
