<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsApiController extends Controller
{
    /**
     * Get all settings grouped by category.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $groups = Setting::getAvailableGroups();
        $data = [];

        foreach ($groups as $groupKey => $groupLabel) {
            $data[$groupKey] = [
                'label' => $groupLabel,
                'settings' => Setting::getGroup($groupKey),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get settings for a specific group.
     *
     * @param string $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $group)
    {
        $groups = Setting::getAvailableGroups();

        if (!isset($groups[$group])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid settings group.',
            ], 404);
        }

        $settings = Setting::global()->group($group)->get()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->getTypedValue(),
                'type' => $setting->type,
                'description' => $setting->description,
                'group' => $setting->group,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'group' => $group,
                'label' => $groups[$group],
                'settings' => $settings,
            ],
        ]);
    }

    /**
     * Get a single setting value.
     *
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSetting(string $key)
    {
        $setting = Setting::where('key', $key)->whereNull('tenant_id')->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $setting->key,
                'value' => $setting->getTypedValue(),
                'type' => $setting->type,
                'description' => $setting->description,
                'group' => $setting->group,
            ],
        ]);
    }

    /**
     * Update settings for a group.
     *
     * @param Request $request
     * @param string $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $group)
    {
        $groups = Setting::getAvailableGroups();

        if (!isset($groups[$group])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid settings group.',
            ], 404);
        }

        $settings = $request->input('settings', []);
        $updated = [];
        $errors = [];

        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->whereNull('tenant_id')->first();

            if (!$setting) {
                $errors[$key] = 'Setting not found.';
                continue;
            }

            // Validate based on type
            try {
                $this->validateSettingValue($value, $setting->type);
                $setting->setTypedValue($value);
                $setting->save();
                $updated[] = $key;
            } catch (\Exception $e) {
                $errors[$key] = $e->getMessage();
            }
        }

        return response()->json([
            'success' => empty($errors),
            'message' => empty($errors) ? 'Settings updated successfully.' : 'Some settings failed to update.',
            'data' => [
                'updated' => $updated,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Update a single setting.
     *
     * @param Request $request
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSetting(Request $request, string $key)
    {
        $setting = Setting::where('key', $key)->whereNull('tenant_id')->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.',
            ], 404);
        }

        $value = $request->input('value');

        // Validate based on type
        try {
            $this->validateSettingValue($value, $setting->type);
            $setting->setTypedValue($value);
            $setting->save();

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully.',
                'data' => [
                    'key' => $setting->key,
                    'value' => $setting->getTypedValue(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Clear settings cache.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        Setting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Settings cache cleared successfully.',
        ]);
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
