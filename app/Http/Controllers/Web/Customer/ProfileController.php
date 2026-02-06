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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Twilio\Rest\Client;

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
            'gstin'        => 'nullable|string|max:15|unique:users,gstin,' . $user->id,
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
        $request->validate(['identifier' => 'required']);

        $user = auth()->user();
        $identifier = trim($request->identifier);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        $otp = random_int(1000, 9999);
        $cacheKey = "otp:{$user->id}:{$identifier}";
        Cache::put($cacheKey, $otp, now()->addMinute());
        try {
            if ($isEmail) {
                Mail::raw(
                    "Your OOHAPP OTP is {$otp}. This OTP will expire in 1 minute.\n\n– Team OOHAPP",
                    fn ($msg) => $msg->to($identifier)->subject('OOHAPP OTP')
                );
            } else {
                $twilio = new Client(
                    env('TWILIO_SID'),
                    env('TWILIO_TOKEN')
                );

                $twilio->messages->create(
                    '+91' . $identifier,
                    [
                        'from' => env('TWILIO_FROM'),
                        'body' => "Your OOHAPP OTP is {$otp}. Valid for 1 minute. – OOHAPP"
                    ]
                );
            }
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            Log::error('OTP send failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP. Try again.'
            ], 500);
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
        $cacheKey = "otp:{$user->id}:{$identifier}";
        $cachedOtp = Cache::get($cacheKey);
        if (!$cachedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please resend.'
            ], 422);
        }
        if ((string)$cachedOtp !== (string)$request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 422);
        }
        Cache::forget($cacheKey);
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user->update([
                'email' => $identifier,
                'email_verified_at' => now(),
            ]);
        } else {
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
    public function billingAddress()
    {
        return view('customer.profile.billingAddress');
    }
    public function billingAddressUpdate(Request $request)
    {
        $user = auth()->user();

        // ✅ Validation
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone' => 'required|digits:10|unique:users,phone,' . auth()->id(),
            'email'            => 'required|email|max:255',
            'billing_address'  => 'required|string|max:500',
            'billing_city'     => 'nullable|string|max:255',
            'billing_state'    => 'nullable|string|max:255',
            'billing_pincode'  => 'required|string|max:10',
        ]);

        $updateData = [
            'name'             => $validated['name'],
            'billing_address'  => $validated['billing_address'],
            'billing_city'     => $validated['billing_city'] ?? null,
            'billing_state'    => $validated['billing_state'] ?? null,
            'billing_pincode'  => $validated['billing_pincode'],
        ];

        /**
         * ================= EMAIL CHECK =================
         */
        if ($validated['email'] !== $user->email) {
            $updateData['email'] = $validated['email'];
            $updateData['email_verified_at'] = null; 
        }

        /**
         * ================= PHONE CHECK =================
         */
        if ($validated['phone'] !== $user->phone) {
            $updateData['phone'] = $validated['phone'];
            $updateData['phone_verified_at'] = null;
        }

       
        $user->update($updateData);

        return redirect()
            ->back()
            ->with('success', 'Billing address updated successfully. Please verify updated contact details.');
    }
}
