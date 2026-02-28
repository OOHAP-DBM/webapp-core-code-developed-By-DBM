<?php
// app/Events/PosBookingCreated.php
namespace Modules\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\POS\Models\POSBooking;
use App\Models\User;

class PosCustomerCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $customer,
        public User $vendor
    ) {}
}
