<?php

namespace App\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Show the admin profile edit page.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        // Get all columns for this user from users table
        $userDetails = \DB::table('users')->where('id', $user->id)->first();
        return view('admin.profile.edit', [
            'user' => $userDetails
        ]);
    }
}
