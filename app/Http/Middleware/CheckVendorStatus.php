<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckVendorStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Only check for vendors
            if ($user->hasRole('vendor')) {
                $blockedStatuses = ['suspended', 'inactive', 'disabled'];

                $isUserBlocked   = in_array($user->status, $blockedStatuses);
                $isProfileBlocked = false;

                // Also check vendor profile onboarding_status
                if ($user->vendorProfile) {
                    $isProfileBlocked = in_array(
                        $user->vendorProfile->onboarding_status,
                        ['suspended', 'rejected']
                    );
                }

                if ($isUserBlocked || $isProfileBlocked) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->with('error', 'account suspended or deactivated. Please contact support.');
                }
            }
        }

        return $next($request);
    }
}