<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * PROMPT 105: Offer Auto-Expiry Settings
 * Seed default configuration for offer expiry
 */
class OfferExpirySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'offer_default_expiry_days',
                'value' => '7',
                'type' => Setting::TYPE_INTEGER,
                'description' => 'Default number of days until offers auto-expire (0 = no expiry)',
                'group' => 'general',
                'tenant_id' => null,
            ],
            [
                'key' => 'offer_expiry_warning_days',
                'value' => '2',
                'type' => Setting::TYPE_INTEGER,
                'description' => 'Send warning notification X days before offer expires',
                'group' => 'notification',
                'tenant_id' => null,
            ],
            [
                'key' => 'offer_allow_vendor_custom_expiry',
                'value' => 'true',
                'type' => Setting::TYPE_BOOLEAN,
                'description' => 'Allow vendors to set custom expiry days for their offers',
                'group' => 'general',
                'tenant_id' => null,
            ],
            [
                'key' => 'offer_min_expiry_days',
                'value' => '1',
                'type' => Setting::TYPE_INTEGER,
                'description' => 'Minimum expiry days vendors can set (if custom expiry allowed)',
                'group' => 'general',
                'tenant_id' => null,
            ],
            [
                'key' => 'offer_max_expiry_days',
                'value' => '90',
                'type' => Setting::TYPE_INTEGER,
                'description' => 'Maximum expiry days vendors can set (if custom expiry allowed)',
                'group' => 'general',
                'tenant_id' => null,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                [
                    'key' => $setting['key'],
                    'tenant_id' => $setting['tenant_id'],
                ],
                $setting
            );
        }

        $this->command->info('✅ Offer expiry settings seeded successfully');
    }
}
