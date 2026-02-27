<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Services\OTPService;

class MobileForgotPasswordController extends Controller
{
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    // 1️⃣ show page
    public function showForm(Request $request)
    {
        return view('auth.mobile-forgot-password', [
            'phone' => $request->phone
        ]);
    }

    // 2️⃣ SEND OTP
    public function sendOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|exists:users,phone'
            ]);

            $user = User::where('phone', $request->phone)->first();

            // purpose = password_reset
            $this->otpService->generate(
                $user->id,
                $user->phone,
                'password_reset'
            );

            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // 3️⃣ VERIFY OTP
    public function verifyOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone' => 'required',
                'otp' => 'required|digits:4'
            ]);

            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json([
                    'status'=>false,
                    'message'=>'User not found'
                ], 404);
            }

            $verified = $this->otpService->verify(
                $user->id,
                $user->phone,
                $request->otp,
                'password_reset'
            );

            if (!$verified) {
                return response()->json([
                    'status'=>false,
                    'message'=>'Invalid or expired OTP'
                ], 422);
            }

            // session me mark kar do verified
            Session::put('password_reset_user', $user->id);

            return response()->json([
                'status'=>true
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    // 4️⃣ RESET PASSWORD
    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'password'=>'required|min:4|confirmed'
            ]);

            $userId = Session::get('password_reset_user');

            if (!$userId) {
                return response()->json([
                    'status'=>false,
                    'message'=>'OTP verification required'
                ], 403);
            }

            $user = User::find($userId);

            $user->password = Hash::make($request->password);
            $user->save();

            // session remove
            Session::forget('password_reset_user');

            return response()->json([
                'status'=>true,
                'message'=>'Password changed successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
}
