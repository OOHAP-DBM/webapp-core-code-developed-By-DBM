<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $activeGroup = $request->get('group', Setting::GROUP_GENERAL);
        $groups = Setting::getAvailableGroups();

        // Validate the group exists
        if (!isset($groups[$activeGroup])) {
            $activeGroup = Setting::GROUP_GENERAL;
        }

        // Get settings for the active group
        $settings = Setting::global()->group($activeGroup)->orderBy('key')->get();

        return view('admin.settings.index', compact('settings', 'activeGroup', 'groups'));
    }

    /**
     * Update settings for a group.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $group = $request->input('group');
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->whereNull('tenant_id')->first();

            if ($setting) {
                // Validate based on type
                $this->validateSettingValue($value, $setting->type);

                $setting->setTypedValue($value);
                $setting->save();
            }
        }

        return redirect()
            ->route('admin.settings.index', ['group' => $group])
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Show general settings.
     *
     * @return \Illuminate\View\View
     */
    public function general()
    {
        $settings = Setting::global()->group(Setting::GROUP_GENERAL)->get();
        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Show booking rules settings.
     *
     * @return \Illuminate\View\View
     */
    public function booking()
    {
        $settings = Setting::global()->group(Setting::GROUP_BOOKING)->get();
        return view('admin.settings.booking', compact('settings'));
    }

    /**
     * Show commission settings.
     *
     * @return \Illuminate\View\View
     */
    public function commission()
    {
        $settings = Setting::global()->group(Setting::GROUP_COMMISSION)->get();
        return view('admin.settings.commission', compact('settings'));
    }

    /**
     * Show notification template settings.
     *
     * @return \Illuminate\View\View
     */
    public function notification()
    {
        $settings = Setting::global()->group(Setting::GROUP_NOTIFICATION)->get();
        return view('admin.settings.notification', compact('settings'));
    }

    /**
     * Show KYC rules settings.
     *
     * @return \Illuminate\View\View
     */
    public function kyc()
    {
        $settings = Setting::global()->group(Setting::GROUP_KYC)->get();
        return view('admin.settings.kyc', compact('settings'));
    }

    /**
     * Show DOOH API configuration.
     *
     * @return \Illuminate\View\View
     */
    public function dooh()
    {
        $settings = Setting::global()->group(Setting::GROUP_DOOH)->get();
        return view('admin.settings.dooh', compact('settings'));
    }

    /**
     * Show automation rules settings.
     *
     * @return \Illuminate\View\View
     */
    public function automation()
    {
        $settings = Setting::global()->group(Setting::GROUP_AUTOMATION)->get();
        return view('admin.settings.automation', compact('settings'));
    }

    /**
     * Show payment settings.
     *
     * @return \Illuminate\View\View
     */
    public function payment()
    {
        $settings = Setting::global()->group(Setting::GROUP_PAYMENT)->get();
        return view('admin.settings.payment', compact('settings'));
    }

    /**
     * Show cancellation rules settings.
     *
     * @return \Illuminate\View\View
     */
    public function cancellation()
    {
        $settings = Setting::global()->group(Setting::GROUP_CANCELLATION)->get();
        return view('admin.settings.cancellation', compact('settings'));
    }

    /**
     * Show refund logic settings.
     *
     * @return \Illuminate\View\View
     */
    public function refund()
    {
        $settings = Setting::global()->group(Setting::GROUP_REFUND)->get();
        return view('admin.settings.refund', compact('settings'));
    }

    /**
     * Clear all settings cache.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        Setting::clearCache();

        return redirect()
            ->back()
            ->with('success', 'Settings cache cleared successfully!');
    }

    /**
     * Validate setting value based on type.
     *
     * @param mixed $value
     * @param string $type
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateSettingValue($value, string $type)
    {
        $rules = match ($type) {
            Setting::TYPE_INTEGER => ['integer'],
            Setting::TYPE_FLOAT => ['numeric'],
            Setting::TYPE_BOOLEAN => ['boolean'],
            Setting::TYPE_JSON, Setting::TYPE_ARRAY => ['json'],
            default => ['string'],
        };

        $validator = Validator::make(
            ['value' => $value],
            ['value' => $rules]
        );

        $validator->validate();
    }
}
