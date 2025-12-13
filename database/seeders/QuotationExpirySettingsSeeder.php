<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * PROMPT 106: Quotation Expiry Settings Seeder
 * 
 * Seeds settings for quotation expiry and auto-cancellation
 */
class QuotationExpirySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'quotation_default_expiry_days',
                'value' => '7',
                'type' => Setting::TYPE_INTEGER,
                'group' => 'general',
                'label' => 'Default Quotation Expiry Days',
                'description' => 'Default number of days before a quotation expires (inherits from offer expiry)',
                'is_public' => false,
                'validation_rules' => 'required|integer|min:1|max:90',
            ],
            [
                'key' => 'quotation_expiry_warning_days',
                'value' => '2',
                'type' => Setting::TYPE_INTEGER,
                'group' => 'notification',
                'label' => 'Quotation Expiry Warning Days',
                'description' => 'Days before expiry to send warning notifications',
                'is_public' => false,
                'validation_rules' => 'required|integer|min:1|max:30',
            ],
            [
                'key' => 'quotation_auto_cancel_enabled',
                'value' => 'true',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'general',
                'label' => 'Enable Auto-Cancel on Quotation Expiry',
                'description' => 'Automatically cancel related bookings when quotation expires',
                'is_public' => false,
                'validation_rules' => 'required|boolean',
            ],
            [
                'key' => 'quotation_notify_on_expiry',
                'value' => 'true',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'notification',
                'label' => 'Notify on Quotation Expiry',
                'description' => 'Send notifications to customer and vendor when quotation expires',
                'is_public' => false,
                'validation_rules' => 'required|boolean',
            ],
            [
                'key' => 'quotation_update_thread_on_expiry',
                'value' => 'true',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'general',
                'label' => 'Update Thread on Quotation Expiry',
                'description' => 'Post system message to conversation thread when quotation expires',
                'is_public' => false,
                'validation_rules' => 'required|boolean',
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }

        $this->command->info('✅ Quotation expiry settings seeded successfully.');
    }
}
