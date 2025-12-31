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

    /**
     * @OA\Post(
     *     path="/auth/vendor/onboarding/send-otp",
     *     tags={"Vendor Onboarding"},
     *     summary="Send OTP to vendor phone",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="9876543210")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
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

    /**
     * @OA\Post(
     *     path="/auth/vendor/onboarding/verify-phone-otp",
     *     tags={"Vendor Onboarding"},
     *     summary="Verify vendor phone OTP",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone","otp"},
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Phone verified"),
     *     @OA\Response(response=422, description="Invalid OTP")
     * )
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

    /**
     * @OA\Post(
     *     path="/auth/vendor/onboarding/send-email-otp",
     *     tags={"Vendor Onboarding"},
     *     summary="Send OTP to vendor email",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="vendor@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OTP sent"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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

    /**
     * @OA\Post(
     *     path="/auth/vendor/onboarding/verify-email-otp",
     *     tags={"Vendor Onboarding"},
     *     summary="Verify vendor email OTP",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","otp"},
     *             @OA\Property(property="email", type="string", example="vendor@example.com"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Email verified"),
     *     @OA\Response(response=422, description="Invalid OTP")
     * )
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


    /**
     * @OA\Post(
     *     path="/auth/vendor/onboarding/business-info",
     *     tags={"Vendor Onboarding"},
     *     summary="Submit vendor business & KYC information",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="gstin", type="string", example="22AAAAA0000A1Z5"),
     *                 @OA\Property(property="business_type", type="string", example="Proprietorship"),
     *                 @OA\Property(property="business_name", type="string", example="OOH Media Pvt Ltd"),
     *                 @OA\Property(property="registered_address", type="string", example="123 Main Street"),
     *                 @OA\Property(property="city", type="string", example="Delhi"),
     *                 @OA\Property(property="state", type="string", example="Delhi"),
     *                 @OA\Property(property="pincode", type="string", example="110001"),
     *
     *                 @OA\Property(property="bank_name", type="string", example="State Bank of India"),
     *                 @OA\Property(property="account_number", type="string", example="1234567890"),
     *                 @OA\Property(property="ifsc_code", type="string", example="SBIN0001234"),
     *                 @OA\Property(property="account_holder_name", type="string", example="John Doe"),
     *
     *                 @OA\Property(property="pan_number", type="string", example="ABCDE1234F"),
     *                 @OA\Property(
     *                     property="pan_card_document",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Business info submitted"),
     *     @OA\Response(response=403, description="Contact verification required"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    public function submitBusinessInfo(VendorBusinessInfoRequest $request, VendorOnboardingService $service)
    {
        $user = Auth::user();
        $profile = $user->vendorProfile;
        // if (!$user->email_verified_at || !$user->phone_verified_at) {
        //     return response()->json(['message' => 'Contact verification required'], 403);
        // }
        $service->saveBusinessInfo($user, $request->validated());
        $profile->onboarding_step = 3;
        $profile->onboarding_status = 'pending_approval';
        $profile->save();
        return response()->json(['message' => 'Business info submitted']);
    }
}
