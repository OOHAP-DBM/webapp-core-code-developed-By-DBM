<?php

namespace Modules\Admin\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Settings\Services\SettingsService;

class BookingRuleController extends Controller
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
     * BookingRuleController constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display booking rules configuration page.
     *
     * @return View
     */
    public function index(): View
    {
        $bookingRules = [];
        
        foreach (self::BOOKING_RULES as $key) {
            $value = $this->settingsService->get($key, $this->getDefaultValue($key));
            $bookingRules[$key] = $value;
        }

        return view('admin.settings.booking_rules', compact('bookingRules'));
    }

    /**
     * Update booking rules.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_hold_minutes' => 'required|integer|min:1|max:1440',
            'grace_period_minutes' => 'required|integer|min:0|max:1440',
            'max_future_booking_start_months' => 'required|integer|min:1|max:24',
            'booking_min_duration_days' => 'required|integer|min:1|max:365',
            'booking_max_duration_months' => 'required|integer|min:1|max:36',
            'allow_weekly_booking' => 'nullable|boolean',
        ]);

        try {
            // Handle checkbox (if not checked, it won't be in request)
            $validated['allow_weekly_booking'] = $request->has('allow_weekly_booking') ? 1 : 0;

            foreach ($validated as $key => $value) {
                $this->settingsService->set(
                    $key,
                    $value,
                    $this->getDefaultType($key),
                    null, // Global settings
                    $this->getDefaultDescription($key),
                    'booking'
                );
            }

            // Clear cache
            $this->settingsService->clearCache();

            return redirect()
                ->route('admin.booking-rules.index')
                ->with('success', 'Booking rules updated successfully');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update booking rules: ' . $e->getMessage());
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
