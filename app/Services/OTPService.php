<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient; // Make sure you install twilio/sdk via composer
use Modules\Enquiries\Mail\OTPEmail;

class OTPService
{
    const OTP_EXPIRY_MINUTES = 5;

    protected TwilioClient $twilio;

    public function __construct()
    {
        $this->twilio = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Generate and send OTP
     */
    public function generate(int $userId, string $identifier, string $purpose): int
    {
        // Delete previous OTP for same purpose
        \App\Models\UserOtp::where([
            'user_id' => $userId,
            'purpose' => $purpose
        ])->delete();

        // Generate 4-digit OTP
        $otp = random_int(1000, 9999);
        $otp = 1234; // For testing  

        \App\Models\UserOtp::create([
            'user_id' => $userId,
            'identifier' => $identifier,
            'otp_hash' => $otp,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        // Send OTP
        $this->sendOTP($identifier, $otp);

        return $otp;
    }

    /**
     * Verify OTP
     */
    public function verify(int $userId, string $identifier, string $otp, string $purpose): bool
    {
        $record = \App\Models\UserOtp::where([
            'user_id' => $userId,
            'identifier' => $identifier,
            'purpose' => $purpose
        ])->first();

        if (!$record) return false;
        if ($record->verified_at) return false;
        if ($record->expires_at->isPast()) return false;
        if ($otp !== $record->otp_hash) return false;

        $record->update(['verified_at' => now()]);

        return true;
    }

    /**
     * Send OTP via SMS or Email
     */
    protected function sendOTP(string $identifier, int $otp): void
    {
        if ($this->isPhoneNumber($identifier)) {
            // Send via Twilio SMS
            try {
                $this->twilio->messages->create($this->formatPhoneNumber($identifier), [
                    'from' => config('services.twilio.from'),
                    'body' => "Your OOHAPP OTP is: {$otp}"
                ]);
            } catch (\Exception $e) {
                Log::error("OTP SMS sending failed: " . $e->getMessage());
            }
        } else {
            // Send via Email
            try {
                Mail::to($identifier)->queue(new OTPEmail($otp));
            } catch (\Exception $e) {
                Log::error("OTP Email sending failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Check if identifier is a phone number
     */
    protected function isPhoneNumber(string $identifier): bool
    {
        return preg_match('/^[0-9]{10,15}$/', $identifier);
    }
    protected function formatPhoneNumber(string $number): string
    {
        if (strlen($number) == 10) {
            return '+91'.$number; // Assuming India
        }
        return $number;
    }
}
