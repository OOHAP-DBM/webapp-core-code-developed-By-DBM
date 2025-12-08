<?php

namespace Modules\Auth\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Auth\Http\Requests\SendOTPRequest;
use Modules\Auth\Http\Requests\VerifyOTPRequest;
use Modules\Auth\Services\OTPService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OTPController extends Controller
{
    public function __construct(
        protected OTPService $otpService
    ) {}

    /**
     * Show OTP request form
     */
    public function showOTPForm(): View
    {
        return view('auth.otp');
    }

    /**
     * Send OTP
     */
    public function sendOTP(SendOTPRequest $request): RedirectResponse
    {
        $result = $this->otpService->generateAndSendOTP($request->input('identifier'));

        if (!$result['success']) {
            return back()->withErrors(['identifier' => $result['message']]);
        }

        return back()
            ->with('success', $result['message'])
            ->with('show_verify_form', true)
            ->with('identifier', $request->input('identifier'));
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(VerifyOTPRequest $request): RedirectResponse
    {
        $result = $this->otpService->verifyOTP(
            $request->input('identifier'),
            $request->input('otp')
        );

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        $user = $result['user'];

        // Login user
        Auth::login($user);

        // Redirect to appropriate dashboard
        return redirect()->route($user->getDashboardRoute())
            ->with('success', 'Logged in successfully!');
    }

    /**
     * Resend OTP
     */
    public function resendOTP(SendOTPRequest $request): RedirectResponse
    {
        $result = $this->otpService->resendOTP($request->input('identifier'));

        if (!$result['success']) {
            return back()->withErrors(['identifier' => $result['message']]);
        }

        return back()->with('success', 'OTP resent successfully');
    }
}
