<?php

namespace App\Notifications;

use App\Models\VendorEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationOTPNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $vendorEmail;
    protected $otp;

    public function __construct(VendorEmail $vendorEmail, string $otp)
    {
        $this->vendorEmail = $vendorEmail;
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address - OOH App')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Please verify your email address using the OTP below.')
            ->line('**OTP: ' . $this->otp . '**')
            ->line('This OTP will expire in 10 minutes.')
            ->action('Verify Email', route('vendor.emails.verify', $this->vendorEmail->id))
            ->line('If you did not request this OTP, please ignore this email.')
            ->line('Thank you for using OOH App!');
    }
}
