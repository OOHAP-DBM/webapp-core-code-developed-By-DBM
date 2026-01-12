<?php


namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
}
