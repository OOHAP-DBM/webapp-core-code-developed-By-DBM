<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\ProfileService;

class ProfileController extends Controller
{
/**
 * @OA\Get(
 *     path="/profile/vendor/show",
 *     tags={"Vendor Profile"},
 *     summary="Get vendor profile",
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="user", type="object"),
 *             @OA\Property(property="vendor", type="object")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */
public function show(ProfileService $profileService)
{
    $user = Auth::user();
    $vendor = $user->vendorProfile;

    // Convert document paths to URLs
    if ($vendor) {
        $vendor->pan_card_document = $vendor->pan_card_document 
            ? asset('storage/' . $vendor->pan_card_document) 
            : null;

        $vendor->aadhaar_card_document = $vendor->aadhaar_card_document 
            ? asset('storage/' . $vendor->aadhaar_card_document) 
            : null;
    }

    return response()->json([
        'user' => $profileService->response($user),
        'vendor' => $vendor,
    ]);
}


/**
 * @OA\Post(
 *     path="/profile/vendor/update",
 *     tags={"Vendor Profile"},
 *     summary="Update vendor profile (only fields present in request are updated)",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="phone", type="string"),
 *             @OA\Property(property="avatar", type="string", format="binary"),
 *             @OA\Property(property="company_name", type="string"),
 *             @OA\Property(property="company_type", type="string"),
 *             @OA\Property(property="gstin", type="string"),
 *             @OA\Property(property="pan", type="string"),
 *             @OA\Property(property="pan_file", type="string", format="binary"),
 *             @OA\Property(property="bank_name", type="string"),
 *             @OA\Property(property="account_holder_name", type="string"),
 *             @OA\Property(property="account_number", type="string"),
 *             @OA\Property(property="ifsc_code", type="string"),
 *             @OA\Property(property="registered_address", type="string"),
 *             @OA\Property(property="city", type="string"),
 *             @OA\Property(property="state", type="string"),
 *             @OA\Property(property="pincode", type="string"),
 *             @OA\Property(property="country", type="string"),
 *             @OA\Property(property="password", type="string"),
 *             @OA\Property(property="otp", type="string"),
 *             @OA\Property(property="section", type="string", description="Section to update: personal, business, pan, bank, address, password, remove-avatar, delete")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Profile updated successfully"),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function update(Request $request, ProfileService $profileService)
{
    $user = Auth::user();
    $vendor = $user->vendorProfile;
    $section = $request->input('section');

    switch ($section) {
        case 'personal':
            $data = $request->only(['name', 'email', 'phone', 'avatar']);
            // Email/phone change: require OTP verification
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $otp = $request->input('otp');
                if (!$otp) {
                    return response()->json(['message' => 'OTP is required to update email.'], 422);
                }
                $profileService->verify($user, 'email', $data['email'], $otp);
            }
            if (isset($data['phone']) && $data['phone'] !== $user->phone) {
                $otp = $request->input('otp');
                if (!$otp) {
                    return response()->json(['message' => 'OTP is required to update phone.'], 422);
                }
                $profileService->verify($user, 'phone', $data['phone'], $otp);
            }
            if ($request->hasFile('avatar')) {
                $data['avatar'] = $profileService->updateAvatar($user, $request->file('avatar'));
            } else {
                unset($data['avatar']);
            }
            $user->fill($data);
            $user->save();
            break;
        case 'business':
            $data = $request->only(['company_name', 'company_type', 'gstin', 'pan', 'pan_file']);
            $vendor->fill(array_filter($data));
            if ($request->hasFile('pan_file')) {
                $bucket = str_pad((int)($vendor->id / 100), 2, '0', STR_PAD_LEFT);
                $vendor->pan_card_document = $request->file('pan_file')->store("media/vendors/documents/{$bucket}/{$vendor->id}", 'private');
            }
            $vendor->save();
            break;
        case 'pan':
            $data = $request->only(['pan', 'pan_file']);
            if ($request->hasFile('pan_file')) {
                $bucket = str_pad((int)($vendor->id / 100), 2, '0', STR_PAD_LEFT);
                $vendor->pan_card_document = $request->file('pan_file')->store("media/vendors/documents/{$bucket}/{$vendor->id}", 'private');
            }
            $vendor->fill(array_filter($data));
            $vendor->save();
            break;
        case 'bank':
            $data = $request->only(['bank_name', 'account_holder_name', 'account_number', 'ifsc_code']);
            $vendor->fill(array_filter($data));
            $vendor->save();
            break;
        case 'address':
            $data = $request->only(['registered_address', 'city', 'state', 'pincode', 'country']);
            $vendor->fill(array_filter($data));
            $vendor->save();
            if (isset($data['country'])) {
                $user->country = $data['country'];
                $user->save();
            }
            break;
        case 'password':
            // Require OTP for password reset
            $otp = $request->input('otp');
            if (!$otp) {
                return response()->json(['message' => 'OTP is required to reset password.'], 422);
            }
            $profileService->verify($user, 'phone', $user->phone, $otp);
            $profileService->changePassword($user, $request->input('current_password'), $request->input('password'));
            break;
        case 'remove-avatar':
            $user->avatar = null;
            $user->save();
            break;
        case 'delete':
            $user->delete();
            if ($vendor) $vendor->delete();
            Auth::logout();
            return response()->json(['message' => 'Your account has been deleted successfully.']);
        default:
            abort(400, 'Invalid profile section');
    }

    return response()->json(['message' => 'Profile updated successfully']);
}

    protected function updatePersonal(Request $request, User $user)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'nullable|required_without:phone|email|unique:users,email,' . $user->id,
            'phone'  => 'nullable|required_without:email|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('private')->exists($user->avatar)) {
                Storage::disk('private')->delete($user->avatar);
            }

            $bucket = str_pad((int)($user->id / 100), 2, '0', STR_PAD_LEFT);
            $avatarPath = "media/users/avatars/{$bucket}/{$user->id}";
            $fileName = time() . '.' . $request->file('avatar')->getClientOriginalExtension();
            $storedPath = Storage::disk('private')->putFileAs($avatarPath, $request->file('avatar'), $fileName);

            if ($storedPath) {
                $data['avatar'] = $storedPath;
            }
        }

        $user->update($data);
    }

    protected function removeAvatar(User $user)
    {
        if ($user->avatar && Storage::disk('private')->exists($user->avatar)) {
            Storage::disk('private')->delete($user->avatar);
        }
        $user->update(['avatar' => null]);
    }

    protected function updateBusiness(Request $request, $vendor)
    {
        $user = Auth::user();
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|string',
            'gstin'        => 'required|string|max:50',
            'pan'          => 'nullable|string|max:10',
            'pan_file'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if (!empty($data['pan'])) {
            $user->update(['pan' => $data['pan']]);
        }

        if ($request->hasFile('pan_file')) {
            if ($vendor->pan_card_document) {
                Storage::disk('private')->delete($vendor->pan_card_document);
            }

            $bucket = str_pad((int)($vendor->id / 100), 2, '0', STR_PAD_LEFT);
            $vendor->pan_card_document = $request->file('pan_file')
                ->store("media/vendors/documents/{$bucket}/{$vendor->id}", 'private');
        }

        $vendor->update([
            'company_name' => $data['company_name'],
            'company_type' => $data['company_type'],
            'gstin'        => $data['gstin'],
        ]);
    }

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

        $user->update(['country' => $data['country']]);
        $vendor->update([
            'registered_address' => $data['registered_address'],
            'city'               => $data['city'],
            'state'              => $data['state'],
            'pincode'            => $data['pincode'],
        ]);
    }

    protected function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'current_password' => 'required|string|min:4',
            'password'         => 'required|string|min:4|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 422);
        }

        $user->update([
            'password' => bcrypt($request->password),
            'status'   => 'active'
        ]);
    }

    protected function deleteAccount(User $user)
    {
        $user->delete();
        if ($user->vendorProfile) {
            $user->vendorProfile->delete();
        }

        Auth::logout();

        return response()->json([
            'message' => 'Your account has been deleted successfully.'
        ]);
    }
}
