<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Notifications\AdminUserRegisteredNotification;
use App\Notifications\UserWelcomeNotification;
use App\Notifications\VendorApprovalPendingNotification;




class RegisterController extends Controller
{
    /**
     * Show role selection screen (first step)
     */
    public function showRoleSelection()
    {
        return view('auth.role-selection');
    }

    /**
     * Store selected role in session
     */
    public function storeRoleSelection(Request $request)
    {
        $request->validate([
            'role' => 'required|in:customer,vendor',
        ]);

        // Store role in session
        session(['signup_role' => $request->role]);

        return redirect()->route('register.form');
    }

    /**
     * Show registration form (second step)
     */
    public function showRegistrationForm()
    {
        // Ensure role is selected
        if (!session()->has('signup_role')) {
            return redirect()->route('register.role-selection')
                ->with('error', 'Please select your role first.');
        }

        $role = session('signup_role');

        return view('auth.register', compact('role'));
    }

    public function showMobileForm(Request $request)
    {
        if (!session()->has('signup_role')) {
            return redirect()->route('register.role-selection')
                ->with('error', 'Please select role first');
        }
        $role = session('signup_role');
        return view('auth.register-mobile', compact('role'));
    }



    /**
     * Handle registration
     */
    public function register(RegisterRequest $request)
    {

        // Restore role from request if session is missing (for multi-step forms)
        if (!session()->has('signup_role') && $request->filled('role')) {
            session(['signup_role' => $request->input('role')]);
        }
        if (!session()->has('signup_role')) {
            return redirect()->route('register.role-selection')
                ->with('error', 'Please select your role first.');
        }
        $role = session('signup_role');

        DB::beginTransaction();

        try {
            \Log::debug('RegisterController@register: request', $request->all());
            // Create user
            $user = User::create([
                'name'  => $request->name,

                'email' => $request->email,
                'email_verified_at' => $request->email_verified ? now() : null,

                'phone' => $request->phone,
                'phone_verified_at' => $request->phone_verified ? now() : null,

                'password' => Hash::make($request->password),

                'status' => 'active',
            ]);


            // Assign role
            $user->assignRole($role);

            // Send welcome email (queued - better performance)
            try {
                if (!empty($user->email)) {
                    Mail::to($user->email)->send(
                        $role === 'vendor'
                            ? new \Modules\Mail\VendorWelcomeMail($user)
                            : new \Modules\Mail\CustomerWelcomeMail($user)
                    );
                }
            } catch (\Throwable $e) {
                \Log::error('Welcome mail failed', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
            if ($role === 'customer') {

                // 🔔 Admin notification
                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(
                        new AdminUserRegisteredNotification($user, 'customer')
                    );
                }

                // 🔔 Customer dashboard notification
                $user->notify(
                    new UserWelcomeNotification('customer')
                );
            }


            // Handle vendor-specific setup
          // Handle vendor-specific setup
            if ($role === 'vendor') {

                // 1️⃣ Create vendor profile
                VendorProfile::create([
                    'user_id' => $user->id,
                    'onboarding_status' => 'draft',
                    'onboarding_step' => 1,
                ]);
                // 3️⃣ Notify admins (approval pending)
                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(
                        new VendorApprovalPendingNotification($user)
                    );
                }

                // 4️⃣ Notify vendor
                $user->notify(
                    new VendorApprovalPendingNotification($user)
                );

                // 2️⃣ Commit DB changes FIRST
                DB::commit();

                

                // 5️⃣ Clear session role
                session()->forget('signup_role');

                // 6️⃣ Login vendor
                Auth::login($user);

                // 7️⃣ Redirect to onboarding
                return redirect()->route('vendor.onboarding.contact-details')
                    ->with('success', 'Account created! Please complete your vendor onboarding.');
            }


            // Customer flow
            DB::commit();

            // Clear session role
            session()->forget('signup_role');

            // Login the customer
            Auth::login($user);

            // Redirect to customer dashboard
            return redirect()->route('home')
                  ->with('success', 'Welcome to OohApp! Your account has been created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('customer.dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent to your email!');
    }

    public function sendEmailOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
         if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered. Please login instead.',
            ], 422);
        }
        $otp = rand(1000, 9999);
        // $otp = 1234;

        Cache::put('email_otp_' . $request->email, $otp, now()->addMinutes(10));
        // Send OTP via email
        // echo $otp; // For testing purposes
        try {
            Mail::raw(
                "Your OOHAPP email verification code is: {$otp}\n\nPlease enter this code to verify your email address. If you did not request this, you may safely ignore this message.",
                function ($m) use ($request) {
                    $m->to($request->email)->subject('OOHAPP Email Verification');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Email OTP send failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
        }
        return response()->json(['success' => true]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);
        $cachedOtp = Cache::get('email_otp_' . $request->email);
        if ($cachedOtp && $request->otp == $cachedOtp) {
            Cache::forget('email_otp_' . $request->email);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Invalid OTP']);
    }

    public function sendPhoneOtp(Request $request)
    {
          if (\App\Models\User::where('phone', $request->phone)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This mobile number is already registered. Please login.',
                ], 422);
            }
        $request->validate(['phone' => 'required']);
        $otp = rand(100000, 999999);
        // $otp = 1234;
        Cache::put('phone_otp_' . $request->phone, $otp, now()->addMinutes(10));
        // Send OTP via SMS (replace with your SMS gateway logic)
        // Example: Http::post('https://sms-gateway/send', [...]);
        // For demo:
        // Log::info(\"Send OTP $otp to phone {$request->phone}\");
        return response()->json(['success' => true]);
    }

    public function verifyPhoneOtp(Request $request)
    {
        $request->validate(['phone' => 'required', 'otp' => 'required']);
        $cachedOtp = Cache::get('phone_otp_' . $request->phone);
        if ($cachedOtp && $request->otp == $cachedOtp) {
            Cache::forget('phone_otp_' . $request->phone);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Invalid OTP']);
    }

    public function skipContactVerification(): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->vendorProfile;

        if (!$profile) {
            return response()->json(['message' => 'Vendor profile not found'], 404);
        }
        $profile->update(['onboarding_step' => 2]);
        return response()->json(['message' => 'Contact verification skipped']);
    }
}
