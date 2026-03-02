<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\POS\Models\POSBooking;

class PosBookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected POSBooking $booking)
    {
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        if (($notifiable->notification_email ?? true) && !empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('POS Booking Confirmed - Invoice #' . ($this->booking->invoice_number ?? $this->booking->id))
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line('Payment has been received and the POS booking is now confirmed.')
            ->line('**Booking ID:** #' . $this->booking->id)
            ->line('**Invoice Number:** ' . ($this->booking->invoice_number ?? ('#' . $this->booking->id)))
            ->line('**Total Amount:** ₹' . number_format((float) $this->booking->total_amount, 2))
            ->line('**Paid Amount:** ₹' . number_format((float) ($this->booking->paid_amount ?? 0), 2))
            ->action('View Booking', $this->resolveActionUrl($notifiable));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'pos_booking_confirmed',
            'booking_id' => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number,
            'status' => $this->booking->status,
            'payment_status' => $this->booking->payment_status,
            'total_amount' => (float) $this->booking->total_amount,
            'paid_amount' => (float) ($this->booking->paid_amount ?? 0),
            'message' => 'Payment received. POS booking has been confirmed.',
            'url' => $this->resolveActionUrl($notifiable),
        ];
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
