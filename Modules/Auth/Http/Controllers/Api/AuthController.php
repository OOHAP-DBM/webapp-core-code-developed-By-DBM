<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\SendOTPRequest;
use Modules\Auth\Http\Requests\VerifyOTPRequest;
use Modules\Auth\Services\OTPService;
use Modules\Users\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\DB;

// /**
//  * @OA\Tag(
//  *     name="Authentication",
//  *     description="Customer & Vendor authentication APIs"
//  * )
//  */

class AuthController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected OTPService $otpService
    ) {}

    /**
     * @OA\Post(
     *     path="/auth/register/otp/send",
     *     tags={"Authentication"},
     *     summary="Send OTP for registration",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier"},
     *             @OA\Property(property="identifier", type="string", example="test@email.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OTP sent for registration")
     * )
     */
    public function sendRegisterOTP(SendOTPRequest $request): JsonResponse
    {
        // Use the registration-aware service method
        $result = $this->otpService->generateAndSendRegisterOTP($request->input('identifier'));
        \Log::info('Register OTP Result:', $result);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400); // 400 is better than 404 for "Already Registered" validation
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'is_new_user' => $result['is_new_user'] ?? true,
                // Only include user_id if it exists in the result
                'user_id' => $result['user_id'] ?? null,
            ],
        ]);
    }
    /**
     * @OA\Post(
     *     path="/auth/register/otp/verify",
     *     tags={"Authentication"},
     *     summary="Verify OTP before registration",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier","otp"},
     *             @OA\Property(property="identifier", type="string", example="test@email.com"),
     *             @OA\Property(property="otp", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OTP verified")
     * )
     */

    public function verifyRegisterOTP(VerifyOTPRequest $request): JsonResponse
    {
        $result = $this->otpService->verifyRegisterOTP(
            $request->input('identifier'),
            $request->input('otp')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        // Now the user is marked as verified in the DB (email_verified_at or phone_verified_at)
        return response()->json([
            'success' => true,
            'message' => 'OTP verified. Please provide your name and password to complete registration.',
            'data' => [
                'identifier' => $request->input('identifier') // Pass this back to frontend
            ]
        ]);
    }
    /**
     * Register a new user
     * 
     * @group Authentication
     * @bodyParam name string required The user's name
     * @bodyParam email string required The user's email
     * @bodyParam phone string The user's phone number
     * @bodyParam password string required The user's password (min 8 chars)
     * @bodyParam password_confirmation string required Password confirmation
     * @bodyParam role string Role (customer or vendor). Default: customer
     */
    // public function register(RegisterRequest $request): JsonResponse
    // {
    //     $role = $request->input('role', 'customer');

    //     $user = $this->userService->createUser(
    //         $request->validated(),
    //         $role
    //     );

    //     // Set active role to assigned role (PROMPT 96)
    //     $user->update(['active_role' => $role]);

    //     // Create API token with role context
    //     $token = $user->createToken('auth_token', ['role:' . $role])->plainTextToken;

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Registration successful',
    //         'data' => [
    //             'user' => new UserResource($user->fresh()),
    //             'token' => $token,
    //             'token_type' => 'Bearer',
    //             'active_role' => $role,
    //         ],
    //     ], 201);
    // }
    // Modules/Auth/Http/Controllers/Api/AuthController.php


    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Complete registration after OTP verification",
     *     description="Registers user after OTP verification and issues access token",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","name","password","password_confirmation"},
     *
     *             @OA\Property(
     *                  property="email",
     *                 type="string",
     *                 example="test@email.com",
     *                 description="User email address or phone number used during OTP verification"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="John Doe"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="Password@123"
     *             ),
     *
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 example="Password@123"
     *             ),
     *
     *             @OA\Property(
     *                 property="role",
     *                 type="string",
     *                 enum={"customer","vendor"},
     *                 example="customer",
     *                 description="Allowed values: customer, vendor. Default is customer"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful"
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="OTP not verified"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */

    // public function register(RegisterRequest $request): JsonResponse
    // {
    //     $identifier = $request->input('email') ?? $request->input('phone');

    //     // 1. Find the user who was just verified
    //     $user = $this->userService->findUserByIdentifier($identifier);

    //     if (!$user) {
    //         return response()->json(['success' => false, 'message' => 'User not found.'], 404);
    //     }

    //     // 2. IMPORTANT: Check if they are actually verified
    //     // This prevents people from registering without an OTP
    //     if (!$user->email_verified_at && !$user->phone_verified_at) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Your identifier is not verified. Please verify OTP first.'
    //         ], 403);
    //     }

    //     // 3. Update the record with Name and Password
    //     $user->update([
    //         'name' => $request->name,
    //         'password' => Hash::make($request->password),
    //         'status' => 'active',
    //     ]);

    //     // 4. Assign Role
    //     $role = $request->input('role', 'customer');
    //     $user->update(['active_role' => $role]);
    //     $user->assignRole($role);
    //     // If using Spatie Permissions: $user->assignRole($role);

    //     // 5. Issue the Token (Now they are officially logged in)
    //     $token = $user->createToken('auth_token', ['role:' . $role])->plainTextToken;
    //     if ($role === 'vendor') {
    //         // Create vendor profile with draft status
    //         VendorProfile::create([
    //             'user_id' => $user->id,
    //             'onboarding_status' => 'draft',
    //             'onboarding_step' => 1,
    //         ]);
    //     }

    //         return response()->json([
    //         'success' => true,
    //         'message' => 'Registration complete!',
    //         'data' => [
    //             'user' => new UserResource($user->fresh()),
    //             'token' => $token,
    //         ],
    //     ], 201);
    // }
    public function register(RegisterRequest $request): JsonResponse
    {
        $identifier = $request->input('email') ?? $request->input('phone');

        // 1. Find the user
        $user = $this->userService->findUserByIdentifier($identifier);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // 2. Security Check: Ensure verification happened
        if (!$user->email_verified_at && !$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Your identifier is not verified. Please verify OTP first.'
            ], 403);
        }

        // Start Transaction
            DB::beginTransaction();

        try {
            // 3. Update Name, Password, and Status
            $user->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            // 4. Assign Role
            $role = $request->input('role', 'customer');
            $user->update(['active_role' => $role]);
            $user->assignRole($role);

            // 5. Vendor Specific Logic
            if ($role === 'vendor') {
                VendorProfile::create([
                    'user_id' => $user->id,
                    'onboarding_status' => 'draft',
                    'onboarding_step' => 1,
                ]);
            }

            // 6. Issue Token
            $token = $user->createToken('auth_token', ['role:' . $role])->plainTextToken;

            // Everything went well, save changes permanently
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration complete!',
                'data' => [
                    'user' => new UserResource($user->fresh()),
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            // Something went wrong, undo all database changes
            DB::rollBack();

            \Log::error('Registration failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null // Only show error in debug mode
            ], 500);
        }
    }

    /**
     * Login with email/phone and password
     * 
     * @group Authentication
     * @bodyParam identifier string required Email or phone number
     * @bodyParam password string required Password
     */
    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Authentication"},
     *     summary="Login with email/phone and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier","password"},
     *             @OA\Property(property="identifier", type="string", example="test@email.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful"
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->userService->verifyCredentials(
            $request->input('identifier'),
            $request->input('password')
        );

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is ' . $user->status,
            ], 403);
        }

        // Update last login
        $user->updateLastLogin();

        // Set active role to primary role if not set (PROMPT 96)
        if (!$user->active_role) {
            $user->update(['active_role' => $user->getPrimaryRole()]);
        }

        // Create API token with role context
        $activeRole = $user->fresh()->getActiveRole();
        $token = $user->createToken('auth_token', ['role:' . $activeRole])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user->fresh()),
                'token' => $token,
                'token_type' => 'Bearer',
                'active_role' => $activeRole,
            ],
        ]);
    }

    /**
     * Send OTP for login
     * 
     * @group Authentication
     * @bodyParam identifier string required Email or phone number
     */
    /**
     * @OA\Post(
     *     path="/auth/otp/send",
     *     tags={"Authentication"},
     *     summary="Send OTP for login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier"},
     *             @OA\Property(property="identifier", type="string", example="+919999999999")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OTP sent successfully")
     * )
     */

    public function sendOTP(SendOTPRequest $request): JsonResponse
    {
        $result = $this->otpService->generateAndSendOTP($request->input('identifier'));

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'user_id' => $result['user_id'],
            ],
        ]);
    }

    /**
     * Verify OTP and login
     * 
     * @group Authentication
     * @bodyParam identifier string required Email or phone number
     * @bodyParam otp string required 6-digit OTP
     */
    /**
     * @OA\Post(
     *     path="/auth/otp/verify",
     *     tags={"Authentication"},
     *     summary="Verify OTP and login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier","otp"},
     *             @OA\Property(property="identifier", type="string", example="+919999999999"),
     *             @OA\Property(property="otp", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OTP verified successfully")
     * )
     */
    public function verifyOTP(VerifyOTPRequest $request): JsonResponse
    {
        $result = $this->otpService->verifyOTP(
            $request->input('identifier'),
            $request->input('otp')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        $user = $result['user'];

        // Create API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }


   
    /**
     * Get authenticated user
     * 
     * @group Authentication
     * @authenticated
     */
    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Authentication"},
     *     summary="Get authenticated user profile",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="User profile")
     * )
     */

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }

    /**
     * Logout (revoke token)
     * 
     * @group Authentication
     * @authenticated
     */
    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout user and revoke token",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logged out successfully")
     * )
     */

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid token.',
            ], 401);
        }

        // Delete current token
        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Refresh token
     * 
     * @group Authentication
     * @authenticated
     */
    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Authentication"},
     *     summary="Refresh API token",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Token refreshed")
     * )
     */

    public function refresh(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $request->user()->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'user' => new UserResource($request->user()),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}
