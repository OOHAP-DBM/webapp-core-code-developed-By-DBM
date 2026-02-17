<?php

namespace Modules\Mail;

use Illuminate\Mail\Mailable;

class OtpVerificationMail extends Mailable
{
    public $otp;

    /**
     * Create a new message instance.
     *
     * @param string $otp
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('OOHAPP Email Verification')
            ->view('emails.otp_verification')
            ->with([
                'otp' => $this->otp,
            ]);
    }
}
