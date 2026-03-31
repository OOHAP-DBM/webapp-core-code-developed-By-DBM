<?php

namespace Modules\POS\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\POS\Models\POSBooking;

class PosCreditNoteCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public POSBooking $booking;
    public ?string $reason;

    public function __construct(POSBooking $booking, ?string $reason = null)
    {
        $this->booking = $booking;
        $this->reason  = $reason;
    }

    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (!empty($notifiable->email) && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $bookingRef  = $this->booking->invoice_number ?? $this->booking->id;
        $creditNote  = $this->booking->credit_note_number ?? 'N/A';
        $role        = resolveUserRole($notifiable);
        $actionUrl   = resolveBookingActionUrl($notifiable, $this->booking->id);
        $actionText  = resolveBookingActionText($notifiable);
        $vendorName  = $this->booking->vendor?->name ?? 'The vendor';
        $customerName = $this->booking->customer_name ?? 'The customer';
        $totalAmount = '₹' . number_format($this->booking->total_amount, 2);

        $mail = new MailMessage;

        match ($role) {

            // ── Customer ─────────────────────────────────────────────
            'customer' => $mail
                ->subject("Your Debit Note Has Been Cancelled — Booking #{$bookingRef}")
                ->greeting("Hello " . ($notifiable->name ?? 'there') . ",")
                ->line("We wanted to inform you that the debit note associated with your booking **#{$bookingRef}** has been cancelled by **{$vendorName}**.")
                ->line("**Booking Details:**")
                ->line("Credit Note Number: **{$creditNote}**")
                ->line("Total Amount: **{$totalAmount}**"),

            // ── Vendor ────────────────────────────────────────────────
            'vendor' => $mail
                ->subject("Debit Note Cancelled Successfully — Booking #{$bookingRef}")
                ->greeting("Hello " . ($notifiable->name ?? 'there') . ",")
                ->line("You have successfully cancelled the debit note for booking **#{$bookingRef}**.")
                ->line("**Booking Details:**")
                ->line("Customer: **{$customerName}**")
                ->line("Credit Note Number: **{$creditNote}**")
                ->line("Total Amount: **{$totalAmount}**"),

            // ── Admin / Super Admin ───────────────────────────────────
            default => $mail
                ->subject("[Admin] Debit Note Cancelled — Booking #{$bookingRef}")
                ->greeting("Hello " . ($notifiable->name ?? 'Admin') . ",")
                ->line("A debit note has been cancelled by **{$vendorName}** for booking **#{$bookingRef}**.")
                ->line("**Booking Details:**")
                ->line("Customer: **{$customerName}**")
                ->line("Credit Note Number: **{$creditNote}**")
                ->line("Total Amount: **{$totalAmount}**"),
        };

        if ($this->reason) {
            $mail->line("**Reason:** {$this->reason}");
        }

        if ($actionUrl) {
            $mail->action($actionText, $actionUrl);
        }

        return $mail
            ->line('If you have any questions, please contact us.')
            ->salutation('Thanks, ' . config('app.name'));
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage($this->toArray($notifiable));
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable): array
    {
        $role = resolveUserRole($notifiable);

        return [
            'type'               => 'credit_note_cancelled',
            'booking_id'         => $this->booking->id,
            'invoice_number'     => $this->booking->invoice_number,
            'credit_note_number' => $this->booking->credit_note_number,
            'reason'             => $this->reason,
            'message'            => $this->getMessage($role),
            'action_url'         => resolveBookingActionUrl($notifiable, $this->booking->id),
        ];
    }

    protected function getMessage(string $role = 'unknown'): string
    {
        $bookingRef  = $this->booking->invoice_number ?? $this->booking->id;
        $vendorName  = $this->booking->vendor?->name ?? 'The vendor';
        $reasonSuffix = $this->reason ? ' Reason: ' . $this->reason : '';

        return match ($role) {
            'customer'             => "The debit note for your booking #{$bookingRef} has been cancelled by {$vendorName}.{$reasonSuffix}",
            'vendor'               => "You have successfully cancelled the debit note for booking #{$bookingRef}.{$reasonSuffix}",
            'admin', 'super_admin' => "{$vendorName} cancelled the debit note for booking #{$bookingRef}.{$reasonSuffix}",
            default                => "The debit note for booking #{$bookingRef} has been cancelled.{$reasonSuffix}",
        };
    }
}