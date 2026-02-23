<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class HoardingSettingsController extends Controller
{
    public function edit()
    {
        $autoApproval = Setting::get('auto_hoarding_approval', false);
        return view('admin.settings.hoarding_auto_approval', compact('autoApproval'));
    }

    public function update(Request $request)
    {
        $value = $request->input('auto_hoarding_approval') == '1';
        Setting::set(
            'auto_hoarding_approval',
            $value,
            Setting::TYPE_BOOLEAN,
            Setting::GROUP_AUTOMATION,
            'Auto approval for hoarding requests'
        );
        return redirect()->back()->with('success', 'Auto approval setting updated!');
    }
}