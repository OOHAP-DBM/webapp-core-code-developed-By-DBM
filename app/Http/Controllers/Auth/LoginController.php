<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function showMobileLoginForm()
    {
        return view('auth.login-mobile');
    }
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        
        $credentials = $request->validate([
            'login' => 'required|string', // Email or phone
            'password' => 'required|string',
        ]);

        // Determine if login is email or phone
        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // Attempt authentication
        $user = User::where($loginType, $credentials['login'])->whereNull('deleted_at')->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }
        // Check if user is suspended
        if ($user->isSuspended()) {
            throw ValidationException::withMessages([
                'login' => ['Your account has been suspended. Please contact support.'],
            ]);
        }

        // Check if user is pending verification (except vendors)
        if ($user->status === 'pending_verification' && !$user->isVendor()) {
            throw ValidationException::withMessages([
                'login' => ['Your account is pending verification. Please check your email.'],
            ]);
        }

        // Check if user is not active
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'login' => ['Your account is not active.'],
            ]);
        }

      
        // Login the user
        Auth::login($user, $request->boolean('remember'));
       

        // Update last login timestamp
        $user->updateLastLogin();
       
        $request->session()->regenerate();
        \Log::info('Web login successful for user after  $request->session()->regenerate();: ' . $this->redirectBasedOnRole($user));
        // Role-based redirect
        
        return $this->redirectBasedOnRole($user);
    }

    /**
     * Redirect user based on their role and onboarding status
     */
    protected function redirectBasedOnRole(User $user)
    {
        $role = $user->getPrimaryRole();
        // $role = $user->active_role;

        switch ($role) {
            case 'customer':
                return redirect()->intended(route('home'));
            case 'vendor':
                return $this->handleVendorRedirect($user);

            case 'admin':
                return redirect()->intended(route('admin.dashboard'));

            default:
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Invalid user role. Please contact support.');
        }
    }

    /**
     * Handle vendor-specific redirect logic
     */
    protected function handleVendorRedirect(User $user)
    {
        $vendorProfile = $user->vendorProfile;

        // // If no vendor profile exists, redirect to onboarding
        if (!$vendorProfile) {
            return redirect()->route('vendor.onboarding.contact-details')
                ->with('info', 'Please complete your vendor onboarding.');
        }
        // Check onboarding status
        switch ($vendorProfile->onboarding_status) {
            case 'draft':
                // Resume onboarding at current step
                return $this->redirectToOnboardingStep($vendorProfile->onboarding_step);

            case 'pending_approval':
                // Show waiting screen
                return redirect()->route('vendor.dashboard')
                    ->with('info', 'Your application is under review. We will notify you once approved.');

            case 'approved':
                // Full access to vendor dashboard
                return redirect()->intended(route('vendor.dashboard'));

            case 'rejected':
                // Show rejection screen
                return redirect()->route('vendor.onboarding.rejected')
                    ->with('error', 'Your vendor application was rejected. Please contact support.');

            case 'suspended':
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Your vendor account has been suspended. Please contact support.');

            default:
                return redirect()->route('vendor.onboarding.contact-details');
        }
    }

    /**
     * Redirect to specific onboarding step
     */
    protected function redirectToOnboardingStep(int $step)
    {
        $routes = [
            1 => 'vendor.onboarding.contact-details',
            2 => 'vendor.onboarding.business-info',
            3 => 'vendor.onboarding.kyc-documents',
            4 => 'vendor.onboarding.bank-details',
            5 => 'vendor.onboarding.terms-agreement',
        ];

        $route = $routes[$step] ?? 'vendor.onboarding.contact-details';

        return redirect()->route($route)
            ->with('info', 'Please complete your vendor onboarding.');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
