<?php
namespace Modules\POS\Notifications\Vendor;
use Illuminate\Bus\Queueable;   
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;

class VendorPosBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $booking) {}

    public function via($notifiable) { return ['database']; }

    public function toArray($notifiable)
    {
        return [
            'message' => "New POS booking #{$this->booking->id} created",
            'amount'  => $this->booking->total_amount,
        ];
    }
}
