<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocalizationService;
use App\Models\Language;
use App\Models\Translation;
use App\Models\TranslationRequest;

class LanguageController extends Controller
{
    protected $localizationService;

    public function __construct(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    /**
     * Switch language
     */
    public function switch(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|max:10',
        ]);

        $locale = $request->input('locale');
        
        // Validate and set locale
        $language = $this->localizationService->getLanguageByCode($locale);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid language code',
            ], 400);
        }

        $this->localizationService->setLocale($locale, 'manual');

        // Return appropriate response based on request type
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Language switched successfully',
                'locale' => $locale,
                'language' => $language,
            ]);
        }

        // Redirect back with success message
        return redirect()->back()->with('success', __('common.language') . ' ' . __('common.success'));
    }

    /**
     * Get available languages (API)
     */
    public function index()
    {
        $languages = $this->localizationService->getActiveLanguages();

        return response()->json([
            'success' => true,
            'languages' => $languages,
            'current' => app()->getLocale(),
        ]);
    }

    /**
     * Get translations for a specific locale (API)
     */
    public function getTranslations(Request $request, $locale)
    {
        $group = $request->get('group', 'common');

        $translations = $this->localizationService->getTranslations($locale, $group);

        return response()->json([
            'success' => true,
            'locale' => $locale,
            'group' => $group,
            'translations' => $translations,
        ]);
    }

    /**
     * Submit translation suggestion
     */
    public function suggestTranslation(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:500',
            'locale' => 'required|string|max:10',
            'group' => 'nullable|string|max:100',
            'suggested_value' => 'required|string',
        ]);

        // Check if language exists and is active
        $language = $this->localizationService->getLanguageByCode($request->locale);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid language code',
            ], 400);
        }

        // Get current translation
        $currentTranslation = Translation::where('key', $request->key)
            ->where('locale', $request->locale)
            ->where('group', $request->group ?? 'common')
            ->first();

        // Create translation request
        $translationRequest = TranslationRequest::create([
            'key' => $request->key,
            'locale' => $request->locale,
            'group' => $request->group ?? 'common',
            'current_value' => $currentTranslation ? $currentTranslation->value : null,
            'suggested_value' => $request->suggested_value,
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Translation suggestion submitted successfully. It will be reviewed by our team.',
            'request' => $translationRequest,
        ]);
    }

    /**
     * Get language usage statistics (Admin only)
     */
    public function statistics(Request $request)
    {
        // Check admin permission
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $days = $request->get('days', 30);

        $stats = $this->localizationService->getLanguageStatistics($days);
        $mostPopular = $this->localizationService->getMostPopularLanguage($days);

        return response()->json([
            'success' => true,
            'days' => $days,
            'statistics' => $stats,
            'most_popular' => $mostPopular,
        ]);
    }

    /**
     * Clear language cache (Admin only)
     */
    public function clearCache()
    {
        // Check admin permission
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->localizationService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Language cache cleared successfully',
        ]);
    }

    /**
     * Language selector widget view
     */
    public function selector()
    {
        $languages = $this->localizationService->getActiveLanguages();
        $currentLocale = app()->getLocale();
        $currentLanguage = $this->localizationService->getLanguageByCode($currentLocale);

        return view('components.language-selector', compact('languages', 'currentLanguage'));
    }
}
