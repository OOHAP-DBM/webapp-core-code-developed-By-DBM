<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class POSSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'pos_auto_approval',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Auto-approve POS bookings without admin review',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_allow_cash_payment',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Allow cash payments without online payment gateway',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_auto_invoice',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Automatically generate invoice for POS bookings',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_credit_note_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Default credit note validity in days',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_enable_sms_notification',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Send SMS notifications to customers for POS bookings',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_enable_whatsapp_notification',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Send WhatsApp notifications to customers for POS bookings',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_enable_email_notification',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Send email notifications to customers for POS bookings',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_gst_rate',
                'value' => '18',
                'type' => 'decimal',
                'description' => 'GST rate percentage for POS bookings',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_allow_credit_note',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Allow vendors to create credit note bookings',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pos_require_customer_gstin',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Make customer GSTIN mandatory for POS bookings',
                'group' => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✓ POS settings seeded successfully');
    }
}
