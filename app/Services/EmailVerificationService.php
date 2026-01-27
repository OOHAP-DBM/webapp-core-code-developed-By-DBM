<?php

namespace App\Services;

use App\Models\User;
use App\Models\VendorEmail;
use App\Models\UserOtp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    const OTP_EXPIRY_MINUTES = 15;
    const OTP_LENGTH = 6;
    const RESEND_DELAY_SECONDS = 60;

    /**
     * Add a new email to vendor's profile
     */
    public function addEmail(User $vendor, string $email, bool $makePrimary = false): VendorEmail
    {
        $vendorEmail = VendorEmail::where('user_id', $vendor->id)
            ->where('email', $email)
            ->first();

        if ($vendorEmail) {
            return $vendorEmail;
        }

        $vendorEmail = VendorEmail::create([
            'user_id' => $vendor->id,
            'email' => $email,
            'is_primary' => $makePrimary,
        ]);

        return $vendorEmail;
    }

    /**
     * Send OTP to email using user_otp table
     */
    public function sendOTP(User $vendor, string $email, string $purpose = 'vendor_email_verification'): bool
    {
        // Check if can resend
        if (!$this->canResendOTP($email, $purpose)) {
            return false;
        }

        try {
            // Delete previous OTP for same email
            UserOtp::where([
                'identifier' => $email,
                'purpose' => $purpose,
            ])->delete();

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store OTP in user_otp table
            UserOtp::create([
                'user_id' => $vendor->id,
                'identifier' => $email,
                'otp_hash' => $otp,
                'purpose' => $purpose,
                'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            ]);

            // Send OTP via email
            Mail::send('emails.otp-verification', [
                'vendor' => $vendor,
                'email' => $email,
                'otp' => $otp,
                'expiresIn' => self::OTP_EXPIRY_MINUTES,
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Email Verification - OOH App');
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email OTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify OTP and mark email as verified
     */
    public function verifyOTP(User $vendor, string $email, string $otp, string $purpose = 'vendor_email_verification'): bool
    {
        try {
            // Find the OTP record
            $otpRecord = UserOtp::where([
                'user_id' => $vendor->id,
                'identifier' => $email,
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

            // Mark OTP as verified
            $otpRecord->update(['verified_at' => now()]);

            // Update or create vendor email record
            $vendorEmail = VendorEmail::where([
                'user_id' => $vendor->id,
                'email' => $email,
            ])->first();

            if ($vendorEmail) {
                $vendorEmail->update([
                    'verified_at' => now(),
                    'is_primary' => !VendorEmail::where('user_id', $vendor->id)->where('verified_at', '!=', null)->exists(),
                ]);
            } else {
                VendorEmail::create([
                    'user_id' => $vendor->id,
                    'email' => $email,
                    'verified_at' => now(),
                    'is_primary' => !VendorEmail::where('user_id', $vendor->id)->where('verified_at', '!=', null)->exists(),
                ]);
            }

            // If this is the vendor's primary signup email, also update user table
            if ($email === $vendor->email) {
                $vendor->update(['email_verified_at' => now()]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Email OTP verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified(User $vendor, string $email): bool
    {
        // Check if it's the primary email
        if ($email === $vendor->email) {
            return $vendor->email_verified_at !== null;
        }

        // Check vendor emails
        return VendorEmail::where([
            'user_id' => $vendor->id,
            'email' => $email,
        ])->where('verified_at', '!=', null)->exists();
    }

    /**
     * Can resend OTP (rate limiting)
     */
    public function canResendOTP(string $email, string $purpose = 'vendor_email_verification'): bool
    {
        $recentOtp = UserOtp::where([
            'identifier' => $email,
            'purpose' => $purpose,
        ])
            ->latest('created_at')
            ->first();

        if (!$recentOtp) {
            return true;
        }

        return $recentOtp->created_at->addSeconds(self::RESEND_DELAY_SECONDS)->isPast();
    }

    /**
     * Resend OTP
     */
    public function resendOTP(User $vendor, string $email, string $purpose = 'vendor_email_verification'): array
    {
        if (!$this->canResendOTP($email, $purpose)) {
            $recentOtp = UserOtp::where([
                'identifier' => $email,
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

        if ($this->sendOTP($vendor, $email, $purpose)) {
            return [
                'success' => true,
                'message' => 'OTP sent successfully to ' . $email,
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to send OTP. Please try again.',
        ];
    }

    /**
     * Get all verified emails for vendor
     */
    public function getVerifiedEmails(User $vendor): array
    {
        $verified = [];

        // Add primary email if verified
        if ($vendor->email && $vendor->email_verified_at) {
            $verified[] = [
                'email' => $vendor->email,
                'type' => 'primary',
                'verified_at' => $vendor->email_verified_at,
                'is_primary' => true,
            ];
        }

        // Add secondary verified emails
        $secondaryEmails = VendorEmail::where([
            'user_id' => $vendor->id,
        ])->whereNotNull('verified_at')->get();

        foreach ($secondaryEmails as $vendorEmail) {
            $verified[] = [
                'email' => $vendorEmail->email,
                'type' => 'secondary',
                'verified_at' => $vendorEmail->verified_at,
                'is_primary' => $vendorEmail->is_primary,
            ];
        }

        return $verified;
    }

    /**
     * Get primary verified email
     */
    public function getPrimaryVerifiedEmail(User $vendor): ?string
    {
        // Check if primary email is verified
        if ($vendor->email && $vendor->email_verified_at) {
            return $vendor->email;
        }

        // Get primary from vendor emails
        $primary = VendorEmail::where([
            'user_id' => $vendor->id,
            'is_primary' => true,
        ])->whereNotNull('verified_at')->first();

        return $primary?->email;
    }

    /**
     * Check if vendor has at least one verified email
     */
    public function hasVerifiedEmail(User $vendor): bool
    {
        if ($vendor->email_verified_at) {
            return true;
        }

        return VendorEmail::where('user_id', $vendor->id)
            ->whereNotNull('verified_at')
            ->exists();
    }

    /**
     * Get pending emails for verification
     */
    public function getPendingEmails(User $vendor): array
    {
        $pending = [];

        // Check primary email
        if ($vendor->email && !$vendor->email_verified_at) {
            $pending[] = [
                'email' => $vendor->email,
                'type' => 'primary',
                'added_at' => $vendor->created_at,
            ];
        }

        // Check secondary emails
        $secondaryEmails = VendorEmail::where([
            'user_id' => $vendor->id,
        ])->whereNull('verified_at')->get();

        foreach ($secondaryEmails as $vendorEmail) {
            $pending[] = [
                'email' => $vendorEmail->email,
                'type' => 'secondary',
                'added_at' => $vendorEmail->created_at,
            ];
        }

        return $pending;
    }

    /**
     * Remove email from vendor profile
     */
    public function removeEmail(User $vendor, string $email): bool
    {
        // Don't allow removing the only verified email
        $verifiedCount = $this->countVerifiedEmails($vendor);

        if ($verifiedCount <= 1 && $this->isEmailVerified($vendor, $email)) {
            return false;
        }

        VendorEmail::where([
            'user_id' => $vendor->id,
            'email' => $email,
        ])->delete();

        return true;
    }

    /**
     * Count verified emails
     */
    public function countVerifiedEmails(User $vendor): int
    {
        $count = 0;

        if ($vendor->email_verified_at) {
            $count++;
        }

        $count += VendorEmail::where('user_id', $vendor->id)
            ->whereNotNull('verified_at')
            ->count();

        return $count;
    }
}
