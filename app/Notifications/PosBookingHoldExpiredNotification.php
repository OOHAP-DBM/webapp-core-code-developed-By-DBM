<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosBookingHoldExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(protected POSBooking $booking) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        $email = $notifiable->email ?? null;

        if (empty($email) && method_exists($notifiable, 'routeNotificationFor')) {
            $email = $notifiable->routeNotificationFor('mail');
        }

        if (($notifiable->notification_email ?? true) && !empty($email)) {
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
        if (Route::has('pos.bookings.redirect')) {
            return route('pos.bookings.redirect', ['id' => $this->booking->id]);
        }

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

        if (Route::has('customer.bookings.show')) {
            return route('customer.bookings.show', ['id' => $this->booking->id]);
        }

        return url('/customer/bookings/' . $this->booking->id);
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
