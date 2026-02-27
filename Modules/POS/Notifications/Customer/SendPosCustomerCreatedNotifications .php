<?php
// app/Jobs/SendPosCustomerCreatedNotifications.php     

namespace Modules\POS\Notifications\Customer;
use Modules\POS\Events\PosCustomerCreated;
use App\Models\User;
use App\Notifications\AdminPosCustomerCreatedNotification;
use App\Notifications\VendorPosCustomerCreatedNotification; 
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
class SendPosCustomerCreatedNotifications implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public PosCustomerCreated $event) {}

    public function handle()
    {
        $customer = $this->event->customer;
        $vendor   = $this->event->vendor;

        User::role('admin')->each(fn ($a) =>
            $a->notify(new AdminPosCustomerCreatedNotification($customer, $vendor))
        );

        $vendor->notify(
            new VendorPosCustomerCreatedNotification($customer)
        );

        $customer->notify(
            new CustomerWelcomeNotification($customer)
        );
    }
}
