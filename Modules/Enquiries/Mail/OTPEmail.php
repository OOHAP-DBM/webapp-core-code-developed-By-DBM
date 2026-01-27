<?php
namespace Modules\Enquiries\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPEmail extends Mailable
{
    use Queueable, SerializesModels;

    public int $otp;

    public function __construct(int $otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Your OTP for OOHAPP')
            ->view('enquiries.emails.otp')
            ->with(['otp' => $this->otp]);
    }
}
