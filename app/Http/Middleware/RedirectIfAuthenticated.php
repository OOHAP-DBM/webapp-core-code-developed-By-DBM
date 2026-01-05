<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                /** @var \App\Models\User $user */
                $user = Auth::guard($guard)->user();
                
                // Check if user account is active
                if (!$user->isActive()) {
                    Auth::guard($guard)->logout();
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json(['message' => 'Your account is ' . $user->status . '. Please contact support.'], 403);
                    }
                    return redirect()->route('login')->with('error', 'Your account is ' . $user->status . '. Please contact support.');
                }

                // Redirect to role-based dashboard (web only)
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['message' => 'Already authenticated.'], 200);
                }
                // Redirect to role-based dashboard
                return redirect($user->getDashboardRoute());
            }
        }

        return $next($request);
    }
}
