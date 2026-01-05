<?php

namespace Modules\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $commissionPercentage;

    public function __construct(User $user, $commissionPercentage)
    {
        $this->user = $user;
        $this->commissionPercentage = $commissionPercentage;
    }

    public function build()
    {
        return $this->subject('Your Vendor Profile Has Been Approved! 🎉')
                    ->view('emails.vendor-approved');
    }
}
