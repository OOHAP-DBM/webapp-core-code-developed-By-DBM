<?php

namespace Modules\Mail;

use App\Models\Hoarding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HoardingPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hoarding;

    public function __construct(Hoarding $hoarding)
    {
        $this->hoarding = $hoarding;
    }

    public function build()
    {
        return $this->subject('Your Hoarding Has Been Approved & Published on OOHAPP 🎉')
                    ->view('emails.hoarding-published')
                    ->with([
                        'hoarding' => $this->hoarding,
                        'hoarding_id' => $this->hoarding->id,
                        'hoarding_commission' => $this->hoarding->commission_percent,
                        'vendor_name' => $this->hoarding->vendor?->name ?? 'Vendor',
                    ]);
    }
}

