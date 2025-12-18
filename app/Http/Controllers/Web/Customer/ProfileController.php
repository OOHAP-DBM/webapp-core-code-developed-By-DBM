<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Auth\Services\OTPService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(
        protected OTPService $otpService
    ) {}
    /**
     * Show customer profile.
     *
     * @return View
     */
    public function index(): View
    {
        return view('customer.profile.index');
    }

    /**
     * Update customer profile.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'company_name' => 'nullable|string|max:255',
            'gstin' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            try {
                $path = $request->file('avatar')->store(
                    'media/users/avatars/' . $user->id,
                    'public'
                );

                // 🔴 EXTRA SAFETY: file exists check
                if (!Storage::disk('public')->exists($path)) {
                    return back()->withErrors([
                        'avatar' => 'Avatar upload failed. Please try again.'
                    ]);
                }

                $validated['avatar'] = $path;

            } catch (\Exception $e) {
                return back()->withErrors([
                    'avatar' => 'Avatar upload failed. Please try again.'
                ]);
            }
        }

        $user->update($validated);

        return redirect()
            ->route('customer.profile.index')
            ->with('success', 'Profile updated successfully!');
    }
    public function removeAvatar()
    {
        $user = auth()->user();
        // Agar avatar hi nahi hai → kuch nahi karna
        if (!$user->avatar) {
            return back()->with('success', 'Profile picture already removed.');
        }
        // File delete (if exists)
        if (Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        // DB se avatar null
        $user->update([
            'avatar' => null
        ]);
        return response()->json(['success' => true]);
    }
    /**
     * Change password.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required'
        ]);

        $user = auth()->user();
        $identifier = trim($request->identifier);

        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        // ✅ UNIQUE CHECK (very important)
        if ($isEmail) {
            if (
                \App\Models\User::where('email', $identifier)
                    ->where('id', '!=', $user->id)
                    ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already in use'
                ], 422);
            }
        } else {
            if (!preg_match('/^[0-9]{10}$/', $identifier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid mobile number'
                ], 422);
            }

            if (
                \App\Models\User::where('phone', $identifier)
                    ->where('id', '!=', $user->id)
                    ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile number already in use'
                ], 422);
            }
        }

        // 🚫 Already verified
        if ($isEmail && $user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 422);
        }

        if (!$isEmail && $user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number already verified'
            ], 422);
        }

        // 🔥 Generate OTP on logged-in user
        $otp = $user->generateOTP();

        // 🔔 SEND OTP (based on type)
        if ($isEmail) {
            // Mail::to($identifier)->send(new OtpMail($otp));
        } else {
            // sendSms($identifier, "Your OTP is $otp");
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully'
        ]);
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'otp' => 'required|digits:4',
        ]);

        $user = auth()->user();
        $identifier = trim($request->identifier);

        if (!$user->isOTPValid($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        $user->clearOTP();

        // EMAIL VERIFY
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user->update([
                'email' => $identifier,
                'email_verified_at' => now(),
            ]);
        }
        // PHONE VERIFY
        else {
            $user->update([
                'phone' => $identifier,
                'phone_verified_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verified successfully'
        ]);
    }
}
