<?php

namespace Modules\Auth\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Auth\Services\RoleSwitchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleSwitchController extends Controller
{
    public function __construct(
        protected RoleSwitchingService $roleSwitchingService
    ) {}

    /**
     * Get available roles for switching (PROMPT 96)
     * 
     * @group Authentication
     * @authenticated
     */
    public function getAvailableRoles(Request $request): JsonResponse
    {
        $user = $request->user();
        $history = $this->roleSwitchingService->getRoleSwitchHistory($user);

        return response()->json([
            'success' => true,
            'data' => [
                'current_role' => $history['current_role'],
                'available_roles' => $history['available_roles'],
                'can_switch' => count($history['available_roles']) > 0,
                'all_assigned_roles' => $history['all_roles'],
                'last_switch' => [
                    'previous_role' => $history['previous_role'],
                    'switched_at' => $history['last_switch_at'],
                ],
            ],
        ]);
    }

    /**
     * Switch active role (PROMPT 96)
     * 
     * @group Authentication
     * @authenticated
     * @bodyParam role string required The role to switch to (admin, vendor, etc)
     */
    public function switchRole(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:super_admin,admin,vendor,subvendor,customer,staff',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role specified',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $targetRole = $request->input('role');

        // Security check
        if (!$this->roleSwitchingService->canSwitchToRole($user, $targetRole)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to switch to this role',
            ], 403);
        }

        // Perform switch
        $switched = $this->roleSwitchingService->switchRole($user, $targetRole);

        if (!$switched) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to switch role',
            ], 500);
        }

        // Revoke current token and create new one with updated role context
        $request->user()->currentAccessToken()->delete();
        $newToken = $user->fresh()->createToken('auth_token', ['role:' . $targetRole])->plainTextToken;

        $history = $this->roleSwitchingService->getRoleSwitchHistory($user->fresh());

        return response()->json([
            'success' => true,
            'message' => 'Role switched successfully',
            'data' => [
                'new_role' => $targetRole,
                'previous_role' => $history['previous_role'],
                'token' => $newToken,
                'token_type' => 'Bearer',
                'permissions' => $this->roleSwitchingService->getActiveRolePermissions($user->fresh()),
            ],
        ]);
    }

    /**
     * Get active role permissions (PROMPT 96)
     * 
     * @group Authentication
     * @authenticated
     */
    public function getActivePermissions(Request $request): JsonResponse
    {
        $user = $request->user();
        $permissions = $this->roleSwitchingService->getActiveRolePermissions($user);

        return response()->json([
            'success' => true,
            'data' => [
                'active_role' => $this->roleSwitchingService->getActiveRole($user),
                'permissions' => $permissions,
            ],
        ]);
    }
}
