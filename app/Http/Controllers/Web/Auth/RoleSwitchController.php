<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Modules\Auth\Services\RoleSwitchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    public function __construct(
        protected RoleSwitchingService $roleSwitchingService
    ) {}

    /**
     * Switch to a different role (PROMPT 96)
     */
    public function switch(Request $request, string $role): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue');
        }

        // Security check: Can user switch to this role?
        if (!$this->roleSwitchingService->canSwitchToRole($user, $role)) {
            return back()->with('error', 'You do not have permission to switch to this role');
        }

        // Perform role switch
        $switched = $this->roleSwitchingService->switchRole($user, $role);

        if (!$switched) {
            return back()->with('error', 'Failed to switch role. Please try again.');
        }

        // Redirect to appropriate dashboard for new role
        $dashboardRoute = $this->roleSwitchingService->getActiveDashboardRoute($user->fresh());

        return redirect()->route($dashboardRoute)
            ->with('success', 'Switched to ' . ucfirst($role) . ' role successfully');
    }

    /**
     * Get available roles (AJAX)
     */
    public function getAvailableRoles(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $history = $this->roleSwitchingService->getRoleSwitchHistory($user);

        return response()->json([
            'current_role' => $history['current_role'],
            'available_roles' => $history['available_roles'],
            'can_switch' => count($history['available_roles']) > 0,
        ]);
    }
}
