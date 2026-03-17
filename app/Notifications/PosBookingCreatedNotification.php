<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosBookingCreatedNotification extends Notification
{
    use Queueable;

    public POSBooking $booking;

    /** True when the booking was created as a credit note */
    protected bool $isCreditNote;

    public function __construct(POSBooking $booking)
    {
        $this->booking      = $booking;
        $this->isCreditNote = ($booking->payment_mode === POSBooking::PAYMENT_MODE_CREDIT_NOTE);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            // ── Type changes for credit note so receivers can route accordingly ──
            'type'    => $this->isCreditNote
                ? 'pos_credit_note_booking_created'
                : 'pos_booking_created',

            'title'   => $this->resolveTitle($notifiable),
            'message' => $this->resolveMessage($notifiable),

            // Core booking data
            'booking_id'      => $this->booking->id,
            'invoice_number'  => $this->booking->invoice_number,
            'total_amount'    => $this->booking->total_amount,
            'start_date'      => $this->booking->start_date,
            'end_date'        => $this->booking->end_date,
            'customer_name'   => $this->booking->customer_name,
            'payment_mode'    => $this->booking->payment_mode,
            'booking_status'  => $this->booking->status,

            // Credit note extras (null for non-credit-note bookings)
            'credit_note_number'   => $this->booking->credit_note_number   ?? null,
            'credit_note_due_date' => $this->booking->credit_note_due_date
                ? (string) $this->booking->credit_note_due_date
                : null,

            'action_url' => $this->resolveActionUrl($notifiable),
        ];
    }

    /* ── Title ─────────────────────────────────────────────────── */
    protected function resolveTitle($notifiable): string
    {
        if ($this->isCreditNote) {
            if ($this->hasAnyRole($notifiable, ['admin', 'super_admin'])) {
                return 'New Credit Note Booking Created';
            }
            if ($this->hasAnyRole($notifiable, ['vendor'])) {
                return 'Credit Note Booking Created';
            }
            return 'Booking Confirmed (Credit Note)';
        }

        // Standard booking titles
        if ($this->hasAnyRole($notifiable, ['admin', 'super_admin'])) {
            return 'New POS Booking Created';
        }
        if ($this->hasAnyRole($notifiable, ['vendor'])) {
            return 'POS Booking Created';
        }

        return 'Booking Created';
    }

    /* ── Message ────────────────────────────────────────────────── */
    protected function resolveMessage($notifiable): string
    {
        $invoiceNumber = $this->booking->invoice_number ?? ('#' . $this->booking->id);

        if ($this->isCreditNote) {
            $creditNoteNumber = $this->booking->credit_note_number ?? 'N/A';
            $dueDate = $this->booking->credit_note_due_date
                ? \Carbon\Carbon::parse($this->booking->credit_note_due_date)->format('d M Y')
                : 'N/A';

            if ($this->hasAnyRole($notifiable, ['admin', 'super_admin'])) {
                return "A new credit note POS booking has been confirmed. Invoice: {$invoiceNumber}, Credit Note: {$creditNoteNumber}, Due: {$dueDate}";
            }
            if ($this->hasAnyRole($notifiable, ['vendor'])) {
                return "Credit note booking confirmed. Invoice: {$invoiceNumber}, Credit Note: {$creditNoteNumber}, Due: {$dueDate}";
            }
            return "Your booking has been confirmed with a credit note. Invoice: {$invoiceNumber}, Credit Note: {$creditNoteNumber}, Payment due by: {$dueDate}";
        }

        // Standard messages
        if ($this->hasAnyRole($notifiable, ['admin', 'super_admin'])) {
            return "A new POS booking has been created. Invoice: {$invoiceNumber}";
        }
        if ($this->hasAnyRole($notifiable, ['vendor'])) {
            return "A POS booking was created successfully. Invoice: {$invoiceNumber}";
        }

        return "Your booking has been created successfully. Invoice: {$invoiceNumber}";
    }

    /* ── Action URL ─────────────────────────────────────────────── */
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

    /* ── Role helper ────────────────────────────────────────────── */
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