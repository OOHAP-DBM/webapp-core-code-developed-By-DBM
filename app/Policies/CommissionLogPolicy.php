<?php

namespace App\Policies;

use App\Models\{User, CommissionLog};
use Illuminate\Auth\Access\HandlesAuthorization;

class CommissionLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any commission logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can view the commission log.
     */
    public function view(User $user, CommissionLog $log): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can override the commission log.
     * Commission logs are immutable by default - override bypasses this.
     */
    public function override(User $user, CommissionLog $log): bool
    {
        // Only super admin can override commission logs
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can delete commission logs.
     */
    public function delete(User $user, CommissionLog $log): bool
    {
        // Commission logs should never be deleted
        return false;
    }
}
