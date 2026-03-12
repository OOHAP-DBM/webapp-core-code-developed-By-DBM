<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosPaymentReminderInAppNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected POSBooking $booking,
        protected int $reminderCount = 1
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $totalAmount = (float) ($this->booking->total_amount ?? 0);
        $paidAmount = (float) ($this->booking->paid_amount ?? 0);
        $remainingAmount = max(0, $totalAmount - $paidAmount);

        return [
            'type' => 'pos_payment_reminder',
            'title' => 'Payment Reminder',
            'message' => 'Payment reminder for invoice #' . ($this->booking->invoice_number ?? $this->booking->id) . ' - Outstanding ₹' . number_format($remainingAmount, 2),
            'booking_id' => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number,
            'status' => $this->booking->status,
            'payment_status' => $this->booking->payment_status,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'reminder_count' => $this->reminderCount,
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
