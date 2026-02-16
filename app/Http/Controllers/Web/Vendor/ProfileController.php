<?php

namespace App\Http\Controllers\Web\Vendor;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Services\OTPService;


class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        // ENUM values (DB ke according)
        $businessTypes = [
            'proprietorship' => 'Proprietorship',
            'partnership'    => 'Partnership',
            'private_limited'=> 'Private Limited',
            'public_limited' => 'Public Limited',
            'llp'            => 'LLP',
            'other'          => 'Other',
        ];

        return view('vendor.profile.edit', [
            'user'          => $user,
            'vendor'        => $user->vendorProfile,
            'businessTypes' => $businessTypes,
        ]);
    }

    public function update(Request $request)
    {
        $section = $request->input('section');

        if ($section === 'delete') {
            return $this->deleteAccount($request);
        }

        $response = match ($section) {
            'personal' => $this->updatePersonal($request, Auth::user()),
            'business' => $this->updateBusiness($request, Auth::user()->vendorProfile),
            'pan'      => $this->updatePAN($request, Auth::user()->vendorProfile),
            'bank'     => $this->updateBank($request, Auth::user()->vendorProfile),
            'address'  => $this->updateAddress($request, Auth::user()->vendorProfile),
            'password' => $this->updatePassword($request, Auth::user()),
            'remove-avatar' => $this->removeAvatar(Auth::user()),
            default    => abort(400, 'Invalid profile section'),
        };

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return $response;
        }

        return back()->with('success', 'Profile updated successfully');
    }


    /* ======================
     | PERSONAL (USER)
     ====================== */
    protected function updatePersonal(Request $request, $user)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'nullable|required_without:phone|email|unique:users,email,' . $user->id,
            'phone'  => 'nullable|required_without:email|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        ]);
        if($request->filled('email')){
            if($request->email !== $user->email && is_null($user->email_verified_at)){
                return back()
                    ->withInput()
                    ->with('reopen_personal_modal', true)
                    ->withErrors([
                        'email' => 'Please verify your email first via OTP.'
                    ], 'personalUpdate');
            }
        }
        if($request->filled('phone')){
            $phone = preg_replace('/[^0-9]/','',$request->phone);

            if($phone !== $user->phone && is_null($user->phone_verified_at)){
                return back()
                    ->withInput()
                    ->with('reopen_personal_modal', true)
                    ->withErrors([
                        'phone' => 'Please verify your mobile number first via OTP.'
                    ], 'personalUpdate');
            }
        }
        if ($request->hasFile('avatar')) {
            try {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('private')->exists($user->avatar)) {
                    Storage::disk('private')->delete($user->avatar);
                }

                // Create bucket directory (00, 01, 02... based on user ID)
                $bucket = str_pad((int)($user->id / 100), 2, '0', STR_PAD_LEFT);
                $avatarPath = "media/users/avatars/{$bucket}/{$user->id}";
                
                // Store the file
                $fileName = time() . '.' . $request->file('avatar')->getClientOriginalExtension();
                $storedPath = Storage::disk('private')->putFileAs($avatarPath, $request->file('avatar'), $fileName);
                
                if ($storedPath) {
                    $data['avatar'] = $storedPath;
                }
            } catch (\Exception $e) {
                return back()->withErrors(['avatar' => 'Failed to upload avatar: ' . $e->getMessage()]);
            }
        }

        $user->update($data);
    }

    /* ======================
     | REMOVE AVATAR
     ====================== */
    protected function removeAvatar($user)
    {
        if ($user->avatar) {
            try {
                if (Storage::disk('private')->exists($user->avatar)) {
                    Storage::disk('private')->delete($user->avatar);
                }
            } catch (\Exception $e) {
                // Continue even if file deletion fails
            }
            
            $user->update(['avatar' => null]);
        }
    }

    /* ======================
     | BUSINESS (VENDOR)
     ====================== */
    protected function updateBusiness(Request $request, $vendor)
    {
        $user = Auth::user();

        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|string',
            'gstin'        => 'required|string|max:50',
            'pan'          => 'nullable|string|max:10',
            'pan_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        /* =========================
        | PAN NUMBER → USERS TABLE
        ========================= */
        if (!empty($data['pan'])) {
            $user->update([
                'pan' => $data['pan'],
            ]);
        }

        /* =========================
        | PAN FILE → VENDOR PROFILE
        ========================= */
        if ($request->hasFile('pan_file')) {

            if ($vendor->pan_card_document) {
                Storage::disk('private')->delete($vendor->pan_card_document);
            }

            $bucket = str_pad((int)($vendor->id / 100), 2, '0', STR_PAD_LEFT);
            $vendor->pan_card_document = $request->file('pan_file')
                ->store("media/vendors/documents/{$bucket}/{$vendor->id}", 'private');
        }

        /* =========================
        | BUSINESS DATA → VENDOR
        ========================= */
        $vendor->update([
            'company_name' => $data['company_name'],
            'company_type' => $data['company_type'],
            'gstin'        => $data['gstin'],
        ]);
    }


    /* ======================
     | PAN (VENDOR)
     ====================== */
    protected function updatePAN(Request $request, $vendor)
    {
        $request->validate([
            'pan'      => 'required|string|max:20',
            'pan_file' => 'required|image|max:2048',
        ]);

        if ($vendor->pan_card_document) {
            Storage::disk('private')->delete($vendor->pan_card_document);
        }

        $bucket = str_pad((int)($vendor->id / 100), 2, '0', STR_PAD_LEFT);
        $path = $request->file('pan_file')->store(
            "media/vendors/documents/{$bucket}/{$vendor->id}",
            'private'
        );

        $vendor->update([
            'pan'               => $request->pan,
            'pan_card_document' => $path,
        ]);
    }

    /* ======================
     | BANK (VENDOR)
     ====================== */
    protected function updateBank(Request $request, $vendor)
    {
        $data = $request->validate([
            'bank_name'           => 'required|string|max:100',
            'account_holder_name' => 'required|string|max:255',
            'account_number'      => 'required|string|max:30',
            'ifsc_code'           => 'required|string|max:20',
        ]);

        $vendor->update($data);
    }

    /* ======================
     | ADDRESS (VENDOR)
     ====================== */
    protected function updateAddress(Request $request, $vendor)
    {
        $user = Auth::user();

        $data = $request->validate([
            'registered_address' => 'required|string',
            'city'               => 'required|string',
            'state'              => 'required|string',
            'pincode'            => 'required|string',
            'country'            => 'required|string',
        ]);

        // Country is stored on users table
        $user->update([
            'country' => $data['country'],
        ]);

        // Rest of the address data is stored on vendor profile
        $vendor->update([
            'registered_address' => $data['registered_address'],
            'city'               => $data['city'],
            'state'              => $data['state'],
            'pincode'            => $data['pincode'],
        ]);
    }

    /* ======================
     | DELETE
     ====================== */
    protected function deleteAccount(Request $request)
    {
        $user = Auth::user();

        $emailOtp = $request->email_otp;
        $phoneOtp = $request->phone_otp;

        // ❌ Dono blank
        if (!$emailOtp && !$phoneOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter OTP from email or mobile'
            ], 422);
        }

        $emailVerified = false;
        $phoneVerified = false;

        // ✅ EMAIL OTP CHECK
        if ($emailOtp) {
            $cached = Cache::get('delete_email_otp_'.$user->id);
            if ($cached && $cached == $emailOtp) {
                $emailVerified = true;
                Cache::forget('delete_email_otp_'.$user->id);
            }
        }

        // ✅ PHONE OTP CHECK
        if ($phoneOtp) {
            $cached = Cache::get('delete_phone_otp_'.$user->id);
            if ($cached && $cached == $phoneOtp) {
                $phoneVerified = true;
                Cache::forget('delete_phone_otp_'.$user->id);
            }
        }

        // ❌ Dono fail
        if (!$emailVerified && !$phoneVerified) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 422);
        }

        // ✅ SOFT DELETE
        $user->delete();
        if ($user->vendorProfile) {
            $user->vendorProfile->delete();
        }

        Auth::logout();

        return response()->json([
            'success' => true,
            'title' => 'We’ll miss you 💔',
            'message' => 'Your account has been deleted successfully.'
        ]);
    }

    /* ======================
     | PASSWORD
     ====================== */
    protected function updatePassword(Request $request, $user)
    {
        $request->validate([
            'current_password' => 'required|string|min:4',
            'password'         => 'required|string|min:4|confirmed',
        ]);

        // Verify current password
        if (!password_verify($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // Update password and set status to active
        $user->update([
            'password' => bcrypt($request->password),
            'status' => 'active'
        ]);
        return back()->with('success', 'Password updated successfully');
    }

    public function viewAvatar($userId)
    {
        $authUser = Auth::user();
        
        // Only allow user to view their own avatar or admins to view any
        if ($authUser->id != $userId && !$authUser->hasRole('admin')) {
            abort(403);
        }

        $user = User::find($userId);

        if (!$user || !$user->avatar) {
            abort(404);
        }

        try {
            return response()->file(
                Storage::disk('private')->path($user->avatar),
                [
                    'Content-Type' => 'image/jpeg',
                    'Cache-Control' => 'private, max-age=3600',
                ]
            );
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function viewPan($vendorId)
    {
        $user = Auth::user();

        $vendor = $user->vendorProfile;

        // Extra safety
        if (!$vendor || $vendor->id != $vendorId) {
            abort(403);
        }

        if (!$vendor->pan_card_document) {
            abort(404);
        }

        return response()->file(
            Storage::disk('private')->path($vendor->pan_card_document),
            [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'private, max-age=0, no-cache',
            ]
        );
    }
    public function sendDeleteOtp(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'type' => 'required|in:email,phone'
            ]);

            $otp = rand(1000, 9999);

            if ($request->type === 'email') {

                Cache::put(
                    'delete_email_otp_'.$user->id,
                    $otp,
                    now()->addMinutes(2)
                );

                Mail::raw(
                    "Your OOHAPP Delete Account OTP is: {$otp}",
                    function ($m) use ($user) {
                        $m->to($user->email)
                        ->subject('OOHAPP Delete Account OTP');
                    }
                );
            }

            if ($request->type === 'phone') {

                Cache::put(
                    'delete_phone_otp_'.$user->id,
                    $otp,
                    now()->addMinutes(2)
                );

                // Same Twilio logic as Register
                $twilio = new Client(
                    env('TWILIO_SID'),
                    env('TWILIO_TOKEN')
                );

                $twilio->messages->create(
                    '+91'.$user->phone,
                    [
                        'from' => env('TWILIO_FROM'),
                        'body' => "Your OOHAPP Delete Account OTP is {$otp}. Valid for 2 minutes."
                    ]
                );
            }

            return response()->json([
                'success' => true
            ]);

        } catch (\Throwable $e) {

            Log::error('Delete OTP failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP'
            ], 500);
        }
    }
    public function sendProfileOtp(Request $request, OTPService $otpService)
    {
        $user = auth()->user();

        $type  = $request->input('type');
        $value = trim($request->input('value'));

        if (!$type || !$value) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 422);
        }

        /* ---------------- EMAIL ---------------- */

        if ($type === 'email') {

            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enter a valid email address'
                ], 422);
            }

            // already used?
            if (
                User::where('email', $value)
                    ->where('id', '!=', $user->id)
                    ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already registered'
                ], 422);
            }

            $purpose = 'profile_email';
        }

        /* ---------------- PHONE ---------------- */

        elseif ($type === 'phone') {

            $value = preg_replace('/[^0-9]/', '', $value);

            if (strlen($value) != 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enter valid 10 digit mobile number'
                ], 422);
            }

            // already used?
            if (
                User::where('phone', $value)
                    ->where('id', '!=', $user->id)
                    ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone already in use'
                ], 422);
            }

            $purpose = 'profile_phone';
        }

        else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type'
            ], 422);
        }

        /* ---------------- SEND OTP ---------------- */

        try {

            $otpService->generate(
                $user->id,
                $value,
                $purpose
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully'
            ]);

        } catch (\Throwable $e) {

            Log::error('PROFILE OTP SEND FAILED', [
                'user_id' => $user->id,
                'type'    => $type,
                'value'   => $value,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP'
            ], 500);
        }
    }
    public function verifyProfileOtp(Request $request, OTPService $otpService)
    {
        $user = auth()->user();

        $type  = $request->input('type');
        $value = trim($request->input('value'));
        $otp   = trim($request->input('otp'));

        if (!$type || !$value || !$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 422);
        }

        // purpose mapping
        if ($type === 'email') {
            $purpose = 'profile_email';
        } elseif ($type === 'phone') {
            $purpose = 'profile_phone';
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification type'
            ], 422);
        }

        try {

            $verified = $otpService->verify(
                $user->id,
                $value,
                $otp,
                $purpose
            );

            if (!$verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect or expired OTP'
                ], 422);
            }

            // SAVE VERIFIED VALUE
            if ($type === 'email') {
                $user->email = $value;
                $user->email_verified_at = now();
            }

            if ($type === 'phone') {
                $user->phone = preg_replace('/[^0-9]/', '', $value);
                $user->phone_verified_at = now();
            }

            $user->save();
            if (!$user->email_verified_at || !$user->phone_verified_at) {
                session()->flash('reopen_personal_modal', true);
            }

            auth()->setUser($user);

            return response()->json([
                'success' => true,
                'message' => ucfirst($type).' verified successfully'
            ], 200, [], JSON_UNESCAPED_UNICODE);


        } catch (\Throwable $e) {

            \Log::error('OTP VERIFY FAILED', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed. Try again.'
            ], 500);
        }
    }
}