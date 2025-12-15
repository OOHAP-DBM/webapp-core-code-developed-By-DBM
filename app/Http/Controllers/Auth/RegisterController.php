<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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

    /**
     * Handle registration
     */
    public function register(RegisterRequest $request)
    {
        // Ensure role is in session
        if (!session()->has('signup_role')) {
            return redirect()->route('register.role-selection')
                ->with('error', 'Please select your role first.');
        }

        $role = session('signup_role');

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => $role === 'customer' ? 'active' : 'pending_verification',
            ]);

            // Assign role
            $user->assignRole($role);

            // Handle vendor-specific setup
            if ($role === 'vendor') {
                // Create vendor profile with draft status
                VendorProfile::create([
                    'user_id' => $user->id,
                    'onboarding_status' => 'draft',
                    'onboarding_step' => 1,
                ]);

                DB::commit();

                // Clear session role
                session()->forget('signup_role');

                // Login the vendor
                Auth::login($user);

                // Redirect to vendor onboarding
                return redirect()->route('vendor.onboarding.company-details')
                    ->with('success', 'Account created! Please complete your vendor onboarding.');
            }

            // Customer flow
            DB::commit();

            // Clear session role
            session()->forget('signup_role');

            // Login the customer
            Auth::login($user);

            // Redirect to customer dashboard
            return redirect()->route('customer.dashboard')
                ->with('success', 'Welcome to OohApp! Your account has been created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
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
}
