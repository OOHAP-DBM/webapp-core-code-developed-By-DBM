<?php

namespace Modules\Settings\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use Illuminate\Http\Request;
use Modules\Settings\Services\SettingsService;

class SettingsController extends Controller
{
    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * SettingsController constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        $this->middleware(['auth:sanctum', 'role:super_admin|admin']);
    }

    /**
     * Get all settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $tenantId = $request->input('tenant_id');
            $group = $request->input('group');

            if ($group) {
                $settings = collect($this->settingsService->getByGroup($group, $tenantId));
                
                return response()->json([
                    'success' => true,
                    'data' => $settings->map(function ($setting, $key) {
                        return [
                            'key' => $key,
                            'value' => $setting['value'],
                            'type' => $setting['type'],
                            'description' => $setting['description'],
                            'group' => $setting['group'],
                        ];
                    })->values(),
                ]);
            }

            $settings = $this->settingsService->getAllWithMetadata($tenantId);

            return response()->json([
                'success' => true,
                'data' => SettingResource::collection($settings),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific setting by key.
     *
     * @param Request $request
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $key)
    {
        try {
            $tenantId = $request->input('tenant_id');
            $value = $this->settingsService->get($key, null, $tenantId);

            if ($value === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'value' => $value,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch setting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update settings (bulk update).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'settings' => 'required|array',
                'settings.*' => 'required',
                'tenant_id' => 'nullable|integer',
            ]);

            $settings = $request->input('settings');
            $tenantId = $request->input('tenant_id');

            $result = $this->settingsService->bulkUpdate($settings, $tenantId);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a single setting.
     *
     * @param Request $request
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSingle(Request $request, string $key)
    {
        try {
            $request->validate([
                'value' => 'required',
                'type' => 'sometimes|string|in:string,integer,float,boolean,json,array',
                'tenant_id' => 'nullable|integer',
            ]);

            $value = $request->input('value');
            $type = $request->input('type', 'string');
            $tenantId = $request->input('tenant_id');

            // Get existing setting to preserve metadata
            $existingSetting = $this->settingsService->getAllWithMetadata($tenantId)
                ->firstWhere('key', $key);

            if (!$existingSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting not found',
                ], 404);
            }

            $this->settingsService->set(
                $key,
                $value,
                $type,
                $tenantId,
                $existingSetting->description,
                $existingSetting->group
            );

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully',
                'data' => [
                    'key' => $key,
                    'value' => $this->settingsService->get($key, null, $tenantId),
                ],
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear settings cache.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache(Request $request)
    {
        try {
            $tenantId = $request->input('tenant_id');
            $this->settingsService->clearCache($tenantId);

            return response()->json([
                'success' => true,
                'message' => 'Settings cache cleared successfully',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

