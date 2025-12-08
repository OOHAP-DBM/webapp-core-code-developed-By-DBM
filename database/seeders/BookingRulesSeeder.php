<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Services\SettingsService;

class BookingRulesSeeder extends Seeder
{
    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * BookingRulesSeeder constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Seed booking rules settings.
     *
     * @return void
     */
    public function run(): void
    {
        $bookingRules = [
            [
                'key' => 'booking_hold_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Minutes to hold a booking before payment required',
                'group' => 'booking',
            ],
            [
                'key' => 'grace_period_minutes',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Grace period before booking start time for cancellation',
                'group' => 'booking',
            ],
            [
                'key' => 'max_future_booking_start_months',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Maximum months in future a booking can start',
                'group' => 'booking',
            ],
            [
                'key' => 'booking_min_duration_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Minimum booking duration in days',
                'group' => 'booking',
            ],
            [
                'key' => 'booking_max_duration_months',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Maximum booking duration in months',
                'group' => 'booking',
            ],
            [
                'key' => 'allow_weekly_booking',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Allow weekly booking option',
                'group' => 'booking',
            ],
        ];

        foreach ($bookingRules as $rule) {
            $this->settingsService->set(
                $rule['key'],
                $rule['value'],
                $rule['type'],
                null, // Global settings
                $rule['description'],
                $rule['group']
            );
        }

        $this->command->info('✓ Booking rules settings seeded successfully');
    }
}
