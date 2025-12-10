<?php

namespace App\Notifications;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteRequestClosedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $quoteRequest;

    public function __construct(QuoteRequest $quoteRequest)
    {
        $this->quoteRequest = $quoteRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Quote Request Closed - ' . $this->quoteRequest->request_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your quote request has been closed.')
            ->line('**Request Details:**')
            ->line('Request Number: ' . $this->quoteRequest->request_number)
            ->line('Hoarding: ' . $this->quoteRequest->hoarding->title);

        if ($this->quoteRequest->selected_quote_id) {
            $selectedQuote = $this->quoteRequest->selectedQuote;
            $mail->line('')
                ->line('**Selected Quote:**')
                ->line('Quote Number: ' . $selectedQuote->quote_number)
                ->line('Vendor: ' . $selectedQuote->vendor->name)
                ->line('Amount: ₹' . number_format($selectedQuote->grand_total, 2));
        }

        $mail->line('Total Quotes Received: ' . $this->quoteRequest->quotes_received_count)
            ->action('View Request', url('/quote-requests/' . $this->quoteRequest->id))
            ->line('Thank you for using our platform!');

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'quote_request_closed',
            'quote_request_id' => $this->quoteRequest->id,
            'request_number' => $this->quoteRequest->request_number,
            'hoarding_id' => $this->quoteRequest->hoarding_id,
            'hoarding_title' => $this->quoteRequest->hoarding->title,
            'selected_quote_id' => $this->quoteRequest->selected_quote_id,
            'quotes_received_count' => $this->quoteRequest->quotes_received_count,
            'message' => 'Your quote request has been closed',
        ];
    }
}
