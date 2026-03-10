<?php
// Modules/Hoardings/Mail/VendorHoardingBulkStatusMail.php

namespace Modules\Hoardings\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VendorHoardingBulkStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vendor;
    public $hoardings;   // Collection of hoardings
    public $action;
    public $adminName;

    public function __construct($vendor, Collection $hoardings, string $action, string $adminName = 'Admin')
    {
        $this->vendor    = $vendor;
        $this->hoardings = $hoardings;
        $this->action    = $action;
        $this->adminName = $adminName;
    }

    public function build()
    {
        $count   = $this->hoardings->count();
        $subject = $count . ' ' . Str::plural('Hoarding', $count) . ' ' . ucfirst($this->action);

        return $this->to($this->vendor->email)
            ->subject($subject)
            ->view('emails.vendor_hoarding_bulk_status')
            ->with([
                'greeting'   => 'Hello ' . ($this->vendor->name ?? ''),
                'hoardings'  => $this->hoardings,
                'action'     => $this->action,
                'adminName'  => $this->adminName,
                'count'      => $count,
            ]);
    }
}