<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HoardingSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.hoarding_auto_approval');
    }

    public function update(Request $request)
    {
        $value = $request->input('auto_hoarding_approval') == '1' ? 'true' : 'false';
        // Update .env file
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $env = file_get_contents($envPath);
            $env = preg_replace('/^Auto_Hoarding_Approval=.*/m', 'Auto_Hoarding_Approval=' . $value, $env);
            file_put_contents($envPath, $env);
        }
        // Optionally clear config cache
        Artisan::call('config:clear');
        return redirect()->back()->with('success', 'Auto approval setting updated!');
    }
}