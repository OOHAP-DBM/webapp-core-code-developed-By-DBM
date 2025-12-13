<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PROMPT 106: Quotation Expiry Warning Notification
 * 
 * Warns customer and vendor that a quotation will expire soon
 */
class QuotationExpiryWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Quotation $quotation;

    public function __construct(Quotation $quotation)
    {
        $this->quotation = $quotation;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isCustomer = $notifiable->id === $this->quotation->customer_id;
        $daysRemaining = $this->quotation->offer->getDaysRemaining();

        return (new MailMessage)
            ->subject('⚠️ Quotation Expiring Soon - #' . $this->quotation->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->getWarningMessage($isCustomer, $daysRemaining))
            ->line('**Quotation Details:**')
            ->line('Quotation ID: #' . $this->quotation->id)
            ->line('Amount: $' . number_format($this->quotation->grand_total, 2))
            ->line('Expires In: ' . $this->quotation->offer->getExpiryLabel())
            ->line('Expiry Date: ' . ($this->quotation->offer->expires_at ? $this->quotation->offer->expires_at->format('M d, Y g:i A') : 'N/A'))
            ->line('**⚠️ Important:** After expiry, this quotation and any related booking requests will be automatically cancelled.')
            ->action(
                $isCustomer ? 'View Quotation' : 'View Quotation',
                url('/quotations/' . $this->quotation->id)
            )
            ->line($this->getActionTextForRole($isCustomer));
    }

    public function toDatabase($notifiable): array
    {
        $isCustomer = $notifiable->id === $this->quotation->customer_id;
        $daysRemaining = $this->quotation->offer->getDaysRemaining();

        return [
            'type' => 'quotation_expiry_warning',
            'quotation_id' => $this->quotation->id,
            'offer_id' => $this->quotation->offer_id,
            'customer_id' => $this->quotation->customer_id,
            'vendor_id' => $this->quotation->vendor_id,
            'amount' => $this->quotation->grand_total,
            'expires_at' => $this->quotation->offer->expires_at?->toIso8601String(),
            'days_remaining' => $daysRemaining,
            'message' => $this->getWarningMessage($isCustomer, $daysRemaining),
        ];
    }

    protected function getWarningMessage(bool $isCustomer, ?int $daysRemaining): string
    {
        $timeText = $daysRemaining === 0 ? 'today' : 
                    ($daysRemaining === 1 ? 'tomorrow' : 
                    "in {$daysRemaining} days");

        if ($isCustomer) {
            return sprintf(
                'The quotation from %s will expire %s. Please complete your booking and payment before the deadline to avoid automatic cancellation.',
                $this->quotation->vendor->name ?? 'the vendor',
                $timeText
            );
        }

        return sprintf(
            'Your quotation to %s will expire %s. If the customer does not respond, the quotation and any pending bookings will be automatically cancelled.',
            $this->quotation->customer->name ?? 'the customer',
            $timeText
        );
    }

    protected function getActionTextForRole(bool $isCustomer): string
    {
        if ($isCustomer) {
            return 'Please review and accept the quotation as soon as possible. Complete payment promptly to secure your booking.';
        }

        return 'You may want to follow up with the customer to remind them of the upcoming deadline.';
    }
}
