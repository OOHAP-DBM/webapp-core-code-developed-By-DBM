<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Modules\POS\Models\POSBooking;

class PosBookingHoldExpiredNotification extends Notification implements ShouldQueue
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
        $holdExpiryAt = $this->resolveHoldExpiryAt();
        $holdExpiredAt = $holdExpiryAt
            ? $holdExpiryAt->format('d M Y, h:i A')
            : 'N/A';

        $isCustomer = (int) ($notifiable->id ?? 0) === (int) $this->booking->customer_id;
        $body = $isCustomer
            ? 'Your POS booking hold has expired because payment was not completed within the hold window.'
            : 'A POS booking hold has expired because payment was not completed within the hold window.';

        $mailData = [
            'greeting' => 'Hello ' . ($notifiable->name ?? 'Customer') . ',',
            'body' => $body,
            'booking_id' => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number ?? ('#' . $this->booking->id),
            'amount' => '₹' . number_format((float) $this->booking->total_amount, 2),
            'payment_mode' => strtoupper((string) $this->booking->payment_mode),
            'hold_expired_at' => $holdExpiredAt,
            'action_url' => $this->resolveActionUrl($notifiable),
            'action_text' => 'View Booking',
        ];

        return (new MailMessage)
            ->subject('POS Booking Hold Expired - Invoice #' . ($this->booking->invoice_number ?? $this->booking->id))
            ->view('emails.pos.booking_hold_expired', $mailData);
    }

    public function toArray($notifiable): array
    {
        $holdExpiryAt = $this->resolveHoldExpiryAt();

        return [
            'type' => 'pos_booking_hold_expired',
            'booking_id' => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number,
            'status' => $this->booking->status,
            'payment_status' => $this->booking->payment_status,
            'total_amount' => (float) $this->booking->total_amount,
            'payment_mode' => $this->booking->payment_mode,
            'hold_expiry_at' => $holdExpiryAt?->toIso8601String(),
            'message' => 'Your POS booking hold expired and the booking was automatically cancelled.',
            'action_url' => $this->resolveActionUrl($notifiable),
        ];
    }

    protected function resolveHoldExpiryAt(): ?Carbon
    {
        $value = $this->booking->hold_expiry_at;

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function resolveActionUrl($notifiable): string
    {
        $notifiableId = (int) ($notifiable->id ?? 0);

        // Customer
        if ($notifiableId > 0 && $notifiableId === (int) $this->booking->customer_id) {
            return url('/customer/pos/bookings/' . $this->booking->id);
        }

        // Vendor or Admin
        if (
            ($notifiableId > 0 && $notifiableId === (int) $this->booking->vendor_id) ||
            (method_exists($notifiable, 'hasRole') && (
                $notifiable->hasRole('vendor') ||
                $notifiable->hasRole('admin') ||
                $notifiable->hasRole('super_admin')
            ))
        ) {
            return url('/vendor/pos/bookings/' . $this->booking->id);
        }

        // Default fallback
        return url('/customer/pos/bookings/' . $this->booking->id);
    }
}
