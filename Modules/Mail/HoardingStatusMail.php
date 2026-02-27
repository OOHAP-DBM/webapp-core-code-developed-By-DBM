<?php

namespace Modules\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Hoarding;

class HoardingStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hoarding;
    public $statusText;

    /**
     * Create a new message instance.
     *
     * @param Hoarding $hoarding
     * @param string $statusText
     */
    public function __construct(Hoarding $hoarding, string $statusText)
    {
        $this->hoarding = $hoarding;
        $this->statusText = $statusText;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Hoarding Status Update')
            ->view('mail.hoarding_status_update')
            ->with([
                'hoarding' => $this->hoarding,
                'statusText' => $this->statusText,
            ]);
    }
}
