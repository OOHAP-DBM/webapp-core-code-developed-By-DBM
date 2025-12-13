<?php

namespace App\Policies;

use App\Models\{User, Booking};
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'vendor']);
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Super admin and admin can view any booking
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Vendor can view their own bookings
        if ($user->hasRole('vendor') && $booking->vendor_id === $user->id) {
            return true;
        }

        // Customer can view their own bookings
        if ($user->hasRole('customer') && $booking->customer_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'customer']);
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Super admin can update any booking
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can update most bookings
        if ($user->hasRole('admin')) {
            return true;
        }

        // Vendor can update certain fields of their bookings
        if ($user->hasRole('vendor') && $booking->vendor_id === $user->id) {
            return in_array($booking->status, ['pending_payment_hold', 'confirmed']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Only super admin can delete bookings
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can override the booking.
     * This is a special permission for critical administrative actions.
     */
    public function override(User $user, Booking $booking): bool
    {
        // Only super admin and admin can override bookings
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can change booking status.
     */
    public function changeStatus(User $user, Booking $booking): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can change payment status.
     */
    public function changePaymentStatus(User $user, Booking $booking): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }
}
