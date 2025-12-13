<?php

namespace App\Policies;

use App\Models\{User, BookingPayment};
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any booking payments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can view the booking payment.
     */
    public function view(User $user, BookingPayment $payment): bool
    {
        // Super admin and admin can view any payment
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Vendor can view payments for their bookings
        if ($user->hasRole('vendor')) {
            $booking = $payment->booking;
            return $booking && $booking->vendor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the booking payment.
     */
    public function update(User $user, BookingPayment $payment): bool
    {
        // Only super admin can update payments normally
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can override the payment.
     * This allows critical financial modifications with audit trail.
     */
    public function override(User $user, BookingPayment $payment): bool
    {
        // Only super admin and admin can override payments
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can change payout status.
     */
    public function changePayoutStatus(User $user, BookingPayment $payment): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can modify commission amounts.
     */
    public function modifyCommission(User $user, BookingPayment $payment): bool
    {
        // Only super admin can modify commission amounts
        return $user->hasRole('super_admin');
    }
}
