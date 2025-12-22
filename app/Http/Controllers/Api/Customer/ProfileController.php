<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
// Customer Profile Section @Aviral
class ProfileController extends Controller
{
    /**
     * Get logged-in customer profile
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => (bool) $user->email_verified_at,
                'phone' => $user->phone,
                'phone_verified' => (bool) $user->phone_verified_at,
                'avatar' => $user->avatar
                    ? asset('storage/' . $user->avatar)
                    : null,
                'company_name' => $user->company_name,
                'gstin' => $user->gstin,
            ],
        ]);
    }
    /**
     * Update profile
     */
    public function update(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->email || !$user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email address before updating profile'
                ], 403);
            }

            if (!$user->phone || !$user->phone_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your phone number before updating profile'
                ], 403);
            }
            $data = $request->validate([
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|unique:users,email,' . $user->id,
                'phone'        => 'required|string|unique:users,phone,' . $user->id,
                'company_name' => 'nullable|string|max:255',
                'gstin'        => 'nullable|string|max:255',
                'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);
            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store(
                    'media/users/avatars/' . $user->id,
                    'public'
                );
                if (!Storage::disk('public')->exists($path)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Avatar upload failed'
                    ], 422);
                }
                $data['avatar'] = $path;
            }

            $user->update($data);
            $user->refresh();

            return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => (bool) $user->email_verified_at,
                'phone' => $user->phone,
                'phone_verified' => (bool) $user->phone_verified_at,
                'avatar' => $user->avatar
                    ? asset('storage/' . $user->avatar)
                    : null,
                'company_name' => $user->company_name,
                'gstin' => $user->gstin,
            ],
        ]);

        } catch (Throwable $e) {

            Log::error('PROFILE_UPDATE_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Profile update failed'
            ], 500);
        }
    }
    /**
     * Remove avatar
     */
    public function removeAvatar(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->update(['avatar' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar removed successfully',
            ]);

        } catch (Throwable $e) {
            Log::error('AVATAR_REMOVE_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove avatar',
            ], 500);
        }
    }
    /**
     * Change password + send mail
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password'     => 'required|min:8|confirmed',
            ]);

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            // Static security mail (no extra files)
            if ($user->email) {
                Mail::raw("Hello {$user->name},\n\nYour account password has been changed successfully.\n\nIf you did not perform this action, please contact OOHAPP support immediately.\n\nRegards,\nOOHAPP Security Team",
                    fn ($msg) => $msg->to($user->email)
                                      ->subject('Your OOHAPP Password Was Changed')
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        } catch (Throwable $e) {
            Log::error('PASSWORD_CHANGE_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to change password',
            ], 500);
        }
    }
    /**
     * Send OTP
     */
    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required',
            ]);
            $user = $request->user();
            $otp = $user->generateOTP();
            if (filter_var($request->identifier, FILTER_VALIDATE_EMAIL)) {
                Mail::raw(
                    "Your OOHAPP verification OTP is: {$otp}. This OTP is valid for 10 minutes.",
                    function ($message) use ($request) {
                        $message->to($request->identifier)
                                ->subject('OOHAPP OTP Verification');
                    }
                );
            } else {
                // 📱 SEND SMS (placeholder)
                // yaha SMS gateway integrate hoga
                Log::info('OTP_SMS_SENT', [
                    'phone' => $request->identifier,
                    'otp'   => $otp,
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
            ]);
        } catch (Throwable $e) {
            Log::error('SEND_OTP_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP',
            ], 500);
        }
    }
    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required',
                'otp'        => 'required|digits:4',
            ]);

            $user = $request->user();

            if (!$user->isOTPValid($request->otp)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                ], 422);
            }

            $user->clearOTP();

            filter_var($request->identifier, FILTER_VALIDATE_EMAIL)
                ? $user->update([
                    'email' => $request->identifier,
                    'email_verified_at' => now(),
                ])
                : $user->update([
                    'phone' => $request->identifier,
                    'phone_verified_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Verified successfully',
            ]);

        } catch (Throwable $e) {
            Log::error('VERIFY_OTP_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed',
            ], 500);
        }
    }
}
