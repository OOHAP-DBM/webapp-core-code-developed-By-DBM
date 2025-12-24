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

class AuthController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected OTPService $otpService
    ) {}

    // public function register(Request $request, UserService $userService)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:100',
    //         'email' => 'required|email|unique:users,email',
    //         'phone' => 'required|string|unique:users,phone',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     return DB::transaction(function () use ($validated, $userService) {
    //         $user = $userService->createCustomer($validated);
    //         $user->status = 'active';
    //         $user->active_role = 'customer';
    //         $user->save();
    //         $token = $user->createToken('customer-api', ['role:customer'])->plainTextToken;
    //         return response()->json(['token' => $token], 201);
    //     });
    // }

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

    public function register(RegisterRequest $request): JsonResponse
    {
        $identifier = $request->input('email') ?? $request->input('phone');

        // 1. Find the user who was just verified
        $user = $this->userService->findUserByIdentifier($identifier);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // 2. IMPORTANT: Check if they are actually verified
        // This prevents people from registering without an OTP
        if (!$user->email_verified_at && !$user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Your identifier is not verified. Please verify OTP first.'
            ], 403);
        }

        // 3. Update the record with Name and Password
        $user->update([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        // 4. Assign Role
        $role = $request->input('role', 'customer');
        $user->update(['active_role' => $role]);
        // If using Spatie Permissions: $user->assignRole($role);

        // 5. Issue the Token (Now they are officially logged in)
        $token = $user->createToken('auth_token', ['role:' . $role])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration complete!',
            'data' => [
                'user' => new UserResource($user->fresh()),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login with email/phone and password
     * 
     * @group Authentication
     * @bodyParam identifier string required Email or phone number
     * @bodyParam password string required Password
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

    // public function verifyRegisterOTP(VerifyOTPRequest $request): JsonResponse
    // {
    //     // 1. Verify the OTP via the service
    //     $result = $this->otpService->verifyRegisterOTP(
    //         $request->input('identifier'),
    //         $request->input('otp')
    //     );

    //     if (!$result['success']) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $result['message'],
    //         ], 400);
    //     }

    //     $user = $result['user'];

    //     // 2. Scenario A: New User (Registration Step 2)
    //     // If name is null, they haven't completed registration. 
    //     // Do NOT issue a login token yet.
    //     if (!$user->name) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'OTP verified successfully. Please complete your profile.',
    //             'data' => [
    //                 'is_new_user' => true,
    //                 'identifier' => $request->input('identifier'),
    //                 // No token issued here to force profile completion
    //             ],
    //         ]);
    //     }

    //     // 3. Scenario B: Existing User (OTP Login)
    //     // Issue token and log them in immediately.
    //     $activeRole = $user->getActiveRole();
    //     $token = $user->createToken('auth_token', ['role:' . $activeRole])->plainTextToken;

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Login successful',
    //         'data' => [
    //             'is_new_user' => false,
    //             'user' => new UserResource($user),
    //             'token' => $token,
    //             'token_type' => 'Bearer',
    //             'active_role' => $activeRole,
    //         ],
    //     ]);
    // }
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
     * Get authenticated user
     * 
     * @group Authentication
     * @authenticated
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
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

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
