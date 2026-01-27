<?php

namespace App\Http\Controllers\Vendor;

use App\Models\VendorEmail;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    protected $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
        $this->middleware('auth');
        $this->middleware('vendor'); // Only vendors can manage emails
    }

    /**
     * Get all vendor emails
     * GET /vendor/emails
     */
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        // Get verified and pending emails
        $verified = $this->emailVerificationService->getVerifiedEmails($vendor);
        $pending = $this->emailVerificationService->getPendingEmails($vendor);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'verified' => $verified,
                'pending' => $pending,
            ]);
        }

        return view('vendor.emails.index', [
            'verified' => $verified,
            'pending' => $pending,
        ]);
    }

    /**
     * Add new email
     * POST /vendor/emails/add
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $vendor = Auth::user();
        $email = $request->email;

        // Check if email already exists
        if ($email === $vendor->email) {
            return response()->json([
                'success' => false,
                'message' => 'This is your primary email'
            ], 422);
        }

        if (VendorEmail::where(['user_id' => $vendor->id, 'email' => $email])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already added'
            ], 422);
        }

        try {
            // Add email to vendor
            $this->emailVerificationService->addEmail($vendor, $email, false);

            // Send OTP
            if ($this->emailVerificationService->sendOTP($vendor, $email, 'vendor_email_verification')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email added. OTP sent to your email address.',
                    'email' => $email,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Failed to add vendor email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add email'
            ], 500);
        }
    }

    /**
     * Verify email OTP
     * POST /vendor/emails/verify
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|digits:6',
        ]);

        $vendor = Auth::user();
        $email = $request->email;

        try {
            // Verify OTP
            if ($this->emailVerificationService->verifyOTP($vendor, $email, $request->otp, 'vendor_email_verification')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email verified successfully',
                    'email' => $email,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Email verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Verification failed'
            ], 500);
        }
    }

    /**
     * Resend OTP
     * POST /vendor/emails/resend-otp
     */
    public function resendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $vendor = Auth::user();
        $email = $request->email;

        $result = $this->emailVerificationService->resendOTP($vendor, $email, 'vendor_email_verification');

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    /**
     * Delete email
     * DELETE /vendor/emails
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $vendor = Auth::user();
        $email = $request->email;

        try {
            // Cannot delete primary email
            if ($email === $vendor->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your primary email'
                ], 422);
            }

            if (!$this->emailVerificationService->removeEmail($vendor, $email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove your only verified email'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email removed successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to remove vendor email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove email'
            ], 500);
        }
    }

    /**
     * Get email verification status
     * GET /vendor/emails/status
     */
    public function getStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $vendor = Auth::user();
        $email = $request->email;

        $isVerified = $this->emailVerificationService->isEmailVerified($vendor, $email);
        $hasPending = count($this->emailVerificationService->getPendingEmails($vendor)) > 0;

        return response()->json([
            'email' => $email,
            'is_verified' => $isVerified,
            'has_pending' => $hasPending,
        ]);
    }
}
