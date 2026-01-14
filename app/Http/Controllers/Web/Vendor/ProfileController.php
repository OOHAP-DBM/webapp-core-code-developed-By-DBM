<?php

namespace App\Http\Controllers\Web\Vendor;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;


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
        $user   = Auth::user();
        $vendor = $user->vendorProfile;

        $section = $request->input('section');

        match ($section) {

            'personal' => $this->updatePersonal($request, $user),

            'business' => $this->updateBusiness($request, $vendor),

            'pan'      => $this->updatePAN($request, $vendor),

            'bank'     => $this->updateBank($request, $vendor),

            'address'  => $this->updateAddress($request, $vendor),

            'password' => $this->updatePassword($request, $user),

            'remove-avatar' => $this->removeAvatar($user),

            'delete'   => $this->deleteAccount($user),

            default    => abort(400, 'Invalid profile section'),
        };

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
    protected function deleteAccount($user)
    {
        // Soft delete the user account
        $user->delete();
        
        // Also soft delete the vendor profile if using SoftDeletes
        if ($user->vendorProfile) {
            $user->vendorProfile->delete();
        }
        
        Auth::logout();
        
        return back()->with('success', 'Your account has been deleted successfully.');
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
}