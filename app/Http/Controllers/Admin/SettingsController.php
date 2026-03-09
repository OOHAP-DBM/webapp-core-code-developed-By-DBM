<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    private const SMS_GATEWAYS = ['twilio', 'msg91', 'clickatell'];

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

        if ($activeGroup === Setting::GROUP_SMS) {
            $this->ensureSmsSettingsExist();
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

        if ($group === Setting::GROUP_SMS) {
            $this->ensureSmsSettingsExist();
            $settings = $this->normalizeSmsSettings($settings);
            $this->validateSmsSettings($settings);
        }

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
        $normalizedType = strtolower(trim($type));
        $normalizedType = match ($normalizedType) {
            'int' => Setting::TYPE_INTEGER,
            'double', 'decimal' => Setting::TYPE_FLOAT,
            'bool' => Setting::TYPE_BOOLEAN,
            default => $normalizedType,
        };

        $normalizedValue = $value;

        if ($normalizedType === Setting::TYPE_STRING && is_scalar($normalizedValue)) {
            $normalizedValue = (string) $normalizedValue;
        }

        if (
            in_array($normalizedType, [Setting::TYPE_JSON, Setting::TYPE_ARRAY], true)
            && (is_array($normalizedValue) || is_object($normalizedValue))
        ) {
            $normalizedValue = json_encode($normalizedValue);
        }

        if ($normalizedType === Setting::TYPE_STRING) {
            Validator::make(
                ['value' => $normalizedValue],
                [
                    'value' => [
                        'nullable',
                        function ($attribute, $value, $fail) {
                            if (!is_null($value) && !is_scalar($value)) {
                                $fail('The ' . $attribute . ' field must be a string.');
                            }
                        },
                    ],
                ]
            )->validate();

            return;
        }

        $rules = match ($normalizedType) {
            Setting::TYPE_INTEGER => ['integer'],
            Setting::TYPE_FLOAT => ['numeric'],
            Setting::TYPE_BOOLEAN => ['boolean'],
            Setting::TYPE_JSON, Setting::TYPE_ARRAY => ['json'],
            default => ['nullable'],
        };

        $validator = Validator::make(
            ['value' => $normalizedValue],
            ['value' => $rules]
        );

        $validator->validate();
    }

    /**
     * Ensure required SMS settings exist for the SMS gateway admin screen.
     */
    private function ensureSmsSettingsExist(): void
    {
        $defaults = [
            'sms_service_enabled' => [
                'value' => true,
                'type' => Setting::TYPE_BOOLEAN,
                'description' => 'Enable SMS delivery via configured SMS gateway',
            ],
            'sms_active_gateway' => [
                'value' => 'twilio',
                'type' => Setting::TYPE_STRING,
                'description' => 'Active SMS gateway (twilio, msg91, clickatell)',
            ],

            'sms_twilio_active' => [
                'value' => true,
                'type' => Setting::TYPE_BOOLEAN,
                'description' => 'Twilio active flag (managed automatically)',
            ],
            'sms_twilio_sid' => [
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'description' => 'Twilio account SID',
            ],
            'sms_twilio_auth_token' => [
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'description' => 'Twilio auth token',
            ],
            'sms_twilio_from' => [
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'description' => 'Twilio sender number in E.164 format',
            ],
            'sms_twilio_alphanumeric_sender_id' => [
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'description' => 'Twilio alphanumeric sender ID',
            ],

            'sms_msg91_active' => [
                'value' => false,
                'type' => Setting::TYPE_BOOLEAN,
                'description' => 'MSG91 active flag (managed automatically)',
            ],
            'sms_msg91_auth_key' => [
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'description' => 'MSG91 auth key',
            ],
            'sms_msg91_sender_id' => [
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'description' => 'MSG91 sender ID',
            ],
            'sms_msg91_route' => [
                'value' => '4',
                'type' => Setting::TYPE_STRING,
                'description' => 'MSG91 route code',
            ],
            'sms_msg91_country' => [
                'value' => '91',
                'type' => Setting::TYPE_STRING,
                'description' => 'MSG91 country code',
            ],
            'sms_msg91_base_url' => [
                'value' => 'https://api.msg91.com/api/v2/sendsms',
                'type' => Setting::TYPE_STRING,
                'description' => 'MSG91 API base URL',
            ],

            'sms_clickatell_active' => [
                'value' => false,
                'type' => Setting::TYPE_BOOLEAN,
                'description' => 'Clickatell active flag (managed automatically)',
            ],
            'sms_clickatell_api_key' => [
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'description' => 'Clickatell API key',
            ],
            'sms_clickatell_from' => [
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'description' => 'Optional Clickatell sender ID',
            ],
            'sms_clickatell_base_url' => [
                'value' => 'https://platform.clickatell.com/messages/http/send',
                'type' => Setting::TYPE_STRING,
                'description' => 'Clickatell API base URL',
            ],
        ];

        foreach ($defaults as $key => $meta) {
            $exists = Setting::where('key', $key)
                ->whereNull('tenant_id')
                ->exists();

            if (!$exists) {
                Setting::set(
                    key: $key,
                    value: $meta['value'],
                    type: $meta['type'],
                    group: Setting::GROUP_SMS,
                    description: $meta['description'],
                    tenantId: null
                );
            }
        }
    }

    /**
     * Normalize SMS settings and ensure only one gateway is active.
     */
    private function normalizeSmsSettings(array $settings): array
    {
        $gatewayFlags = [
            'twilio' => filter_var($settings['sms_twilio_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'msg91' => filter_var($settings['sms_msg91_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'clickatell' => filter_var($settings['sms_clickatell_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];

        $enabledGateways = array_keys(array_filter($gatewayFlags));

        if (count($enabledGateways) > 1) {
            throw ValidationException::withMessages([
                'settings.sms_active_gateway' => 'Only 1 active SMS gateway is allowed.',
            ]);
        }

        $activeGateway = strtolower((string) ($settings['sms_active_gateway'] ?? ''));

        if (count($enabledGateways) === 1) {
            $activeGateway = $enabledGateways[0];
        }

        if (!in_array($activeGateway, self::SMS_GATEWAYS, true)) {
            $activeGateway = $enabledGateways[0] ?? '';
        }

        $settings['sms_active_gateway'] = $activeGateway;
        $settings['sms_service_enabled'] = filter_var(
            $settings['sms_service_enabled'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? '1' : '0';

        $settings['sms_twilio_active'] = $activeGateway === 'twilio' ? '1' : '0';
        $settings['sms_msg91_active'] = $activeGateway === 'msg91' ? '1' : '0';
        $settings['sms_clickatell_active'] = $activeGateway === 'clickatell' ? '1' : '0';

        return $settings;
    }

    /**
     * Validate SMS settings and selected active gateway requirements.
     */
    private function validateSmsSettings(array $settings): void
    {
        $rules = [
            'sms_service_enabled' => ['required', 'boolean'],
            'sms_active_gateway' => ['required', Rule::in(self::SMS_GATEWAYS)],

            'sms_twilio_sid' => ['nullable', 'max:255'],
            'sms_twilio_auth_token' => ['nullable', 'max:255'],
            'sms_twilio_from' => ['nullable', 'max:255'],
            'sms_twilio_alphanumeric_sender_id' => ['nullable', 'max:50'],

            'sms_msg91_auth_key' => ['nullable', 'max:255'],
            'sms_msg91_sender_id' => ['nullable', 'max:30'],
            'sms_msg91_route' => ['nullable', 'max:20'],
            'sms_msg91_country' => ['nullable', 'max:10'],
            'sms_msg91_base_url' => ['nullable', 'max:255'],

            'sms_clickatell_api_key' => ['nullable', 'max:255'],
            'sms_clickatell_from' => ['nullable', 'max:50'],
            'sms_clickatell_base_url' => ['nullable', 'max:255'],
        ];

        $smsEnabled = filter_var($settings['sms_service_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($smsEnabled) {
            $activeGateway = $settings['sms_active_gateway'] ?? 'twilio';

            if ($activeGateway === 'twilio') {
                $rules['sms_twilio_sid'] = ['required', 'max:255'];
                $rules['sms_twilio_auth_token'] = ['required', 'max:255'];
                $rules['sms_twilio_from'] = ['nullable', 'max:255', 'required_without:sms_twilio_alphanumeric_sender_id'];
                $rules['sms_twilio_alphanumeric_sender_id'] = ['nullable', 'max:50', 'required_without:sms_twilio_from'];
            }

            if ($activeGateway === 'msg91') {
                $rules['sms_msg91_auth_key'] = ['required', 'max:255'];
                $rules['sms_msg91_sender_id'] = ['required', 'max:30'];
            }

            if ($activeGateway === 'clickatell') {
                $rules['sms_clickatell_api_key'] = ['required', 'max:255'];
            }
        }

        $messages = [
            'sms_twilio_sid.required' => 'Twilio SID is required when Twilio is active.',
            'sms_twilio_auth_token.required' => 'Twilio auth token is required when Twilio is active.',
            'sms_twilio_from.required_without' => 'Twilio phone number is required when alphanumeric sender ID is not provided.',
            'sms_twilio_alphanumeric_sender_id.required_without' => 'Twilio alphanumeric sender ID is required when phone number is not provided.',
            'sms_msg91_auth_key.required' => 'MSG91 auth key is required when MSG91 is active.',
            'sms_msg91_sender_id.required' => 'MSG91 sender ID is required when MSG91 is active.',
            'sms_clickatell_api_key.required' => 'Clickatell API key is required when Clickatell is active.',
            'sms_active_gateway.required' => 'Please activate one SMS gateway.',
            'sms_active_gateway.in' => 'Only 1 active SMS gateway is allowed.',
        ];

        Validator::make($settings, $rules, $messages)->validate();
    }
}
