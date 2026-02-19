<?php

namespace Modules\Hoardings\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class VendorHoardingStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vendor;
    public $hoarding;
    public $action;
    public $adminName;
    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($vendor, $hoarding, $action, $adminName = null, $customMessage = null)
    {
        $this->vendor = $vendor;
        $this->hoarding = $hoarding;
        $this->action = $action;
        $this->adminName = $adminName;
        $this->customMessage = $customMessage;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Hoarding ' . ucfirst($this->action);
        $greeting = 'Hello ' . ($this->vendor->name ?? '');
        $hoardingTitle = $this->hoarding->title ?? $this->hoarding->name ?? 'Hoarding';
        $adminName = $this->adminName ?? 'Admin';
        $mailMessage = $this->customMessage ?? ("Your hoarding '" . $hoardingTitle . "' has been " . $this->action . " by " . $adminName . ".");
        $url = route('vendor.myHoardings.show', $this->hoarding->id);

        return $this->to($this->vendor->email)
            ->subject($subject)
            ->view('emails.vendor_hoarding_status')
            ->with([
                'greeting' => $greeting,
                'mailMessage' => $mailMessage,
                'actionUrl' => $url,
                'hoardingTitle' => $hoardingTitle,
                'action' => $this->action,
                'adminName' => $adminName,
            ]);
    }
}
