<?php

namespace App\Http\Controllers\Vendor;

use App\Services\MobileOTPService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MobileOTPController extends Controller
{
    protected $mobileOTPService;

    public function __construct(MobileOTPService $mobileOTPService)
    {
        $this->mobileOTPService = $mobileOTPService;
        $this->middleware('auth');
        $this->middleware('vendor'); // Only vendors can verify mobile
    }

    /**
     * Show mobile verification page
     * GET /vendor/verify-mobile
     */
    public function show()
    {
        $vendor = Auth::user();

        return view('vendor.mobile.verify', [
            'phone' => $vendor->phone,
            'is_verified' => $vendor->isMobileVerified(),
        ]);
    }

    /**
     * Send OTP to mobile
     * POST /vendor/mobile/send-otp
     */
    public function sendOTP(Request $request)
    {
        $vendor = Auth::user();

        // Validate mobile number if updating
        if ($request->filled('phone')) {
            $request->validate([
                'phone' => 'required|string|min:10|max:15',
            ]);

            $vendor->update(['phone' => $request->phone]);
        }

        try {
            if ($this->mobileOTPService->sendOTP($vendor, 'mobile_verification')) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent to your registered mobile number',
                    'phone' => $vendor->phone,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Failed to send mobile OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify mobile OTP
     * POST /vendor/mobile/verify
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|digits:6',
        ]);

        $vendor = Auth::user();

        if ($this->mobileOTPService->verifyOTP($vendor, $request->otp, 'mobile_verification')) {
            return response()->json([
                'success' => true,
                'message' => 'Mobile number verified successfully',
                'verified_at' => $vendor->fresh()->phone_verified_at,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP. Please try again.'
        ], 422);
    }

    /**
     * Resend OTP
     * POST /vendor/mobile/resend-otp
     */
    public function resendOTP()
    {
        $vendor = Auth::user();
        $result = $this->mobileOTPService->resendOTP($vendor, 'mobile_verification');

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    /**
     * Get mobile verification status
     * GET /vendor/mobile/status
     */
    public function getStatus()
    {
        $vendor = Auth::user();

        return response()->json([
            'is_verified' => $vendor->isMobileVerified(),
            'phone' => $vendor->phone,
            'verified_at' => $vendor->phone_verified_at,
        ]);
    }
}
