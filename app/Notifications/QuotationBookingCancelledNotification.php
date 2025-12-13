<?php

namespace App\Notifications;

use App\Models\Quotation;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PROMPT 106: Quotation Booking Cancelled Notification
 * 
 * Notifies customer and vendor when a booking is auto-cancelled due to quotation expiry
 */
class QuotationBookingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Quotation $quotation;
    protected Booking $booking;

    public function __construct(Quotation $quotation, Booking $booking)
    {
        $this->quotation = $quotation;
        $this->booking = $booking;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isCustomer = $notifiable->id === $this->booking->customer_id;

        return (new MailMessage)
            ->subject('Booking Cancelled - Quotation Expired')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->getMessageForRole($isCustomer))
            ->line('**Booking Details:**')
            ->line('Booking ID: #' . $this->booking->id)
            ->line('Booking Number: ' . ($this->booking->booking_number ?? 'N/A'))
            ->line('Amount: $' . number_format($this->booking->total_amount, 2))
            ->line('Status: Cancelled')
            ->line('**Quotation Details:**')
            ->line('Quotation ID: #' . $this->quotation->id)
            ->line('Expired On: ' . ($this->quotation->offer->expires_at ? $this->quotation->offer->expires_at->format('M d, Y g:i A') : 'N/A'))
            ->line('**Cancellation Reason:** Quotation expired before payment completion.')
            ->line($this->getActionTextForRole($isCustomer))
            ->action(
                $isCustomer ? 'View Bookings' : 'View Bookings',
                $isCustomer ? url('/customer/bookings') : url('/vendor/bookings')
            )
            ->line('Thank you for using our platform!');
    }

    public function toDatabase($notifiable): array
    {
        $isCustomer = $notifiable->id === $this->booking->customer_id;

        return [
            'type' => 'booking_auto_cancelled',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'quotation_id' => $this->quotation->id,
            'customer_id' => $this->booking->customer_id,
            'vendor_id' => $this->booking->vendor_id,
            'amount' => $this->booking->total_amount,
            'cancelled_at' => $this->booking->cancelled_at?->toIso8601String(),
            'message' => $this->getMessageForRole($isCustomer),
        ];
    }

    protected function getMessageForRole(bool $isCustomer): string
    {
        if ($isCustomer) {
            return sprintf(
                'Your booking (#%s) has been automatically cancelled because the quotation expired before payment was completed. The quotation deadline passed on %s.',
                $this->booking->booking_number ?? $this->booking->id,
                $this->quotation->offer->expires_at ? $this->quotation->offer->expires_at->format('M d, Y') : 'N/A'
            );
        }

        return sprintf(
            'Booking #%s from %s has been automatically cancelled because the quotation expired before payment completion.',
            $this->booking->booking_number ?? $this->booking->id,
            $this->booking->customer->name ?? 'the customer'
        );
    }

    protected function getActionTextForRole(bool $isCustomer): string
    {
        if ($isCustomer) {
            return 'If you would like to proceed with this booking, please request a new quotation from the vendor.';
        }

        return 'No action is required. The hoarding slot has been released back to your inventory.';
    }
}
