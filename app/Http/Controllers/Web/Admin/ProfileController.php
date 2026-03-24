<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        $profile = DB::table('user_profiles')
            ->where('user_id', $user->id)
            ->first();

        $totalHoardings = DB::table('hoardings')
            ->where('vendor_id', $user->id)
            ->whereNull('deleted_at')
            ->count();

        $totalActiveOrders =
            DB::table('bookings')
                ->where('vendor_id', $user->id)
                ->where('status', 'confirmed')
                ->count()
            +
            DB::table('pos_bookings')
                ->where('vendor_id', $user->id)
                ->where('status', 'confirmed')
                ->count();

        $totalEarnings = DB::table('pos_bookings')
            ->where('vendor_id', $user->id)
            ->where('status', 'confirmed')
            ->sum('total_amount');

        $fields = [
            $user->name,
            $user->email,
            $user->avatar,
            $profile?->company_name,
            $profile?->business_type,
            $profile?->gstin,
            $profile?->pan_document,
            $profile?->bank_name,
            $profile?->account_holder_name,
            $profile?->account_number,
            $profile?->ifsc_code,
            $profile?->country,
            $profile?->state,
            $profile?->city,
            $profile?->pincode,
            $profile?->address,
        ];

        $filledFields = collect($fields)->filter(fn ($value) => !empty($value))->count();
        $totalFields = count($fields);
        $profileCompletion = $totalFields > 0 ? round(($filledFields / $totalFields) * 100) : 0;

        return view('admin.profile.edit', [
            'user'              => $user,
            'profile'           => $profile,
            'totalActiveOrders' => $totalActiveOrders,
            'totalHoardings'    => $totalHoardings,
            'totalEarnings'     => $totalEarnings,
            'profileCompletion' => $profileCompletion,
        ]);
    }

    public function updatePersonal(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);

        DB::table('users')
            ->where('id', auth()->id())
            ->update([
                'name'       => $request->name,
                'email'      => $request->email,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Personal info updated successfully.');
    }

    public function updateBusiness(Request $request)
    {
        $request->validate([
            'company_name'  => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'gstin'         => 'nullable|string|max:15',
            'pan_number'    => 'nullable|string|max:20',
            'pan_document'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $profile = DB::table('user_profiles')
            ->where('user_id', auth()->id())
            ->first();

        $data = [
            'company_name'  => $request->company_name,
            'business_type' => $request->business_type,
            'gstin'         => $request->gstin,
            'pan_number'    => $request->pan_number,
            'updated_at'    => now(),
        ];

        if ($request->hasFile('pan_document')) {
                if ($profile && !empty($profile->pan_document) && Storage::disk('private')->exists($profile->pan_document)) {
                    Storage::disk('private')->delete($profile->pan_document);
                }

                $bucket = str_pad((int) (auth()->id() / 100), 2, '0', STR_PAD_LEFT);
                $directory = "media/admin/documents/{$bucket}/" . auth()->id();

                $extension = $request->file('pan_document')->getClientOriginalExtension();
                $fileName = 'pan.' . strtolower($extension);

                $path = $request->file('pan_document')->storeAs(
                    $directory,
                    $fileName,
                    'private'
                );

                $data['pan_document'] = $path;
            }

        if ($profile) {
            DB::table('user_profiles')
                ->where('user_id', auth()->id())
                ->update($data);
        } else {
            DB::table('user_profiles')->insert(array_merge($data, [
                'user_id'    => auth()->id(),
                'created_at' => now(),
            ]));
        }

        return back()->with('success', 'Business details updated successfully.');
    }

    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name'           => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_number'      => 'nullable|string|max:50',
            'ifsc_code'           => 'nullable|string|max:20',
        ]);

        $profile = DB::table('user_profiles')
            ->where('user_id', auth()->id())
            ->first();

        $bankData = [
            'bank_name'           => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number'      => $request->account_number,
            'ifsc_code'           => $request->ifsc_code,
            'updated_at'          => now(),
        ];

        if ($profile) {
            DB::table('user_profiles')
                ->where('user_id', auth()->id())
                ->update($bankData);
        } else {
            DB::table('user_profiles')->insert(array_merge($bankData, [
                'user_id'    => auth()->id(),
                'created_at' => now(),
            ]));
        }

        return back()->with('success', 'Bank details updated successfully.');
    }

    public function updateAddress(Request $request)
    {
        $request->validate([
            'country'          => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'city'             => 'nullable|string|max:100',
            'pincode'          => 'nullable|digits:6',
            'business_address' => 'nullable|string|max:500',
        ]);

        $profile = DB::table('user_profiles')
            ->where('user_id', auth()->id())
            ->first();

        $addressData = [
            'country'    => $request->country,
            'state'      => $request->state,
            'city'       => $request->city,
            'pincode'    => $request->pincode,
            'address'    => $request->business_address,
            'updated_at' => now(),
        ];

        if ($profile) {
            DB::table('user_profiles')
                ->where('user_id', auth()->id())
                ->update($addressData);
        } else {
            DB::table('user_profiles')->insert(array_merge($addressData, [
                'user_id'    => auth()->id(),
                'created_at' => now(),
            ]));
        }

        return back()->with('success', 'Address updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = DB::table('users')
            ->where('id', auth()->id())
            ->first();

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors([
                'old_password' => 'Old password is incorrect.'
            ]);
        }

        DB::table('users')
            ->where('id', auth()->id())
            ->update([
                'password'   => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function viewPan()
    {
        $profile = DB::table('user_profiles')
            ->where('user_id', auth()->id())
            ->first();

        if (!$profile || !$profile->pan_document) {
            abort(404);
        }

        if (!Storage::disk('private')->exists($profile->pan_document)) {
            abort(404);
        }

        $path = Storage::disk('private')->path($profile->pan_document);
        $mime = Storage::disk('private')->mimeType($profile->pan_document) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=0, no-cache',
        ]);
    }
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120',
        ]);

        $user = DB::table('users')
            ->where('id', auth()->id())
            ->first();

        if (!$user) {
            abort(404);
        }

        $data = [
            'updated_at' => now(),
        ];

        if ($request->hasFile('avatar')) {
            if (!empty($user->avatar) && Storage::disk('private')->exists($user->avatar)) {
                Storage::disk('private')->delete($user->avatar);
            }

            $bucket = str_pad((int) (auth()->id() / 100), 2, '0', STR_PAD_LEFT);
            $directory = "media/admin/avatars/{$bucket}/" . auth()->id();

            $extension = $request->file('avatar')->getClientOriginalExtension();
            $fileName = 'avatar.' . strtolower($extension);

            $path = $request->file('avatar')->storeAs(
                $directory,
                $fileName,
                'private'
            );

            $data['avatar'] = $path;
        }

        DB::table('users')
            ->where('id', auth()->id())
            ->update($data);

        return back()->with('success', 'Avatar updated successfully.');
    }

    public function viewAvatar()
    {
        $user = DB::table('users')
            ->where('id', auth()->id())
            ->first();

        if (!$user || !$user->avatar) {
            abort(404);
        }

        $avatar = str_replace('\\', '/', $user->avatar);

        if (!Storage::disk('private')->exists($avatar)) {
            abort(404);
        }

        $fullPath = Storage::disk('private')->path($avatar);

        return response(
            file_get_contents($fullPath),
            200,
            [
                'Content-Type' => mime_content_type($fullPath),
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }
    public function removeAvatar()
    {
        $user = DB::table('users')->where('id', auth()->id())->first();

        if ($user && !empty($user->avatar) && Storage::disk('private')->exists($user->avatar)) {
            Storage::disk('private')->delete($user->avatar);
        }

        DB::table('users')
            ->where('id', auth()->id())
            ->update([
                'avatar' => null,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Avatar removed successfully.');
    }
}