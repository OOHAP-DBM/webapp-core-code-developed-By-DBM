<?php

namespace App\Notifications;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteRequestPublishedNotification extends Notification implements ShouldQueue
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
            ->subject('New Quote Request - ' . $this->quoteRequest->request_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new quote request from ' . $this->quoteRequest->customer->name)
            ->line('**Request Details:**')
            ->line('Request Number: ' . $this->quoteRequest->request_number)
            ->line('Hoarding: ' . $this->quoteRequest->hoarding->title)
            ->line('Location: ' . $this->quoteRequest->hoarding->location)
            ->line('Duration: ' . $this->quoteRequest->duration_days . ' ' . $this->quoteRequest->duration_type)
            ->line('Start Date: ' . $this->quoteRequest->preferred_start_date->format('d M Y'))
            ->line('End Date: ' . $this->quoteRequest->preferred_end_date->format('d M Y'));

        if ($this->quoteRequest->budget_min || $this->quoteRequest->budget_max) {
            $budgetRange = 'Budget: ';
            if ($this->quoteRequest->budget_min) {
                $budgetRange .= '₹' . number_format($this->quoteRequest->budget_min, 2);
            }
            if ($this->quoteRequest->budget_max) {
                $budgetRange .= ' - ₹' . number_format($this->quoteRequest->budget_max, 2);
            }
            $mail->line($budgetRange);
        }

        $mail->line('Response Deadline: ' . $this->quoteRequest->response_deadline->format('d M Y H:i'))
            ->action('Submit Quote', url('/quote-requests/' . $this->quoteRequest->id))
            ->line('Please review the requirements and submit your quote before the deadline.');

        if ($this->quoteRequest->requirements) {
            $mail->line('')
                ->line('**Requirements:**')
                ->line($this->quoteRequest->requirements);
        }

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'quote_request_published',
            'quote_request_id' => $this->quoteRequest->id,
            'request_number' => $this->quoteRequest->request_number,
            'customer_id' => $this->quoteRequest->customer_id,
            'customer_name' => $this->quoteRequest->customer->name,
            'hoarding_id' => $this->quoteRequest->hoarding_id,
            'hoarding_title' => $this->quoteRequest->hoarding->title,
            'duration_days' => $this->quoteRequest->duration_days,
            'response_deadline' => $this->quoteRequest->response_deadline,
            'message' => 'New quote request from ' . $this->quoteRequest->customer->name,
        ];
    }
}
