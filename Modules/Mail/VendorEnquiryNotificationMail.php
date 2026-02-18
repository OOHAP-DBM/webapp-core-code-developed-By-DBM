<?php

namespace Modules\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Enquiries\Models\Enquiry;
use Illuminate\Support\Collection;

class VendorEnquiryNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $enquiry;
    public $vendor;
    public $items;

    public function __construct(Enquiry $enquiry, User $vendor, Collection $items)
    {
        $this->enquiry = $enquiry;
        $this->vendor = $vendor;
        $this->items = $items;
    }

    public function build()
    {
        return $this->subject('New Enquiry on Your Hoarding - ' . $this->vendor->name)
                    ->view('emails.vendor-enquiry-notification');
    }
}