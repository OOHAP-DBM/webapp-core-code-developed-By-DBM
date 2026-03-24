<?php
// app/Http/Controllers/Admin/RazorpaySettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewaySetting;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RazorpaySettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|super_admin']);
    }

    public function index()
    {
        $settings = PaymentGatewaySetting::getRazorpay();
        return view('admin.settings.razorpay-config.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'key_id'         => 'required|string|starts_with:rzp_',
            'key_secret'     => 'nullable|string|min:10',
            'webhook_secret' => 'nullable|string',
            'mode'           => 'required|in:test,live',
            'currency'       => 'required|string|size:3',
            'is_active'      => 'boolean',
            'business_name'  => 'nullable|string|max:100',
            'theme_color'    => 'nullable|string|max:7',
        ]);

        // ✅ Key must match mode
        if ($validated['mode'] === 'live' && str_starts_with($validated['key_id'], 'rzp_test_')) {
            return back()->withErrors([
                'key_id' => 'You are using a Test key but selected Live mode.'
            ])->withInput();
        }

        if ($validated['mode'] === 'test' && str_starts_with($validated['key_id'], 'rzp_live_')) {
            return back()->withErrors([
                'key_id' => 'You are using a Live key but selected Test mode.'
            ])->withInput();
        }

        try {
            $settings = PaymentGatewaySetting::firstOrNew(['gateway' => 'razorpay']);

            $settings->key_id        = $validated['key_id'];
            $settings->mode          = $validated['mode'];
            $settings->currency      = strtoupper($validated['currency']);
            $settings->is_active     = $request->boolean('is_active');
            $settings->business_name = $validated['business_name'] ?? null;
            $settings->theme_color   = $validated['theme_color'] ?? '#009A5C';
            $settings->updated_by    = Auth::id();

            // ✅ Only update if new value provided
            if (!empty($validated['key_secret'])) {
                $settings->key_secret = $validated['key_secret'];
            }

            if (!empty($validated['webhook_secret'])) {
                $settings->webhook_secret = $validated['webhook_secret'];
            }

            $settings->save();

            Log::info('Razorpay settings updated', [
                'updated_by' => Auth::id(),
                'mode'       => $validated['mode'],
                'is_active'  => $settings->is_active,
            ]);

            return back()->with('success', 'Razorpay settings saved successfully.');
        } catch (\Exception $e) {
            Log::error('Razorpay settings save failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }

    public function testCredentials(Request $request)
    {
        $request->validate([
            'key_id'     => 'required|string|starts_with:rzp_',
            'key_secret' => 'required|string|min:10',
        ]);

        $service = new RazorpayService();
        $result  = $service->testCredentials(
            $request->key_id,
            $request->key_secret
        );

        return response()->json($result);
    }

    public function toggleActive()
    {
        $settings = PaymentGatewaySetting::getRazorpay();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Razorpay not configured yet.'
            ], 404);
        }

        if (!$settings->isConfigured() && !$settings->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot activate — Key ID and Secret are required.'
            ], 422);
        }

        $settings->update([
            'is_active'  => !$settings->is_active,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success'   => true,
            'is_active' => $settings->is_active,
            'message'   => $settings->is_active ? 'Razorpay activated.' : 'Razorpay deactivated.',
        ]);
    }
}
