<?php


namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\TwilioService;
use App\Models\User;
use App\Models\UserOtp;
use App\Mail\OtpMail;

class ProfileService
{
    public function updateAvatar($user, $file): string
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        return $file->store("media/users/avatars/{$user->id}", 'public');
    }

    public function changePassword($user, string $currentPassword, string $newPassword): array
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect',
            ];
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all active tokens (important security step)
        $user->tokens()->delete();

        // Security notification mail
        if ($user->email) {
            Mail::raw(
                "Hello {$user->name},\n\nYour OOHAPP account password was changed successfully.\n\n"
                    . "If you did not perform this action, please contact support immediately.\n\n"
                    . "Regards,\nOOHAPP Security Team",
                fn($msg) => $msg->to($user->email)->subject('Password Changed Successfully')
            );
        }

        return [
            'success' => true,
            'message' => 'Password changed successfully',
        ];
    }

    public function response($user, array $extra = []): array
    {
        return array_merge([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => (bool) $user->email_verified_at,
            'phone' => $user->phone,
            'phone_verified' => (bool) $user->phone_verified_at,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
        ], $extra);
    }

    const OTP_EXPIRY_MINUTES = 0.5; // 30 seconds for testing
    const OTP_LENGTH = 4;

    /* ==============================
     | SEND OTP
     ============================== */
    public function send(User $user, string $type, string $value): void
    {
        $purpose = "change_{$type}";

        // Rate limit (1 OTP / minute)
        $recent = UserOtp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('created_at', '>', now()->subMinute())
            ->exists();

        if ($recent) {
            abort(429, 'Please wait before requesting another OTP');
        }

        // Invalidate old OTPs
        UserOtp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->delete();

        $otp = random_int(1000, 9999);

        UserOtp::create([
            'user_id' => $user->id,
            'identifier' => $value,
            'purpose' => $purpose,
            'otp_hash' => (string)$otp,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        // Development: Log OTP (remove in production)
        if (config('app.env') !== 'production') {
            \Log::info('OTP Generated', [
                'user_id' => $user->id,
                'identifier' => $value,
                'otp' => $otp,
                'purpose' => $purpose,
            ]);
        }

        $this->dispatchOtp($value, $otp);
    }

    /* ==============================
     | VERIFY OTP
     ============================== */
    public function verify(User $user, string $type, string $value, string $otp): void
    {
        $record = UserOtp::where([
            'user_id' => $user->id,
            'identifier' => $value,
            'purpose' => "change_{$type}",
        ])->first();

        if (!$record) {
            \Log::warning('OTP verification failed: No record found', [
                'user_id' => $user->id,
                'identifier' => $value,
                'purpose' => "change_{$type}",
            ]);
            abort(422, 'Invalid or expired OTP');
        }

        if ($record->verified_at) {
            \Log::warning('OTP verification failed: Already verified', [
                'user_id' => $user->id,
                'verified_at' => $record->verified_at,
            ]);
            abort(422, 'OTP already used');
        }

        if ($record->isExpired()) {
            \Log::warning('OTP verification failed: Expired', [
                'user_id' => $user->id,
                'expires_at' => $record->expires_at,
            ]);
            abort(422, 'OTP has expired');
        }

        if (!$record->matches($otp)) {
            \Log::warning('OTP verification failed: Invalid OTP', [
                'user_id' => $user->id,
                'provided_otp' => $otp,
                'provided_otp_type' => gettype($otp),
                'stored_hash' => $record->otp_hash,
            ]);
            abort(422, 'Invalid OTP code');
        }

        $record->update(['verified_at' => now()]);

        // Apply change
        if ($type === 'email') {
            $user->update([
                'email' => $value,
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'phone' => $value,
                'phone_verified_at' => now(),
            ]);
        }

        // OTP is single-use
        $record->delete();
    }

    /* ==============================
     | DELIVERY
     ============================== */
    protected function dispatchOtp(string $identifier, int $otp): void
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            Mail::to($identifier)->send(new \Modules\Mail\OtpVerificationMail($otp));
        } else {
            // Use TwilioService for SMS
            $message = "Your OOHAPP verification code is: {$otp}. Valid for 5 minutes.";
            try {
                app(TwilioService::class)->sendSMS($identifier, $message);
            } catch (\Exception $e) {
                \Log::error('Failed to send OTP SMS: ' . $e->getMessage(), [
                    'phone' => $identifier,
                    'otp' => $otp,
                ]);
            }
        }
    }
}
