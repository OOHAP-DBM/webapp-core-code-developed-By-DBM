<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces a single canonical domain (non-www) and HTTPS.
 *
 * Primary domain: oohapp.io (non-www, HTTPS)
 *
 * Redirects:
 *  - http://oohapp.io/*        → https://oohapp.io/*
 *  - https://www.oohapp.io/*   → https://oohapp.io/*
 *  - http://www.oohapp.io/*    → https://oohapp.io/*
 */
class CanonicalRedirect
{
    /** Primary host without scheme */
    private const PRIMARY_HOST = 'oohapp.io';

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Strip leading "www." if present
        $canonicalHost = preg_replace('/^www\./i', '', $host);

        $needsRedirect = false;

        // Wrong host (www variant)
        if ($host !== $canonicalHost) {
            $needsRedirect = true;
        }

        // Non-HTTPS (only enforce in production)
        if (app()->isProduction() && !$request->isSecure()) {
            $needsRedirect = true;
        }

        if ($needsRedirect) {
            $url = 'https://' . $canonicalHost . $request->getRequestUri();
            return redirect($url, 301);
        }

        return $next($request);
    }
}
