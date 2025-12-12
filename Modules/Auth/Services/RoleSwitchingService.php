<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class RoleSwitchingService
{
    /**
     * Get list of roles a user can switch to
     * 
     * SECURITY RULES (PROMPT 96):
     * - Only Admin users can switch between Admin and Vendor roles
     * - Customers can only operate within their own role
     * - Each role must have isolated permissions
     */
    public function getAvailableRoles(User $user): array
    {
        $allRoles = $user->roles()->pluck('name')->toArray();
        
        // RULE 1: Customer cannot switch roles
        if (in_array('customer', $allRoles) && count($allRoles) === 1) {
            return []; // No switching allowed
        }
        
        // RULE 2: Only admins can switch between admin and vendor
        $hasAdmin = in_array('admin', $allRoles) || in_array('super_admin', $allRoles);
        $hasVendor = in_array('vendor', $allRoles) || in_array('subvendor', $allRoles);
        
        if ($hasAdmin && $hasVendor) {
            // Admin with vendor role - can switch between both
            return array_values(array_intersect($allRoles, ['super_admin', 'admin', 'vendor', 'subvendor']));
        }
        
        if ($hasAdmin) {
            // Admin without vendor role - can switch between admin types only
            return array_values(array_intersect($allRoles, ['super_admin', 'admin']));
        }
        
        // RULE 3: Vendor, Staff, and other non-admin roles cannot switch
        return [];
    }

    /**
     * Check if user can switch to a specific role
     */
    public function canSwitchToRole(User $user, string $targetRole): bool
    {
        $availableRoles = $this->getAvailableRoles($user);
        
        // Check if target role is in available roles
        if (!in_array($targetRole, $availableRoles)) {
            return false;
        }
        
        // Verify user actually has the role assigned
        if (!$user->hasRole($targetRole)) {
            return false;
        }
        
        // Prevent switching to same role
        if ($user->active_role === $targetRole) {
            return false;
        }
        
        return true;
    }

    /**
     * Switch user's active role
     */
    public function switchRole(User $user, string $targetRole): bool
    {
        if (!$this->canSwitchToRole($user, $targetRole)) {
            Log::warning('Unauthorized role switch attempt', [
                'user_id' => $user->id,
                'current_role' => $user->active_role,
                'target_role' => $targetRole,
                'ip_address' => request()->ip(),
            ]);
            
            return false;
        }
        
        $previousRole = $user->active_role;
        
        // Update active role
        $user->update([
            'active_role' => $targetRole,
            'previous_role' => $previousRole,
            'last_role_switch_at' => now(),
        ]);
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Regenerate session to prevent session fixation (only for web requests)
        if (Auth::check() && request()->hasSession()) {
            request()->session()->regenerate();
        }
        
        Log::info('Role switched successfully', [
            'user_id' => $user->id,
            'from_role' => $previousRole,
            'to_role' => $targetRole,
            'ip_address' => request()->ip(),
        ]);
        
        return true;
    }

    /**
     * Get current active role for user
     */
    public function getActiveRole(User $user): ?string
    {
        // If active_role is not set, use primary role
        if (!$user->active_role) {
            $primaryRole = $user->getPrimaryRole();
            
            // Set it for future use
            if ($primaryRole) {
                $user->update(['active_role' => $primaryRole]);
            }
            
            return $primaryRole;
        }
        
        // Verify user still has the active role assigned
        if (!$user->hasRole($user->active_role)) {
            // Active role was revoked, fallback to primary role
            $primaryRole = $user->getPrimaryRole();
            $user->update(['active_role' => $primaryRole]);
            
            return $primaryRole;
        }
        
        return $user->active_role;
    }

    /**
     * Get active role permissions
     */
    public function getActiveRolePermissions(User $user): array
    {
        $activeRole = $this->getActiveRole($user);
        
        if (!$activeRole) {
            return [];
        }
        
        $role = Role::findByName($activeRole, 'web');
        
        return $role->permissions()->pluck('name')->toArray();
    }

    /**
     * Get dashboard route for active role
     */
    public function getActiveDashboardRoute(User $user): string
    {
        $activeRole = $this->getActiveRole($user);
        
        return match($activeRole) {
            'super_admin', 'admin' => 'admin.dashboard',
            'vendor', 'subvendor' => 'vendor.dashboard',
            'staff' => 'staff.dashboard',
            default => 'customer.dashboard',
        };
    }

    /**
     * Get layout for active role
     */
    public function getActiveLayout(User $user): string
    {
        $activeRole = $this->getActiveRole($user);
        
        return match($activeRole) {
            'super_admin', 'admin' => 'layouts.admin',
            'vendor', 'subvendor' => 'layouts.vendor',
            'staff' => 'layouts.staff',
            default => 'layouts.customer',
        };
    }

    /**
     * Check if user has permission in active role
     */
    public function hasPermissionInActiveRole(User $user, string $permission): bool
    {
        $activeRolePermissions = $this->getActiveRolePermissions($user);
        
        return in_array($permission, $activeRolePermissions);
    }

    /**
     * Reset to primary role (used on logout or security reset)
     */
    public function resetToPrimaryRole(User $user): void
    {
        $primaryRole = $user->getPrimaryRole();
        
        if ($primaryRole && $user->active_role !== $primaryRole) {
            $user->update([
                'active_role' => $primaryRole,
                'previous_role' => null,
                'last_role_switch_at' => null,
            ]);
            
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    /**
     * Get role switching history for audit
     */
    public function getRoleSwitchHistory(User $user): array
    {
        return [
            'current_role' => $this->getActiveRole($user),
            'previous_role' => $user->previous_role,
            'last_switch_at' => $user->last_role_switch_at,
            'available_roles' => $this->getAvailableRoles($user),
            'all_roles' => $user->roles()->pluck('name')->toArray(),
        ];
    }
}
