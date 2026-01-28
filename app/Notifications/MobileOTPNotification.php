<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MobileOTPNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $otp;

    public function __construct(User $user, string $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail']; // You can add SMS via Twilio, AWS SNS, etc.
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verify Your Mobile Number - OOH App')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Please verify your mobile number using the OTP below.')
            ->line('**OTP: ' . $this->otp . '**')
            ->line('This OTP will expire in 10 minutes.')
            ->line('If you did not request this OTP, please ignore this email.')
            ->line('Thank you for using OOH App!');
    }
}
