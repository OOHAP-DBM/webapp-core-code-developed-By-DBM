<?php

namespace App\Policies;

use App\Models\{User, QuoteRequest};
use Illuminate\Auth\Access\HandlesAuthorization;

class QuoteRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any quote requests.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'vendor', 'customer']);
    }

    /**
     * Determine whether the user can view the quote request.
     */
    public function view(User $user, QuoteRequest $quote): bool
    {
        // Super admin and admin can view any quote
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Vendor can view quotes assigned to them
        if ($user->hasRole('vendor') && $quote->vendor_id === $user->id) {
            return true;
        }

        // Customer can view their own quote requests
        if ($user->hasRole('customer') && $quote->customer_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create quote requests.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'customer']);
    }

    /**
     * Determine whether the user can update the quote request.
     */
    public function update(User $user, QuoteRequest $quote): bool
    {
        // Super admin can update any quote
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can update quotes
        if ($user->hasRole('admin')) {
            return true;
        }

        // Vendor can update quotes assigned to them
        if ($user->hasRole('vendor') && $quote->vendor_id === $user->id) {
            return !in_array($quote->status, ['accepted', 'closed']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the quote request.
     */
    public function delete(User $user, QuoteRequest $quote): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can override the quote request.
     */
    public function override(User $user, QuoteRequest $quote): bool
    {
        // Only super admin and admin can override quotes
        return $user->hasAnyRole(['super_admin', 'admin']);
    }
}
