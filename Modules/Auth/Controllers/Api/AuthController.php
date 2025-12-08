<?php

namespace Modules\Auth\Controllers\Api;

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

class AuthController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected OTPService $otpService
    ) {}

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
    public function register(RegisterRequest $request): JsonResponse
    {
        $role = $request->input('role', 'customer');

        $user = $this->userService->createUser(
            $request->validated(),
            $role
        );

        // Create API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
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

        // Create API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
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

