<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosBookingCreatedNotification extends Notification
{
    use Queueable;

    public $booking;

    public function __construct(POSBooking $booking)
    {
        $this->booking = $booking;
    }

    // Sirf database channel
    public function via($notifiable)
    {
        return ['database'];
    }

    // Sirf DB mein save hoga
    public function toDatabase($notifiable)
    {
        $title = $this->resolveTitle($notifiable);
        // $message = $this->resolveMessage($notifiable);

        return [
            'type' => 'pos_booking_created',
            'title' => $title,
            'message' => "Pos Booking Created: " . ($this->booking->invoice_number ?? ('#' . $this->booking->id)),
            'booking_id'     => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number,
            'total_amount'   => $this->booking->total_amount,
            'start_date'     => $this->booking->start_date,
            'end_date'       => $this->booking->end_date,
            'customer_name'  => $this->booking->customer_name,
            'action_url'     => $this->resolveActionUrl($notifiable),
        ];
    }

    protected function resolveTitle($notifiable): string
    {
        if (method_exists($notifiable, 'hasRole')) {
            if ($notifiable->hasRole('admin') || $notifiable->hasRole('super_admin')) {
                return 'New POS Booking Created';
            }

            if ($notifiable->hasRole('vendor')) {
                return 'POS Booking Created';
            }
        }

        return 'Booking Created';
    }

    protected function resolveMessage($notifiable): string
    {
        $invoiceNumber = $this->booking->invoice_number ?? ('#' . $this->booking->id);

        if (method_exists($notifiable, 'hasRole')) {
            if ($notifiable->hasRole('admin') || $notifiable->hasRole('super_admin')) {
                return 'A new POS booking has been created. Invoice: ' . $invoiceNumber;
            }

            if ($notifiable->hasRole('vendor')) {
                return 'A POS booking was created successfully. Invoice: ' . $invoiceNumber;
            }
        }

        return 'Your booking has been created successfully. Invoice: ' . $invoiceNumber;
    }

    protected function resolveActionUrl($notifiable): string
    {
        $bookingId = (int) $this->booking->id;

        if ($bookingId <= 0) {
            return '/';
        }

        if (Route::has('pos.bookings.redirect')) {
            return route('pos.bookings.redirect', ['id' => $bookingId], false);
        }

        $notifiableId = (int) ($notifiable->id ?? 0);


        if ($this->hasAnyRole($notifiable, ['admin', 'superadmin', 'super_admin'])) {
            return '/admin/pos/bookings/' . $bookingId;
        }

        if (
            ($notifiableId > 0 && $notifiableId === (int) $this->booking->customer_id)
            || $this->hasAnyRole($notifiable, ['customer'])
        ) {
            if (Route::has('customer.pos.booking.show')) {
                return route('customer.pos.booking.show', ['booking' => $this->booking->id], false);
            }

            return '/customer/pos-booking/' . $this->booking->id;
        }

        if ($this->hasAnyRole($notifiable, ['vendor']) || ($notifiableId > 0 && $notifiableId === (int) $this->booking->vendor_id)) {
            return '/vendor/pos/bookings/' . $bookingId;
        }

        return '/';
    }

    protected function hasAnyRole($notifiable, array $roles): bool
    {
        if (method_exists($notifiable, 'hasAnyRole')) {
            return (bool) $notifiable->hasAnyRole($roles);
        }

        if (!method_exists($notifiable, 'hasRole')) {
            return false;
        }

        foreach ($roles as $role) {
            if ($notifiable->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
