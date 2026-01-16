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
use App\Models\User;

// Customer Profile Section @Aviral



/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Customer & Vendor authentication APIs"
 * )
 */

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
     *     path="/profile/customer/show",
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
     *     path="/profile/customer/update",
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

        if (!$user->email_verified_at && !$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Verify either email or phone first',
            ], 403);
        }


        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'gstin' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $service->updateAvatar($user, $request->file('avatar'));
        }


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
     *     path="/profile/avatar",
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
     *     path="/profile/change-password",
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
     *     path="/profile/send-otp",
     *     tags={"Profile OTP"},
     *     summary="Send OTP to change email or phone",
     *     description="Sends a one-time password (OTP) to the provided email or phone number. Requires Bearer authentication.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type","value"},
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"email","phone"},
     *                 example="email",
     *                 description="Type of identifier to change"
     *             ),
     *             @OA\Property(
     *                 property="value",
     *                 type="string",
     *                 example="newuser@example.com",
     *                 description="New email or phone number"
     *             )
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
     *     @OA\Response(
     *         response=422,
     *         description="Validation or business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Email already taken")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=429,
     *         description="Too many OTP requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Please wait before requesting another OTP")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */


    /* ==============================
     | SEND OTP
     ============================== */
    public function sendOtp(Request $request, ProfileService $service)
    {
        $data = $request->validate([
            'type' => 'required|in:email,phone',
            'value' => 'required|string',
        ]);

        $user = $request->user();

        if ($data['type'] === 'email') {
            if ($data['value'] === $user->email) {
                return response()->json(['success' => false, 'message' => 'Same email'], 422);
            }
            if (User::where('email', $data['value'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Email already taken'], 422);
            }
        }

        if ($data['type'] === 'phone') {
            if ($data['value'] === $user->phone) {
                return response()->json(['success' => false, 'message' => 'Same phone'], 422);
            }
            if (User::where('phone', $data['value'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Phone already taken'], 422);
            }
        }

        $service->send($user, $data['type'], $data['value']);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
        ]);
    }




    /**
     * @OA\Post(
     *     path="/profile/verify-otp",
     *     tags={"Profile OTP"},
     *     summary="Verify OTP and update email or phone",
     *     description="Verifies the OTP sent to email or phone and updates the user's profile accordingly.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type","value","otp"},
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"email","phone"},
     *                 example="phone",
     *                 description="Type of identifier being verified"
     *             ),
     *             @OA\Property(
     *                 property="value",
     *                 type="string",
     *                 example="9876543210",
     *                 description="Email or phone number used for OTP"
     *             ),
     *             @OA\Property(
     *                 property="otp",
     *                 type="string",
     *                 example="1234",
     *                 description="4-digit OTP received by the user"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified and profile updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email updated successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Invalid or expired OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid or expired OTP")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function verifyOtp(Request $request, ProfileService $service)
    {
        $data = $request->validate([
            'type' => 'required|in:email,phone',
            'value' => 'required|string',
            'otp' => 'required|digits:4',
        ]);

        $service->verify(
            $request->user(),
            $data['type'],
            $data['value'],
            $data['otp']
        );

        return response()->json([
            'success' => true,
            'message' => ucfirst($data['type']) . ' updated successfully',
        ]);
    }
}
