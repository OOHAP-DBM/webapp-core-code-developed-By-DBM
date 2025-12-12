<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Services\RoleSwitchingService;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    public function __construct(
        protected RoleSwitchingService $roleSwitchingService
    ) {}

    /**
     * Ensure user has an active role set (PROMPT 96)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $user = $request->user();
            
            // If no active role, set it to primary role
            if (!$user->active_role) {
                $activeRole = $this->roleSwitchingService->getActiveRole($user);
                
                if ($activeRole) {
                    $user->update(['active_role' => $activeRole]);
                }
            }
        }

        return $next($request);
    }
}
