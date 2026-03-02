<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\OTPService;
use App\Models\User;

class EmailSettingController extends Controller
{
    /**
     * Get current email settings for the authenticated vendor
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isVendor()) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $vendorProfile = $user->vendorProfile;

        return response()->json([
            'success' => true,
            'data' => [
                'primary_email'     => $user->email,
                'additional_emails' => $vendorProfile?->additional_emails ?? [],
                'email_preferences' => $vendorProfile?->email_preferences ?? [],
            ]
        ]);
    }

    /**
     * Send OTP to email for verification
     */
    public function sendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));
        $user  = Auth::user();

        // Check if email is already used by another user as primary email
        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered to another account.'
            ], 422);
        }

        // Check if email is already verified in additional emails
        $additionalEmails = $user->vendorProfile?->additional_emails ?? [];
        if (in_array($email, $additionalEmails)) {
            $prefs = $user->vendorProfile?->email_preferences ?? [];
            if (!empty($prefs[$email]['verified'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already verified.'
                ], 422);
            }
        }

        try {
            $otpService = app(OTPService::class);
            $otpService->sendEmailOTP($email, $user);

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to ' . $email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP for email
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));
        $user  = Auth::user();

        try {
            $otpService = app(OTPService::class);

            if ($otpService->verifyEmailOTP($email, $request->input('otp'), $user)) {
                $profile = $user->vendorProfile;

                if (!$profile) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vendor profile not found.'
                    ], 404);
                }

                $prefs         = $profile->email_preferences ?? [];
                $prefs[$email] = [
                    'verified'      => true,
                    'verified_at'   => now()->toDateTimeString(),
                    'notifications' => false, // Default to disabled
                ];

                $profile->email_preferences = $prefs;
                $profile->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Email verified successfully.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Update email settings (add/remove additional emails, update primary email)
     * All additional emails must be verified via OTP before saving.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'primary_email'          => 'required|email|max:255',
            'additional_emails'      => 'nullable|array',
            'additional_emails.*'    => 'required|email|max:255|distinct',
            'email_notifications'    => 'nullable|array',
            'notification_email'     => 'nullable|boolean',
            'notification_push'      => 'nullable|boolean',
            'notification_whatsapp'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $user    = Auth::user();
        $profile = $user->vendorProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found.'
            ], 404);
        }

        $prefs            = $profile->email_preferences ?? [];
        $additionalEmails = $request->additional_emails ?? [];

        // Normalize emails to lowercase
        $additionalEmails = array_values(array_unique(
            array_map('strtolower', array_map('trim', $additionalEmails))
        ));

        $primaryEmail = strtolower(trim($request->primary_email));

        // Check for duplicates with primary email
        foreach ($additionalEmails as $email) {
            if ($email === $primaryEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Additional emails cannot be the same as primary email.'
                ], 422);
            }
        }

        // Strict verification check — all additional emails must be verified via OTP
        foreach ($additionalEmails as $email) {
            if (!isset($prefs[$email]) || empty($prefs[$email]['verified'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Email '{$email}' must be verified before saving."
                ], 422);
            }
        }

        // Update per-email notification toggles
        $emailNotifications = $request->email_notifications ?? [];
        foreach ($additionalEmails as $email) {
            if (isset($prefs[$email])) {
                $prefs[$email]['notifications'] = isset($emailNotifications[$email])
                    && $emailNotifications[$email] == '1';
            }
        }

        // Remove preferences for emails no longer in the list
        $prefsToKeep = [];
        foreach ($prefs as $email => $pref) {
            if (in_array($email, $additionalEmails)) {
                $prefsToKeep[$email] = $pref;
            }
        }

        // Update global notification preferences if provided
        if ($request->has('notification_email')) {
            $user->notification_email = $request->boolean('notification_email');
        }
        if ($request->has('notification_push')) {
            $user->notification_push = $request->boolean('notification_push');
        }
        if ($request->has('notification_whatsapp')) {
            $user->notification_whatsapp = $request->boolean('notification_whatsapp');
        }

        // Update primary email and vendor profile
        $user->email = $primaryEmail;
        $user->save();

        $profile->additional_emails  = $additionalEmails;
        $profile->email_preferences  = $prefsToKeep;
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Email settings updated successfully.',
            'data'    => [
                'primary_email'     => $user->email,
                'additional_emails' => $profile->additional_emails,
                'email_preferences' => $profile->email_preferences,
            ]
        ]);
    }

    /**
     * Delete a specific additional email
     */
    public function deleteEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $email   = strtolower(trim($request->input('email')));
        $user    = Auth::user();
        $profile = $user->vendorProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found.'
            ], 404);
        }

        $additionalEmails = $profile->additional_emails ?? [];
        $prefs            = $profile->email_preferences ?? [];

        if (!in_array($email, $additionalEmails)) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found in additional emails.'
            ], 404);
        }

        // Remove the email and its preferences
        $profile->additional_emails = array_values(array_filter(
            $additionalEmails,
            fn($e) => $e !== $email
        ));
        unset($prefs[$email]);
        $profile->email_preferences = $prefs;
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Email removed successfully.'
        ]);
    }
}