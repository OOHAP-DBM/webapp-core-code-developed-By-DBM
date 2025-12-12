<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActiveRoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Enforce active role permissions and prevent privilege escalation (PROMPT 96)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $activeRole = $user->active_role ?? $user->getPrimaryRole();

        // No active role - suspicious, logout user
        if (!$activeRole) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.');
        }

        // Verify user still has the active role assigned
        if (!$user->hasRole($activeRole)) {
            // Active role was revoked, reset to primary role
            $primaryRole = $user->getPrimaryRole();
            $user->update(['active_role' => $primaryRole]);
            
            return redirect()->route($user->getDashboardRoute())
                ->with('warning', 'Your role permissions have changed. Please login again if needed.');
        }

        // If specific roles are required for this route
        if (!empty($roles)) {
            // Check if active role matches required roles
            if (!in_array($activeRole, $roles)) {
                \Log::warning('Active role access denied', [
                    'user_id' => $user->id,
                    'active_role' => $activeRole,
                    'required_roles' => $roles,
                    'route' => $request->path(),
                    'ip' => $request->ip(),
                ]);

                abort(403, 'You do not have permission to access this resource with your current role.');
            }
        }

        // Set active role in request for controllers to use
        $request->merge(['active_role' => $activeRole]);

        return $next($request);
    }
}
