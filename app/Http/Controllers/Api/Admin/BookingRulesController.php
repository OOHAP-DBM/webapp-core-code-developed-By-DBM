<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Settings\Services\SettingsService;

class BookingRulesController extends Controller
{
    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * Booking rule keys
     */
    const BOOKING_RULES = [
        'booking_hold_minutes',
        'grace_period_minutes',
        'max_future_booking_start_months',
        'booking_min_duration_days',
        'booking_max_duration_months',
        'allow_weekly_booking',
    ];

    /**
     * BookingRulesController constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        $this->middleware(['auth:sanctum', 'role:super_admin|admin']);
    }

    /**
     * Get all booking rules.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $rules = [];
            
            foreach (self::BOOKING_RULES as $key) {
                $setting = $this->settingsService->getAllWithMetadata()
                    ->firstWhere('key', $key);
                
                if ($setting) {
                    $rules[$key] = [
                        'value' => $setting->getTypedValue(),
                        'type' => $setting->type,
                        'description' => $setting->description,
                        'group' => $setting->group,
                    ];
                } else {
                    // Return defaults if not set
                    $rules[$key] = [
                        'value' => $this->getDefaultValue($key),
                        'type' => $this->getDefaultType($key),
                        'description' => $this->getDefaultDescription($key),
                        'group' => 'booking',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $rules,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking rules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update booking rules.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_hold_minutes' => 'nullable|integer|min:1|max:1440',
            'grace_period_minutes' => 'nullable|integer|min:0|max:1440',
            'max_future_booking_start_months' => 'nullable|integer|min:1|max:24',
            'booking_min_duration_days' => 'nullable|integer|min:1|max:365',
            'booking_max_duration_months' => 'nullable|integer|min:1|max:36',
            'allow_weekly_booking' => 'nullable|boolean',
        ]);

        try {
            foreach ($validated as $key => $value) {
                if (in_array($key, self::BOOKING_RULES)) {
                    $this->settingsService->set(
                        $key,
                        $value,
                        $this->getDefaultType($key),
                        null, // Global settings
                        $this->getDefaultDescription($key),
                        'booking'
                    );
                }
            }

            // Clear cache to ensure updated values are used
            $this->settingsService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Booking rules updated successfully',
                'data' => $validated,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking rules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get default value for a booking rule.
     *
     * @param string $key
     * @return mixed
     */
    protected function getDefaultValue(string $key)
    {
        $defaults = [
            'booking_hold_minutes' => 30,
            'grace_period_minutes' => 15,
            'max_future_booking_start_months' => 12,
            'booking_min_duration_days' => 7,
            'booking_max_duration_months' => 12,
            'allow_weekly_booking' => false,
        ];

        return $defaults[$key] ?? null;
    }

    /**
     * Get default type for a booking rule.
     *
     * @param string $key
     * @return string
     */
    protected function getDefaultType(string $key): string
    {
        return $key === 'allow_weekly_booking' ? 'boolean' : 'integer';
    }

    /**
     * Get default description for a booking rule.
     *
     * @param string $key
     * @return string
     */
    protected function getDefaultDescription(string $key): string
    {
        $descriptions = [
            'booking_hold_minutes' => 'Minutes to hold a booking before payment required',
            'grace_period_minutes' => 'Grace period before booking start time for cancellation',
            'max_future_booking_start_months' => 'Maximum months in future a booking can start',
            'booking_min_duration_days' => 'Minimum booking duration in days',
            'booking_max_duration_months' => 'Maximum booking duration in months',
            'allow_weekly_booking' => 'Allow weekly booking option',
        ];

        return $descriptions[$key] ?? '';
    }
}
