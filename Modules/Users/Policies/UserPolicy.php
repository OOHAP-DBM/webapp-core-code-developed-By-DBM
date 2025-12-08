<?php

namespace Modules\Users\Policies;

use Modules\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Super admin and admin can view any user
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Users can view their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Super admin can update anyone
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can update non-super-admin users
        if ($user->hasRole('admin') && !$model->hasRole('super_admin')) {
            return true;
        }

        // Users can update their own profile (except role and status)
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Super admin can delete anyone (except themselves)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can delete non-super-admin and non-admin users
        if ($user->hasRole('admin') && !$model->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only super admin can force delete
        return $user->hasRole('super_admin') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can change status of the model.
     */
    public function changeStatus(User $user, User $model): bool
    {
        // Cannot change own status
        if ($user->id === $model->id) {
            return false;
        }

        // Super admin can change anyone's status
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can change non-super-admin and non-admin status
        if ($user->hasRole('admin') && !$model->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can assign roles to the model.
     */
    public function assignRole(User $user, User $model): bool
    {
        // Super admin can assign any role
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can assign roles (except super_admin and admin)
        if ($user->hasRole('admin') && !$model->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return false;
    }
}

