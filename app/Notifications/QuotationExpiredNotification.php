<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PROMPT 106: Quotation Expired Notification
 * 
 * Notifies customer and vendor when a quotation has expired
 */
class QuotationExpiredNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Quotation Expired - #' . $this->quotation->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->getMessageForRole($isCustomer))
            ->line('**Quotation Details:**')
            ->line('Quotation ID: #' . $this->quotation->id)
            ->line('Offer ID: #' . $this->quotation->offer_id)
            ->line('Amount: $' . number_format($this->quotation->grand_total, 2))
            ->line('Expired On: ' . ($this->quotation->offer->expires_at ? $this->quotation->offer->expires_at->format('M d, Y g:i A') : 'N/A'))
            ->line('**Important:** Any related booking requests have been automatically cancelled.')
            ->line($this->getActionTextForRole($isCustomer))
            ->action(
                $isCustomer ? 'View Enquiries' : 'View Quotations',
                $isCustomer ? url('/customer/enquiries') : url('/vendor/quotations')
            )
            ->line('Thank you for using our platform!');
    }

    public function toDatabase($notifiable): array
    {
        $isCustomer = $notifiable->id === $this->quotation->customer_id;

        return [
            'type' => 'quotation_expired',
            'quotation_id' => $this->quotation->id,
            'offer_id' => $this->quotation->offer_id,
            'customer_id' => $this->quotation->customer_id,
            'vendor_id' => $this->quotation->vendor_id,
            'amount' => $this->quotation->grand_total,
            'expired_at' => $this->quotation->offer->expires_at?->toIso8601String(),
            'message' => $this->getMessageForRole($isCustomer),
        ];
    }

    protected function getMessageForRole(bool $isCustomer): string
    {
        if ($isCustomer) {
            return sprintf(
                'The quotation from %s has expired as the acceptance deadline has passed. Any booking requests related to this quotation have been automatically cancelled.',
                $this->quotation->vendor->name ?? 'the vendor'
            );
        }

        return sprintf(
            'Your quotation to %s has expired as the customer did not respond within the deadline. Any pending bookings have been automatically cancelled.',
            $this->quotation->customer->name ?? 'the customer'
        );
    }

    protected function getActionTextForRole(bool $isCustomer): string
    {
        if ($isCustomer) {
            return 'If you are still interested, please contact the vendor for a new quotation or create a new enquiry.';
        }

        return 'If the customer reaches out again, you can create a new offer and quotation.';
    }
}
