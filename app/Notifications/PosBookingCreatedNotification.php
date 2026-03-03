<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\POS\Models\POSBooking;

class PosBookingCreatedNotification extends Notification implements ShouldQueue
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
        $notifiableId = (int) ($notifiable->id ?? 0);

        if ($notifiableId > 0 && $notifiableId === (int) $this->booking->customer_id) {
            return url('/customer/bookings/' . $this->booking->id);
        }

        if ($notifiableId > 0 && $notifiableId === (int) $this->booking->vendor_id) {
            return url('/vendor/pos/bookings/' . $this->booking->id);
        }

        if (method_exists($notifiable, 'hasRole')) {
            if ($notifiable->hasRole('vendor') || $notifiable->hasRole('admin') || $notifiable->hasRole('super_admin')) {
                return url('/vendor/pos/bookings/' . $this->booking->id);
            }
        }

        return url('/customer/bookings/' . $this->booking->id);
    }
}