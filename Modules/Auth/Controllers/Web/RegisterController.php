<?php

namespace Modules\Auth\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Users\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Show registration form
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $role = $request->input('role', 'customer');

        $user = $this->userService->createUser(
            $request->validated(),
            $role
        );

        // Login user
        Auth::login($user);

        // Redirect to appropriate dashboard
        return redirect()->route($user->getDashboardRoute())
            ->with('success', 'Registration successful! Welcome to OOHAPP.');
    }
}
