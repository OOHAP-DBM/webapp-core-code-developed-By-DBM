<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Rate Limit Middleware
 * 
 * Logs rate limit violations and provides custom responses
 */
class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If rate limit was hit (429 status)
        if ($response->getStatusCode() === 429) {
            $this->logRateLimitViolation($request);
        }

        return $response;
    }

    /**
     * Log rate limit violations for monitoring
     */
    protected function logRateLimitViolation(Request $request): void
    {
        Log::warning('API Rate Limit Exceeded', [
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role,
            'path' => $request->path(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
