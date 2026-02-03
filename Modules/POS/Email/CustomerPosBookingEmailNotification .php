<?php

namespace Modules\POS\Email;        
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;  
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;  

class CustomerPosBookingEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $booking) {}

    public function via($notifiable) { return ['mail']; }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('POS Booking Confirmed')
            ->greeting("Hello {$notifiable->name}")
            ->line("Your booking #{$this->booking->id} is confirmed.")
            ->line("Amount: ₹{$this->booking->total_amount}")
            ->line("Thank you for choosing us.");
    }
}
