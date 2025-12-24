<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OTPService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    // Modules/Auth/Services/OTPService.php

    public function generateAndSendRegisterOTP(string $identifier): array
    {
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        // 1. Check if user exists and IS ALREADY REGISTERED (has a name/password)
        if ($user && $user->name !== null) {
            return [
                'success' => false,
                'message' => 'This ' . ($this->isPhoneNumber($identifier) ? 'phone' : 'email') . ' is already registered. Please login.',
            ];
        }

        // 2. If user doesn't exist at all, create the "Skeleton" record
        if (!$user) {
            $data = $this->isPhoneNumber($identifier)
                ? ['phone' => $identifier]
                : ['email' => $identifier];

            $data['status'] = 'pending_verification';
            $user = $this->userRepository->create($data);
        }

        // 3. Generate 4-digit OTP (This uses the method in your User model)
        $otp = $user->generateOTP();

        // 4. Send the OTP
        $this->sendOTP($user, $otp);

        return [
            'success' => true,
            'message' => 'OTP sent successfully',
            'is_new_user' => true
        ];
    }

    // Modules/Auth/Services/OTPService.php

    public function verifyRegisterOTP(string $identifier, string $otp): array
    {
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        // 1. Check if user exists
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // 2. Validate OTP using the method in your User Model
        if (!$user->isOTPValid($otp)) {
            return ['success' => false, 'message' => 'Invalid or expired OTP'];
        }

        // 3. Mark as verified in the Database
        if ($this->isPhoneNumber($identifier)) {
            $user->phone_verified_at = now();
        } else {
            $user->email_verified_at = now();
        }

        // Clear the OTP so it can't be used again
        $user->clearOTP();
        $user->save();

        return [
            'success' => true,
            'message' => 'Identity verified. You can now complete your registration.',
            'identifier' => $identifier
        ];
    }
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
