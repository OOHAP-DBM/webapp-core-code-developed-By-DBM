<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Hoarding;
use App\Models\User;

class VendorHoardingRatedNotification extends Notification
{
    use Queueable;

    protected $hoarding;
    protected $rating;
    protected $review;
    protected $customer;

    public function __construct(Hoarding $hoarding, $rating, $review, User $customer)
    {
        $this->hoarding = $hoarding;
        $this->rating = $rating;
        $this->review = $review;
        $this->customer = $customer;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $actionUrl = route('hoardings.show', $this->hoarding->slug ?? $this->hoarding->id);

        return (new MailMessage)
            ->subject('Your Hoarding Has Been Rated')
            ->view('emails.hoarding-rated-vendor', [
                'notifiable' => $notifiable,
                'hoarding'   => $this->hoarding,
                'rating'     => $this->rating,
                'review'     => $this->review,
                'customer'   => $this->customer,
                'actionUrl'  => $actionUrl,
            ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'hoarding_id' => $this->hoarding->id,
            'hoarding_title' => $this->hoarding->title ?? '',
            'rating' => $this->rating,
            'review' => $this->review,
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name ?? '',
            'message' => 'Your hoarding has received a new rating.',
            'action_url' => route('hoardings.show', $this->hoarding->slug ?? $this->hoarding->id),
        ];
    }
}
