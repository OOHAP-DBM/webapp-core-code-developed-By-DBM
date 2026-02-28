<?php
// app/Notifications/CustomerPosWelcomeEmailNotification.php
namespace Modules\POS\Notifications\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class CustomerPosWelcomeEmailNotification extends Notification
{
    use Queueable;

    public User $customer;

    public function __construct(User $customer)
    {
        $this->customer = $customer;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to OOH POS')
            ->greeting('Dear ' . $this->customer->name)
            ->line('Your account has been created successfully.')
            ->line('You can now login and start booking hoardings.')
            ->line('For support, contact: ' . config('support.contact'))
            ->salutation('Thank you!');
    }
}