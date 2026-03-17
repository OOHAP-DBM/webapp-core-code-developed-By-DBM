<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Legacy XSS filter (Safari/IE)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (limit browser features)
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(self), payment=(self)'
        );

        // HTTP Strict Transport Security (HSTS) — only set over HTTPS
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy (report-only initially to avoid breaking things)
        // Upgrade to enforcing after auditing your specific scripts/styles inline.
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com https://www.google-analytics.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net https://fonts.googleapis.com https://cdnjs.cloudflare.com; " .
            "font-src 'self' https://fonts.bunny.net https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self' https://www.google-analytics.com; " .
            "frame-ancestors 'self';"
        );

        return $response;
    }
}
