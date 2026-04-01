<?php

namespace Modules\POS\Services;

use Illuminate\Support\Facades\Route;

class PosBookingUrlResolver
{
    public function resolve($booking, $notifiable = null): string
    {
        $notifiableId = (int) ($notifiable->id ?? 0);

        // 🔁 Global redirect (highest priority)
        if (Route::has('pos.bookings.redirect')) {
            return route('pos.bookings.redirect', ['id' => $booking->id]);
        }

        // 👑 Admin
        if ($this->hasAnyRole($notifiable, ['admin', 'superadmin', 'super_admin'])) {
            if (Route::has('admin.pos.show')) {
                return route('admin.pos.show', ['id' => $booking->id]);
            }

            return url('/admin/pos/bookings/' . $booking->id);
        }

        // 🏢 Vendor
        if (
            $this->hasAnyRole($notifiable, ['vendor']) ||
            ($notifiableId > 0 && $notifiableId === (int) $booking->vendor_id)
        ) {
            if (Route::has('vendor.pos.bookings.invoice')) {
                return route('vendor.pos.bookings.invoice', ['id' => $booking->id]);
            }

            if (Route::has('vendor.pos.bookings.show')) {
                return route('vendor.pos.bookings.show', ['id' => $booking->id]);
            }

            return url('/vendor/pos/bookings/' . $booking->id);
        }

        // 👤 Customer
        if (
            ($notifiableId > 0 && $notifiableId === (int) $booking->customer_id) ||
            $this->hasAnyRole($notifiable, ['customer'])
        ) {
            if (Route::has('customer.pos.booking.show')) {
                return route('customer.pos.booking.show', ['booking' => $booking->id]);
            }

            return url('/customer/pos-booking/' . $booking->id);
        }

        return url('/');
    }

    private function hasAnyRole($user, array $roles): bool
    {
        if (!$user || !method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasAnyRole($roles);
    }
}