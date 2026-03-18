<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosBookingCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected POSBooking $booking,
        protected array $hoardingTitles = [],
        protected ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $resolvedReason = trim((string) ($this->reason ?? $this->booking->cancellation_reason ?? ''));

        return [
            'type' => 'pos_booking_cancelled',
            'title' => $this->resolveTitle($notifiable),
            'message' => $this->resolveMessage($notifiable, $resolvedReason),
            'booking_id' => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number,
            'status' => $this->booking->status,
            'cancelled_at' => $this->booking->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $resolvedReason !== '' ? $resolvedReason : null,
            'hoarding_title' => $this->resolveHoardingPreview(),
            'hoarding_titles' => $this->resolveHoardingTitles(),
            'action_url' => $this->resolveActionUrl($notifiable),
        ];
    }

    protected function resolveTitle($notifiable): string
    {
        if ($this->hasAnyRole($notifiable, ['vendor'])) {
            return 'POS Booking Cancelled';
        }

        if ($this->hasAnyRole($notifiable, ['admin', 'superadmin', 'super_admin'])) {
            return 'POS Booking Cancelled';
        }

        return 'Your POS Booking Has Been Cancelled';
    }

    protected function resolveMessage($notifiable, string $reason): string
    {
        $invoiceNumber = $this->booking->invoice_number ?? ('#' . $this->booking->id);
        $hoardingPreview = $this->resolveHoardingPreview();

        if ($this->hasAnyRole($notifiable, ['vendor'])) {
            $message = "POS booking {$invoiceNumber} has been cancelled. Hoarding: {$hoardingPreview}.";
        } elseif ($this->hasAnyRole($notifiable, ['admin', 'superadmin', 'super_admin'])) {
            $message = "POS booking {$invoiceNumber} has been cancelled. Hoarding: {$hoardingPreview}.";
        } else {
            $message = "Your POS booking {$invoiceNumber} has been cancelled. Hoarding: {$hoardingPreview}.";
        }

        if ($reason !== '') {
            $message .= ' Reason: ' . $reason;
        }

        return $message;
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

    protected function resolveHoardingTitles(): array
    {
        $titles = collect($this->hoardingTitles)
            ->map(function ($title) {
                return trim((string) $title);
            })
            ->filter()
            ->unique()
            ->values();

        if ($titles->isEmpty()) {
            if (!$this->booking->relationLoaded('bookingHoardings')) {
                $this->booking->load('bookingHoardings.hoarding');
            }

            $titles = $this->booking->bookingHoardings
                ->map(function ($bookingHoarding) {
                    return trim((string) ($bookingHoarding->hoarding->title ?? ''));
                })
                ->filter()
                ->unique()
                ->values();
        }

        if ($titles->isEmpty() && !empty($this->booking->hoarding?->title)) {
            $titles = collect([trim((string) $this->booking->hoarding->title)]);
        }

        return $titles->all();
    }

    protected function resolveHoardingPreview(): string
    {
        $titles = $this->resolveHoardingTitles();

        if (empty($titles)) {
            return 'N/A';
        }

        $preview = array_slice($titles, 0, 2);
        $remaining = count($titles) - count($preview);
        $label = implode(', ', $preview);

        if ($remaining > 0) {
            $label .= ' +' . $remaining . ' more';
        }

        return $label;
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
