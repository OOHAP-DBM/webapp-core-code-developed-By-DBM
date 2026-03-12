<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosBookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected POSBooking $booking) {}

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
        $totalAmount = (float) $this->booking->total_amount;
        $paidAmount = (float) ($this->booking->paid_amount ?? 0);
        $remainingAmount = max(0, $totalAmount - $paidAmount);
        $isFullyPaid = $remainingAmount < 0.01;

        return (new MailMessage)
            ->subject(($isFullyPaid ? 'POS Booking Confirmed' : 'POS Payment Received') . ' - Invoice #' . ($this->booking->invoice_number ?? $this->booking->id))
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line($isFullyPaid
                ? 'Payment has been received and the POS booking is now confirmed.'
                : 'Partial payment has been received for this POS booking.')
            ->line('**Booking ID:** #' . $this->booking->id)
            ->line('**Invoice Number:** ' . ($this->booking->invoice_number ?? ('#' . $this->booking->id)))
            ->line('**Total Amount:** ₹' . number_format($totalAmount, 2))
            ->line('**Paid Amount:** ₹' . number_format($paidAmount, 2))
            ->line('**Payment Status:** ' . ($isFullyPaid ? 'Paid' : 'Partial'))
            ->lineIf(!$isFullyPaid, '**Remaining Amount:** ₹' . number_format($remainingAmount, 2))
            ->action('View Booking', $this->resolveActionUrl($notifiable));
    }

    public function toArray($notifiable): array
    {
        $totalAmount = (float) $this->booking->total_amount;
        $paidAmount = (float) ($this->booking->paid_amount ?? 0);
        $remainingAmount = max(0, $totalAmount - $paidAmount);
        $isFullyPaid = $remainingAmount < 0.01;

        return [
            'type' => 'pos_booking_confirmed',
            'booking_id' => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number,
            'status' => $this->booking->status,
            'payment_status' => $this->booking->payment_status,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'total_amount_formatted' => '₹' . number_format($totalAmount, 2),
            'paid_amount_formatted' => '₹' . number_format($paidAmount, 2),
            'remaining_amount_formatted' => '₹' . number_format($remainingAmount, 2),
            'message' => $isFullyPaid
                ? 'Payment received. POS booking has been confirmed.'
                : 'Partial payment received for POS booking. Awaiting remaining amount.',
            'action_url' => $this->resolveActionUrl($notifiable),
        ];
    }

    protected function resolveActionUrl($notifiable): string
    {
        $notifiableId = (int) ($notifiable->id ?? 0);

        if ($this->hasAnyRole($notifiable, ['admin', 'superadmin', 'super_admin'])) {
            if (Route::has('admin.pos.show')) {
                return route('admin.pos.show', ['id' => $this->booking->id]);
            }

            return url('/admin/pos/bookings/' . $this->booking->id);
        }

        if ($this->hasAnyRole($notifiable, ['vendor']) || ($notifiableId > 0 && $notifiableId === (int) $this->booking->vendor_id)) {
            if (Route::has('vendor.pos.show')) {
                return route('vendor.pos.show', ['id' => $this->booking->id]);
            }

            return url('/vendor/pos/bookings/' . $this->booking->id);
        }

        if (
            ($notifiableId > 0 && $notifiableId === (int) $this->booking->customer_id)
            || $this->hasAnyRole($notifiable, ['customer'])
        ) {
            if (Route::has('customer.bookings.show')) {
                return route('customer.bookings.show', ['id' => $this->booking->id]);
            }

            return url('/customer/bookings/' . $this->booking->id);
        }

        return url('/');
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
