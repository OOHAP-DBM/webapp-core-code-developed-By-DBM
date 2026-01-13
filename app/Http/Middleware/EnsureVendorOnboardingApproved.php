<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorOnboardingApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            !$user ||
            !$user->vendorProfile ||
            !$user->vendorProfile->isApproved()
        ) {
            // API REQUEST
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your vendor onboarding is not approved yet.',
                ], 403);
            }

            // WEB REQUEST
            return redirect()
                ->route('vendor.dashboard')
                ->with('error', 'Your vendor onboarding is not approved yet.');
        }
        

        return $next($request);
    }
}
