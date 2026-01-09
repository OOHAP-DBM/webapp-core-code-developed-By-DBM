<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Modules\Auth\Services\OTPService;
use App\Services\ProfileService;
use Throwable;
// Customer Profile Section @Aviral


/**
 * @OA\Schema(
 *     schema="CustomerProfile",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=12),
 *     @OA\Property(property="name", type="string", example="Rahul Sharma"),
 *     @OA\Property(property="email", type="string", example="rahul@example.com"),
 *     @OA\Property(property="email_verified", type="boolean", example=true),
 *     @OA\Property(property="phone", type="string", example="9876543210"),
 *     @OA\Property(property="phone_verified", type="boolean", example=true),
 *     @OA\Property(property="avatar", type="string", example="https://app.com/storage/media/users/avatars/12/avatar.png"),
 *     @OA\Property(property="company_name", type="string", example="OOH Media Pvt Ltd"),
 *     @OA\Property(property="gstin", type="string", example="09ABCDE1234F1Z5")
 * )
 */

class ProfileController extends Controller
{

    /**
     * @OA\Get(
     *     path="/customer/profile",
     *     tags={"Customer Profile"},
     *     summary="Get logged-in customer profile",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/CustomerProfile")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function show(Request $request, ProfileService $service)
    {
        return response()->json([
            'success' => true,
            'data' => $service->response($request->user(), [
                'company_name' => $request->user()->company_name,
                'gstin' => $request->user()->gstin,
            ]),
        ]);
    }


    /**
     * @OA\Post(
     *     path="/customer/profile",
     *     tags={"Customer Profile"},
     *     summary="Update customer profile",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","email","phone"},
     *                 @OA\Property(property="name", type="string", example="Rahul Sharma"),
     *                 @OA\Property(property="email", type="string", example="rahul@example.com"),
     *                 @OA\Property(property="phone", type="string", example="9876543210"),
     *                 @OA\Property(property="company_name", type="string", example="OOH Media Pvt Ltd"),
     *                 @OA\Property(property="gstin", type="string", example="09ABCDE1234F1Z5"),
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     format="binary",
     *                     description="Profile image (jpg, png, webp)"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/CustomerProfile")
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Email or phone not verified"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function update(Request $request, ProfileService $service)
    {
        $user = $request->user();

        if (!$user->email_verified_at || !$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Verify email & phone first',
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'required|string|unique:users,phone,'.$user->id,
            'company_name' => 'nullable|string|max:255',
            'gstin' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $service->updateAvatar($user, $request->file('avatar'));
        }

        if ($data['email'] !== $user->email) $data['email_verified_at'] = null;
        if ($data['phone'] !== $user->phone) $data['phone_verified_at'] = null;

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data' => $service->response($user->fresh(), [
                'company_name' => $user->company_name,
                'gstin' => $user->gstin,
            ]),
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/customer/profile/avatar",
     *     tags={"Customer Profile"},
     *     summary="Remove customer avatar",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Avatar removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avatar removed successfully")
     *         )
     *     ),
     *
     *     @OA\Response(response=500, description="Unable to remove avatar")
     * )
     */

    public function removeAvatar(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->update(['avatar' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar removed successfully',
            ]);
        } catch (Throwable $e) {
            Log::error('AVATAR_REMOVE_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove avatar',
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/customer/profile/change-password",
     *     tags={"Customer Profile"},
     *     summary="Change account password",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="OldPass@123"),
     *             @OA\Property(property="new_password", type="string", example="NewPass@123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="NewPass@123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password changed successfully")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Invalid current password"),
     *     @OA\Response(response=500, description="Unable to change password")
     * )
     */

    public function changePassword(Request $request, ProfileService $service)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => [
                    'required',
                    'confirmed'
                ],
            ]);

            $result = $service->changePassword(
                $request->user(),
                $request->current_password,
                $request->new_password
            );

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 422);
        } catch (\Throwable $e) {
            \Log::error('PASSWORD_CHANGE_FAILED', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to change password',
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/customer/profile/send-otp",
     *     tags={"Customer Profile"},
     *     summary="Send OTP to email or phone",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier"},
     *             @OA\Property(property="identifier", type="string", example="rahul@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully")
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Invalid identifier"),
     *     @OA\Response(response=500, description="Unable to send OTP")
     * )
     */

    public function sendOtp(Request $request, OTPService $otpService)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $user = $request->user();

        if (!in_array($request->identifier, [$user->email, $user->phone])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid identifier',
            ], 403);
        }

        $result = $otpService->generateAndSendOTP($request->identifier);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 500);
    }



    /**
     * @OA\Post(
     *     path="/customer/profile/verify-otp",
     *     tags={"Customer Profile"},
     *     summary="Verify OTP for email or phone",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier","otp"},
     *             @OA\Property(property="identifier", type="string", example="rahul@example.com"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Verified successfully")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Invalid or expired OTP"),
     *     @OA\Response(response=500, description="OTP verification failed")
     * )
     */

    public function verifyOtp(Request $request, OTPService $otpService)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|digits:6',
        ]);

        $result = $otpService->verifyOTPForLoggedInUser(
            $request->identifier,
            $request->otp
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 422);
    }

}
