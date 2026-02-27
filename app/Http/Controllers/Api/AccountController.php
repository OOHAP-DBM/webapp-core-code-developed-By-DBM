<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\OTPService;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    // Send OTP for account deletion (email or phone)
    public function sendDeleteOtp(Request $request, OTPService $otpService)
    {
        $user = Auth::user();
        $type = $request->input('type'); // 'email' or 'phone'
        $value = $type === 'email' ? $user->email : $user->phone;

        if (!$type || !$value) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 422);
        }

        try {
            $otpService->generate($user->id, $value, 'delete_account_' . $type);
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Delete OTP send failed', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP'
            ], 500);
        }
    }

    // Verify OTP for account deletion
    public function verifyDeleteOtp(Request $request, OTPService $otpService)
    {
        $user = Auth::user();
        $type = $request->input('type'); // 'email' or 'phone'
        $value = $type === 'email' ? $user->email : $user->phone;
        $otp = $request->input('otp');

        if (!$type || !$value || !$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 422);
        }

        try {
            $verified = $otpService->verify($user->id, $value, $otp, 'delete_account_' . $type);
            if (!$verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect or expired OTP'
                ], 422);
            }
            // Mark as verified in session for deletion
            session(['delete_account_verified_' . $type => true]);
            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' OTP verified successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Delete OTP verify failed', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Verification failed. Try again.'
            ], 500);
        }
    }

    // Final delete account (requires OTP verification)
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        // Check OTP verification from user_otps table
        $emailOtpVerified = \DB::table('user_otps')
            ->where('user_id', $user->id)
            ->where('purpose', 'delete_account_email')
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists();

        $phoneOtpVerified = \DB::table('user_otps')
            ->where('user_id', $user->id)
            ->where('purpose', 'delete_account_phone')
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists();

        if (!$emailOtpVerified && !$phoneOtpVerified) {
            return response()->json([
                'success' => false,
                'message' => 'OTP verification required before deleting account.'
            ], 422);
        }

        // Optionally save reason if provided
        if ($request->filled('reason')) {
            $user->delete_reason = $request->input('reason');
            $user->save();
        }

        // Soft delete user and vendor profile if exists
        $user->delete();
        if (method_exists($user, 'vendorProfile') && $user->vendorProfile) {
            $user->vendorProfile->delete();
        }

        Auth::logout();

        return response()->json([
            'success' => true,
            'title' => 'We’ll miss you 💔',
            'message' => 'Your account has been deleted successfully.'
        ]);
    }
}
