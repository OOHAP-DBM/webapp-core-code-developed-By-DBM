<?php

namespace Modules\Offers\Policies;

use App\Models\User;
use Modules\Offers\Models\Offer;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfferPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any offers.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'vendor']);
    }

    /**
     * Determine whether the user can view the offer.
     */
    public function view(User $user, Offer $offer): bool
    {
        // Super admin and admin can view any offer
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Vendor can view their own offers
        if ($user->hasRole('vendor') && $offer->vendor_id === $user->id) {
            return true;
        }

        // Customer can view offers sent to them
        if ($user->hasRole('customer') && $offer->customer_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create offers.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'vendor']);
    }

    /**
     * Determine whether the user can update the offer.
     */
    public function update(User $user, Offer $offer): bool
    {
        // Super admin can update any offer
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can update offers
        if ($user->hasRole('admin')) {
            return true;
        }

        // Vendor can update their own offers if not yet accepted
        if ($user->hasRole('vendor') && $offer->vendor_id === $user->id) {
            return !in_array($offer->status, ['accepted', 'completed']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the offer.
     */
    public function delete(User $user, Offer $offer): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can override the offer.
     */
    public function override(User $user, Offer $offer): bool
    {
        // Only super admin and admin can override offers
        return $user->hasAnyRole(['super_admin', 'admin']);
    }
}
