<?php

namespace Modules\Auth\Services;

use Modules\Users\Models\User;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OTPService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Generate and send OTP
     */
    public function generateAndSendOTP(string $identifier): array
    {
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        // Generate OTP
        $otp = $user->generateOTP();

        // Send OTP (implement your SMS/Email provider here)
        $this->sendOTP($user, $otp);

        return [
            'success' => true,
            'message' => 'OTP sent successfully',
            'user_id' => $user->id,
        ];
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(string $identifier, string $otp): array
    {
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        if (!$user->isOTPValid($otp)) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ];
        }

        // Clear OTP
        $user->clearOTP();

        // Verify phone if OTP was sent to phone
        if ($this->isPhoneNumber($identifier) && !$user->phone_verified_at) {
            $this->userRepository->verifyPhone($user->id);
        }

        // Update last login
        $user->updateLastLogin();

        // Activate user if pending verification
        if ($user->status === 'pending_verification') {
            $this->userRepository->updateStatus($user->id, 'active');
        }

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
            'user' => $user->fresh(),
        ];
    }

    /**
     * Send OTP via SMS or Email
     */
    protected function sendOTP(User $user, string $otp): void
    {
        // For development, log OTP
        if (config('app.debug')) {
            Log::info("OTP for user {$user->id}: {$otp}");
        }

        // Implement your SMS/Email provider here
        // Example: Twilio, AWS SNS, or Email notification
        
        // For phone numbers
        if ($user->phone) {
            // TODO: Implement SMS sending
            // Example: Twilio::sendSMS($user->phone, "Your OOHAPP OTP is: {$otp}");
        }

        // Fallback to email
        if ($user->email) {
            // TODO: Implement email sending
            // $user->notify(new OTPNotification($otp));
        }
    }

    /**
     * Check if identifier is a phone number
     */
    protected function isPhoneNumber(string $identifier): bool
    {
        return preg_match('/^[0-9+\-\(\)\s]+$/', $identifier);
    }

    /**
     * Resend OTP
     */
    public function resendOTP(string $identifier): array
    {
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        // Check if OTP was recently sent (rate limiting)
        if ($user->otp_expires_at && $user->otp_expires_at->isFuture() && $user->otp_expires_at->diffInMinutes(now()) > 8) {
            return [
                'success' => false,
                'message' => 'Please wait before requesting a new OTP',
            ];
        }

        return $this->generateAndSendOTP($identifier);
    }
}

