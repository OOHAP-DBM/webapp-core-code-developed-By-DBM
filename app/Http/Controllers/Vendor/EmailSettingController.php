<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\OTPService;
use App\Models\User;

class EmailSettingController extends Controller
{
    /**
     * Show email settings page
     */
    public function show()
    {
        return view('vendor.email-settings');
    }

    /**
     * Send verification OTP to email
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
        $user = Auth::user();

        // Check if email is already used by another user as primary email
        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return response()->json([
                'success' => false, 
                'message' => 'This email is already registered to another account.'
            ], 422);
        }

        // Check if email is already in user's additional emails
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

        // Send OTP
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
            'otp' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first()
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));
        $user = Auth::user();

        try {
            $otpService = app(OTPService::class);
            
            if ($otpService->verifyEmailOTP($email, $request->input('otp'), $user)) {
                // Mark email as verified in vendor profile preferences
                $profile = $user->vendorProfile;
                
                if (!$profile) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Vendor profile not found.'
                    ], 404);
                }

                $prefs = $profile->email_preferences ?? [];
                $prefs[$email] = [
                    'verified' => true, 
                    'verified_at' => now()->toDateTimeString(),
                    'notifications' => false // Default to disabled
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
     * Update email settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'primary_email' => 'required|email|max:255',
            'additional_emails' => 'nullable|array',
            'additional_emails.*' => 'required|email|max:255|distinct',
            'email_notifications' => 'nullable|array',
            'notification_email' => 'nullable|boolean',
            'notification_push' => 'nullable|boolean',
            'notification_whatsapp' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $profile = $user->vendorProfile;

        if (!$profile) {
            return back()->with('error', 'Vendor profile not found.');
        }

        $prefs = $profile->email_preferences ?? [];
        $additionalEmails = $request->additional_emails ?? [];

        // Normalize emails to lowercase
        $additionalEmails = array_map('strtolower', array_map('trim', $additionalEmails));
        $additionalEmails = array_unique($additionalEmails);
        $additionalEmails = array_values($additionalEmails); // Reindex array

        // Check for duplicates with primary email
        $primaryEmail = strtolower(trim($request->primary_email));
        foreach ($additionalEmails as $email) {
            if ($email === $primaryEmail) {
                return back()
                    ->withErrors(['additional_emails' => 'Additional emails cannot be the same as primary email.'])
                    ->withInput();
            }
        }

        // Strict verification check for all additional emails
        foreach ($additionalEmails as $email) {
            if (!isset($prefs[$email]) || empty($prefs[$email]['verified'])) {
                return back()
                    ->withErrors(['additional_emails' => "Email '{$email}' must be verified before saving."])
                    ->withInput();
            }
        }

        // Update notification preferences for each email
        $emailNotifications = $request->email_notifications ?? [];
        foreach ($additionalEmails as $email) {
            if (isset($prefs[$email])) {
                // Check if this specific email has notifications enabled
                $prefs[$email]['notifications'] = isset($emailNotifications[$email]) && $emailNotifications[$email] == '1';
            }
        }

        // Remove emails that are no longer in the list
        $prefsToKeep = [];
        foreach ($prefs as $email => $pref) {
            if (in_array($email, $additionalEmails)) {
                $prefsToKeep[$email] = $pref;
            }
        }
        // Update user settings
        $user->email = $primaryEmail;
        $user->save();
        // Update vendor profile
        $profile->additional_emails = $additionalEmails;
        $profile->email_preferences = $prefsToKeep;
        $profile->save();

        return back()->with('success', 'Email settings updated successfully.');
    }

      // Get notification preferences
    public function preferences(Request $request)
    {
        $user = Auth::user();
        $preferences = [
            'email' => $user->notification_email,
            'push' => $user->notification_push,
            'whatsapp' => $user->notification_whatsapp,
        ];
        // Vendor: multiple emails and preferences
        if ($user->isVendor()) {
            $vendorProfile = $user->vendorProfile;
            $preferences['primary_email'] = $user->email;
            $preferences['additional_emails'] = $vendorProfile?->additional_emails ?? [];
            $preferences['email_preferences'] = $vendorProfile?->email_preferences ?? [];
        }
        return response()->json($preferences);
    }

    // Update notification preferences
    public function updatePreferences(Request $request)
    {
        \Log::info('Updating notification preferences', ['user_id' => Auth::id(), 'data' => $request->all()]);
        $user = Auth::user();
        $data = $request->validate([
            'email' => 'boolean',
            'push' => 'boolean',
            'whatsapp' => 'boolean',
            'primary_email' => 'nullable|email',
            'additional_emails' => 'nullable|array',
            'additional_emails.*' => 'email',
            'email_preferences' => 'nullable|array',
        ]);

        // Update user preferences
        $user->notification_email = $data['email'] ?? $user->notification_email;
        $user->notification_push = $data['push'] ?? $user->notification_push;
        $user->notification_whatsapp = $data['whatsapp'] ?? $user->notification_whatsapp;

           if ($user->isVendor()) {
            $vendorProfile = $user->vendorProfile;
            if ($vendorProfile) {
                if (!empty($data['primary_email'])) {
                    $user->email = $data['primary_email'];
                    $user->save();
                }
                if (isset($data['additional_emails'])) {
                    $vendorProfile->additional_emails = $data['additional_emails'];
                }
                if (isset($data['email_preferences'])) {
                    $vendorProfile->email_preferences = $data['email_preferences'];
                }
                $vendorProfile->save();
            }
        }
        else {
            if (!empty($data['primary_email'])) {
                $user->email = $data['primary_email'];
            }
        }

        $user->save();

        // Vendor: handle multiple emails and preferences
     

        return response()->json(['success' => true]);
    }
}