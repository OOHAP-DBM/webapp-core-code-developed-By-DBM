<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\POS\Models\POSBooking;

class POSPaymentReminderNotification extends Notification implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use Queueable;

    public function __construct(protected POSBooking $booking, protected int $reminderCount = 1) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $remainingAmount = max(0, (float) $this->booking->total_amount - (float) ($this->booking->paid_amount ?? 0));
        $remainingFormatted = number_format($remainingAmount, 2);
        $paidFormatted = number_format($this->booking->paid_amount ?? 0, 2);
        $totalFormatted = number_format($this->booking->total_amount, 2);

        $url = route('pos.booking.detail', ['id' => $this->booking->id]);

        return (new MailMessage)
            ->subject('Payment Reminder - Invoice #' . $this->booking->invoice_number)
            ->greeting("Hello {$this->booking->customer_name},")
            ->line("This is a payment reminder for your POS booking.")
            ->line("")
            ->line("**Booking Details:**")
            ->line("Invoice Number: #{$this->booking->invoice_number}")
            ->line("Booking Status: " . ucfirst($this->booking->status))
            ->line("")
            ->line("**Payment Summary:**")
            ->line("Total Amount: ₹{$totalFormatted}")
            ->line("Paid So Far: ₹{$paidFormatted}")
            ->line("Remaining Balance: ₹{$remainingFormatted}")
            ->line("")
            ->line("Reminder Count: {$this->reminderCount}/3")
            ->line("")
            ->action('View Booking Details', $url)
            ->line('Please settle the outstanding balance at your earliest convenience.')
            ->line('Thank you for your business!');
    }
}
