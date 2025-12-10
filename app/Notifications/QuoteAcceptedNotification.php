<?php

namespace App\Notifications;

use App\Models\VendorQuote;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $quote;
    protected $booking;

    public function __construct(VendorQuote $quote, Booking $booking)
    {
        $this->quote = $quote;
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Quote Accepted - ' . $this->quote->quote_number)
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Your quote has been accepted by ' . $this->quote->customer->name)
            ->line('**Quote Details:**')
            ->line('Quote Number: ' . $this->quote->quote_number)
            ->line('Hoarding: ' . $this->quote->hoarding->title)
            ->line('Duration: ' . $this->quote->duration_days . ' ' . $this->quote->duration_type)
            ->line('Amount: ₹' . number_format($this->quote->grand_total, 2))
            ->line('')
            ->line('**Booking Created:**')
            ->line('Booking Number: ' . $this->booking->booking_number)
            ->line('Start Date: ' . $this->booking->start_date->format('d M Y'))
            ->line('End Date: ' . $this->booking->end_date->format('d M Y'))
            ->action('View Booking', url('/bookings/' . $this->booking->id))
            ->line('Please proceed with the next steps to complete the booking.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'quote_accepted',
            'quote_id' => $this->quote->id,
            'quote_number' => $this->quote->quote_number,
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'customer_id' => $this->quote->customer_id,
            'customer_name' => $this->quote->customer->name,
            'hoarding_id' => $this->quote->hoarding_id,
            'hoarding_title' => $this->quote->hoarding->title,
            'amount' => $this->quote->grand_total,
            'message' => 'Your quote has been accepted by ' . $this->quote->customer->name,
        ];
    }
}
