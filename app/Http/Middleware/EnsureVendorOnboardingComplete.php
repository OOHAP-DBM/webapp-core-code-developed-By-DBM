<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorOnboardingComplete
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Only apply to vendors
        if (!$user || !$user->isVendor()) {
            return $next($request);
        }

        // Allow access to onboarding routes
        if ($request->routeIs('vendor.onboarding.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        $profile = $user->vendorProfile;

        // If no profile, redirect to onboarding
        if (!$profile) {
            return redirect()->route('vendor.onboarding.contact-details')
                ->with('info', 'Please complete your vendor onboarding first.');
        }

        // Check onboarding status
        switch ($profile->onboarding_status) {
            case 'draft':
                // Redirect to current step
                return redirect()->route($this->getStepRoute($profile->onboarding_step))
                    ->with('info', 'Please complete your vendor onboarding.');

            case 'pending_approval':
                return redirect()->route('vendor.onboarding.waiting')
                    ->with('info', 'Your application is under review.');

            case 'rejected':
                return redirect()->route('vendor.onboarding.rejected')
                    ->with('error', 'Your vendor application was rejected.');

            case 'suspended':
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Your account has been suspended.');

            case 'approved':
                // Vendor is approved, allow access
                return $next($request);

            default:
                return redirect()->route('vendor.onboarding.contact-details');
        }
    }

    /**
     * Get route name for onboarding step
     */
    protected function getStepRoute(int $step): string
    {
        return match($step) {
            1 => 'vendor.onboarding.contact-details',
            2 => 'vendor.onboarding.business-info',
            3 => 'vendor.onboarding.kyc-documents',
            4 => 'vendor.onboarding.bank-details',
            5 => 'vendor.onboarding.terms-agreement',
            default => 'vendor.onboarding.contact-details',
        };
    }
}
