<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Users\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Show login form
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->userService->verifyCredentials(
            $request->input('identifier'),
            $request->input('password')
        );

        if (!$user) {
            return back()->withErrors([
                'identifier' => 'Invalid credentials',
            ])->withInput($request->only('identifier'));
        }

        if (!$user->isActive()) {
            return back()->withErrors([
                'identifier' => 'Your account is ' . $user->status . '. Please contact support.',
            ])->withInput($request->only('identifier'));
        }

        // Login user
        Auth::login($user, $request->boolean('remember'));

        // Set active role to primary role if not set (PROMPT 96)
        if (!$user->active_role) {
            $user->update(['active_role' => $user->getPrimaryRole()]);
        }

        // Update last login
        $user->updateLastLogin();

        $request->session()->regenerate();

        // Redirect to appropriate dashboard
        return redirect()->route($user->getDashboardRoute())
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle logout
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully');
    }
}
