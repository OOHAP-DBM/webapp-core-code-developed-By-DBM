<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserOtp;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Log;

class MobileOTPService
{
    const OTP_EXPIRY_MINUTES = 10;
    const OTP_LENGTH = 6;
    const MAX_ATTEMPTS = 5;
    const RESEND_DELAY_SECONDS = 60;

    protected TwilioClient $twilio;

    public function __construct()
    {
        $this->twilio = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Generate and send OTP to mobile
     */
    public function sendOTP(User $vendor, string $purpose = 'mobile_verification'): bool
    {
        // Check if can resend (rate limiting)
        if (!$this->canResendOTP($vendor->phone, $purpose)) {
            return false;
        }

        try {
            // Delete previous OTP for same purpose
            UserOtp::where([
                'user_id' => $vendor->id,
                'identifier' => $vendor->phone,
                'purpose' => $purpose,
            ])->delete();

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store OTP in user_otp table
            UserOtp::create([
                'user_id' => $vendor->id,
                'identifier' => $vendor->phone,
                'otp_hash' => $otp,
                'purpose' => $purpose,
                'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            ]);

            // Send OTP via Twilio SMS
            $this->sendViaTwilio($vendor->phone, $otp);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send mobile OTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP via Twilio
     */
    protected function sendViaTwilio(string $phoneNumber, string $otp): void
    {
        try {
            $this->twilio->messages->create(
                $this->formatPhoneNumber($phoneNumber),
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Your OOH App verification code is: {$otp}. Valid for 10 minutes.",
                ]
            );
        } catch (\Exception $e) {
            Log::error('Twilio SMS sending failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Format phone number to E.164 format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove non-numeric characters
        $cleaned = preg_replace('/\D/', '', $phone);

        // Add +91 prefix if not present (for India)
        if (strlen($cleaned) === 10) {
            return '+91' . $cleaned;
        }

        if (strpos($cleaned, '91') === 0 && strlen($cleaned) === 12) {
            return '+' . $cleaned;
        }

        return '+' . $cleaned;
    }

    /**
     * Verify OTP and mark mobile as verified
     */
    public function verifyOTP(User $vendor, string $otp, string $purpose = 'mobile_verification'): bool
    {
        try {
            // Find the OTP record
            $otpRecord = UserOtp::where([
                'user_id' => $vendor->id,
                'identifier' => $vendor->phone,
                'purpose' => $purpose,
            ])->first();

            if (!$otpRecord) {
                return false;
            }

            // Check if already verified
            if ($otpRecord->verified_at) {
                return false;
            }

            // Check if expired
            if ($otpRecord->expires_at->isPast()) {
                return false;
            }

            // Check if OTP matches
            if ($otpRecord->otp_hash !== $otp) {
                return false;
            }

            // Mark as verified
            $otpRecord->update(['verified_at' => now()]);

            // Update user's phone_verified_at
            $vendor->update(['phone_verified_at' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error('OTP verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if mobile is verified
     */
    public function isMobileVerified(User $vendor): bool
    {
        return $vendor->phone_verified_at !== null;
    }

    /**
     * Can resend OTP (rate limiting)
     */
    public function canResendOTP(string $phoneNumber, string $purpose = 'mobile_verification'): bool
    {
        $recentOtp = UserOtp::where([
            'identifier' => $phoneNumber,
            'purpose' => $purpose,
        ])
            ->latest('created_at')
            ->first();

        if (!$recentOtp) {
            return true;
        }

        // Allow resend after specified delay
        return $recentOtp->created_at->addSeconds(self::RESEND_DELAY_SECONDS)->isPast();
    }

    /**
     * Resend OTP with validation
     */
    public function resendOTP(User $vendor, string $purpose = 'mobile_verification'): array
    {
        if (!$this->canResendOTP($vendor->phone, $purpose)) {
            $recentOtp = UserOtp::where([
                'identifier' => $vendor->phone,
                'purpose' => $purpose,
            ])
                ->latest('created_at')
                ->first();

            $nextResendTime = $recentOtp->created_at->addSeconds(self::RESEND_DELAY_SECONDS);

            return [
                'success' => false,
                'message' => 'Please wait before requesting another OTP',
                'retry_after' => $nextResendTime->diffInSeconds(now()),
            ];
        }

        if ($this->sendOTP($vendor, $purpose)) {
            return [
                'success' => true,
                'message' => 'OTP sent successfully to your registered mobile number',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to send OTP. Please try again.',
        ];
    }

    /**
     * Clear OTP for user
     */
    public function clearOTP(User $vendor, string $purpose = 'mobile_verification'): void
    {
        UserOtp::where([
            'user_id' => $vendor->id,
            'purpose' => $purpose,
        ])->delete();
    }

    /**
     * Get OTP record for verification check
     */
    public function getOtpRecord(User $vendor, string $purpose = 'mobile_verification'): ?UserOtp
    {
        return UserOtp::where([
            'user_id' => $vendor->id,
            'identifier' => $vendor->phone,
            'purpose' => $purpose,
        ])->first();
    }
}
