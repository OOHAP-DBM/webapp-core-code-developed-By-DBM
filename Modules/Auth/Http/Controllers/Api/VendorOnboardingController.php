<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\JsonResponse;

/**
 * @group Vendor Onboarding
 *
 * APIs for vendor onboarding (contact verification and business info)
 */
class VendorOnboardingController extends Controller
{
    /**
     * STEP RULE:
     * Email signup → phone verification Optional 
     * Phone signup → email verification OPTIONAL
     */
    private function upgradeToStepTwo(User $user): void
    {
        $profile = $user->vendorProfile;
        if (!$profile) return;

        if ($profile->onboarding_step < 2) {
            $profile->update(['onboarding_step' => 2]);
        }
    }

    /**
     * @group Vendor Onboarding
     * @bodyParam contact string required The contact (phone or email). Example: 9876543210
     * @response 200 {"message": "OTP sent"}
     * @response 422 {"message": "Validation error"}
     */

    /**
     * @OA\Post(
     *     path="/auth/vendor/onboarding/send-otp",
     *     tags={"Vendor Onboarding"},
     *     summary="Send OTP to vendor phone/email",
     *     description="Generates and sends a 4-digit OTP to the provided identifier (phone or email).",
        * security={{"sanctum":{}}},
        * @OA\RequestBody(
        * required=true,
        * @OA\JsonContent(
        * required={"identifier"},
        * @OA\Property(property="identifier", type="string", example="9876543210", description="Phone number or Email address")
        * )
        * ),
        * @OA\Response(
        * response=200,
        * description="OTP sent successfully",
        * @OA\JsonContent(@OA\Property(property="message", type="string", example="OTP sent successfully"))
        * ),
        * @OA\Response(response=401, description="Unauthenticated"),
        * @OA\Response(response=422, description="Validation error")
        * )
        */
    public function sendOtp(SendOTPRequest $request, OTPService $otpService): JsonResponse
    {
        $otpService->generateAndSendOTP($request->identifier);

        return response()->json(['message' => 'OTP sent successfully']);
    }


    /**
     * @group Vendor Onboarding
     * @bodyParam contact string required The phone number. Example: 9876543210
     * @bodyParam otp string required The OTP. Example: 1234
     * @response 200 {"message": "contact verified"}
     * @response 422 {"message": "Invalid OTP"}
     */

    /**
     * @OA\Post(
     *     path="/auth/vendor/onboarding/verify-otp",
     *     tags={"Vendor Onboarding"},
     *     summary="Verify vendor contact OTP",
     *     security={{"sanctum":{}}},
     *     description="Verifies the OTP and upgrades the vendor to Onboarding Step 2.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *     required={"identifier","otp"},
     *     @OA\Property(property="identifier", type="string", example="9876543210"),
     *     @OA\Property(property="otp", type="string", example="123456")
     *     )
     *     ),
     *     @OA\Response(
     *       response=200, 
     *     description="Verification successful",
     *     @OA\JsonContent(@OA\Property(property="message", type="string", example="Verification successful"))
     *     ),
     *     @OA\Response(response=422, description="Invalid OTP or expired")
     *     )
     */
    public function verifyOtp(VerifyOTPRequest $request, OTPService $otpService): JsonResponse
    {
        $otpService->verifyOTPForLoggedInUser(
            $request->identifier,
            $request->otp
        );

        $this->upgradeToStepTwo(Auth::user());

        return response()->json([
            'message' => 'Verification successful'
        ]);
    }



    /**
     * @OA\Post(
     * path="/auth/vendor/onboarding/skip-contact",
     * tags={"Vendor Onboarding"},
     * summary="Skip Contact verification",
     * security={{"sanctum":{}}},
     * @OA\Response(response=200, description="Contact verification skipped")
     * )
     */
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

    public function submitBusinessInfo(VendorBusinessInfoRequest $request, VendorOnboardingService $service): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->vendorProfile;

        if (!$profile) {
            return response()->json(['message' => 'Vendor profile not found'], 404);
        }

        // Logic to save business info via service
        $service->saveBusinessInfo($user, $request->validated());
        $profile->update([
            'onboarding_step' => 3,
            'onboarding_status' => 'pending_approval'
        ]);

        return response()->json(['message' => 'Business info submitted']);
    }

    /**
     * @OA\Post(
     * path="/auth/vendor/onboarding/skip-business-info",
     * tags={"Vendor Onboarding"},
     * summary="Skip business info/KYC step",
     * description="Allows the vendor to skip the business information step. Sets onboarding_step to 3 and status to 'pending_approval'.",
     * security={{"sanctum":{}}},
     * * @OA\Response(
     * response=200,
     * description="Business info skipped successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Business info skipped")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated - Token missing or invalid"
     * ),
     * @OA\Response(
     * response=404,
     * description="Vendor profile not found",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Vendor profile not found")
     * )
     * )
     * )
     */
    public function skipBusinessInfo(): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->vendorProfile;

        if (!$profile) {
            return response()->json(['message' => 'Vendor profile not found'], 404);
        }

        $profile->update([
            'onboarding_step' => 3,
            'onboarding_status' => 'pending_approval'
        ]);

        return response()->json(['message' => 'Business info skipped']);
    }
}
