<?php

namespace Modules\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Enquiries\Models\Enquiry;

class CustomerEnquiryConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $enquiry;
    public $customer;

    public function __construct(Enquiry $enquiry, User $customer)
    {
        $this->enquiry = $enquiry;
        $this->customer = $customer;
    }

    public function build()
    {
        return $this->subject('Enquiry Confirmation - Your Campaign Details')
            ->view('emails.customer-enquiry-confirmation');
    }
}
