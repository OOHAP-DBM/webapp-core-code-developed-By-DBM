<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Auth\Http\Requests\Api\Vendor\SendPhoneOtpRequest;
use App\Http\Requests\Api\Vendor\VerifyPhoneOtpRequest;
use Modules\Auth\Http\Requests\SendOTPRequest;
use Modules\Auth\Http\Requests\VerifyOTPRequest;
use App\Http\Requests\Vendor\VendorBusinessInfoRequest;
use Modules\Auth\Services\OTPService;
use App\Services\VendorOnboardingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VendorProfile;

/**
 * @group Vendor Onboarding
 *
 * APIs for vendor onboarding (contact verification and business info)
 */
class VendorOnboardingController extends Controller
{
    /**
     * @group Vendor Onboarding
     * @bodyParam phone string required The phone number. Example: 9876543210
     * @response 200 {"message": "OTP sent"}
     * @response 422 {"message": "Validation error"}
     */
    public function sendOtp(SendOTPRequest $request, OTPService $otpService)
    {
        // $user = Auth::user();
        $otpService->generateAndSendOTP($request->phone);
        return response()->json(['message' => 'OTP sent']);
    }

    /**
     * @group Vendor Onboarding
     * @bodyParam phone string required The phone number. Example: 9876543210
     * @bodyParam otp string required The OTP. Example: 1234
     * @response 200 {"message": "Phone verified"}
     * @response 422 {"message": "Invalid OTP"}
     */
    public function verifyPhoneOtp(VerifyOTPRequest $request, OTPService $otpService)
    {
        $user = Auth::user();
        if (!$otpService->verifyOTP($request->phone, $request->otp)) {
            return response()->json(['message' => 'Invalid OTP'], 422);
        }
        $user->phone_verified_at = now();
        $user->save();
        $profile = $user->vendorProfile;
        if ($user->email_verified_at && $user->phone_verified_at && $profile->onboarding_step < 2) {
            $profile->onboarding_step = 2;
            $profile->save();
        }
        return response()->json(['message' => 'Phone verified']);
    }

    /**
     * @group Vendor Onboarding
     * @bodyParam email string required The email address. Example: vendor@example.com
     * @response 200 {"message": "OTP sent"}
     * @response 422 {"message": "Validation error"}
     */
    public function sendEmailOtp(SendOTPRequest $request, OTPService $otpService)
    {
        $user = Auth::user();
        $otpService->generateAndSendOTP($request->email);
        return response()->json(['message' => 'OTP sent']);
    }

    /**
     * @group Vendor Onboarding
     * @bodyParam email string required The email address. Example: vendor@example.com
     * @bodyParam otp string required The OTP. Example: 1234
     * @response 200 {"message": "Email verified"}
     * @response 422 {"message": "Invalid OTP"}
     */
    public function verifyEmailOtp(VerifyOTPRequest $request, OTPService $otpService)
    {
        $user = Auth::user();
        if (!$otpService->verifyOTP($request->email, $request->otp)) {
            return response()->json(['message' => 'Invalid OTP'], 422);
        }
        $user->email_verified_at = now();
        $user->save();
        $profile = $user->vendorProfile;
        if ($user->email_verified_at && $user->phone_verified_at && $profile->onboarding_step < 2) {
            $profile->onboarding_step = 2;
            $profile->save();
        }
        return response()->json(['message' => 'Email verified']);
    }

    /**
     * @group Vendor Onboarding
     * @bodyParam gstin string required GSTIN. Example: 22AAAAA0000A1Z5
     * @bodyParam business_type string required Business type. Example: Proprietorship
     * @bodyParam business_name string required Business name. Example: OOH Media
     * @bodyParam registered_address string required Address. Example: 123 Main St
     * @bodyParam pincode string required Pincode. Example: 110001
     * @bodyParam city string required City. Example: Delhi
     * @bodyParam state string required State. Example: Delhi
     * @bodyParam country string required Country. Example: India
     * @bodyParam bank_name string required Bank name. Example: SBI
     * @bodyParam account_number string required Account number. Example: 1234567890
     * @bodyParam ifsc_code string required IFSC code. Example: SBIN0001234
     * @bodyParam account_holder_name string required Account holder name. Example: John Doe
     * @bodyParam pan_number string required PAN number. Example: ABCDE1234F
     * @bodyParam pan_card_document file required PAN card document (PDF/Image)
     * @response 200 {"message": "Business info submitted"}
     * @response 403 {"message": "Contact verification required"}
     * @response 422 {"message": "Validation error"}
     */
    public function submitBusinessInfo(VendorBusinessInfoRequest $request, VendorOnboardingService $service)
    {
        $user = Auth::user();
        $profile = $user->vendorProfile;
        if (!$user->email_verified_at || !$user->phone_verified_at) {
            return response()->json(['message' => 'Contact verification required'], 403);
        }
        $service->saveBusinessInfo($user, $request->validated());
        $profile->onboarding_step = 3;
        $profile->onboarding_status = 'pending_approval';
        $profile->save();
        return response()->json(['message' => 'Business info submitted']);
    }
}
