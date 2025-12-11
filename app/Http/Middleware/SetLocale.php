<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LocalizationService;

class SetLocale
{
    protected $localizationService;

    public function __construct(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auto-detect and set locale
        $language = $this->localizationService->detectLanguage($request);

        // Share language data with all views
        view()->share('currentLanguage', $language);
        view()->share('availableLanguages', $this->localizationService->getActiveLanguages());
        view()->share('isRTL', $language->direction === 'rtl');
        view()->share('textDirection', $language->direction);

        return $next($request);
    }
}
