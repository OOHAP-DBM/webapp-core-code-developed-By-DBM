<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Booking Settings
            [
                'key' => 'booking_hold_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Number of minutes to hold a booking before auto-cancellation',
                'group' => 'booking',
            ],
            [
                'key' => 'booking_hold_duration_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Payment hold duration in minutes for booking flow',
                'group' => 'booking',
            ],
            [
                'key' => 'draft_expiry_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Draft auto-expiry duration in minutes',
                'group' => 'booking',
            ],
            [
                'key' => 'min_booking_duration_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Minimum booking duration in days',
                'group' => 'booking',
            ],
            [
                'key' => 'max_booking_duration_days',
                'value' => '365',
                'type' => 'integer',
                'description' => 'Maximum booking duration in days',
                'group' => 'booking',
            ],
            [
                'key' => 'min_advance_booking_days',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Minimum days in advance for booking',
                'group' => 'booking',
            ],
            [
                'key' => 'max_advance_booking_days',
                'value' => '365',
                'type' => 'integer',
                'description' => 'Maximum days in advance for booking',
                'group' => 'booking',
            ],
            [
                'key' => 'max_future_booking_start_months',
                'value' => '4',
                'type' => 'integer',
                'description' => 'Maximum months in advance a booking can start',
                'group' => 'booking',
            ],
            [
                'key' => 'min_booking_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Minimum number of days for a booking (legacy)',
                'group' => 'booking',
            ],
            [
                'key' => 'max_booking_days',
                'value' => '90',
                'type' => 'integer',
                'description' => 'Maximum number of days for a single booking (legacy)',
                'group' => 'booking',
            ],
            [
                'key' => 'auto_approve_bookings',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically approve bookings without admin review',
                'group' => 'booking',
            ],
            [
                'key' => 'allow_overlapping_bookings',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Allow multiple bookings for the same hoarding at the same time',
                'group' => 'booking',
            ],

            // Payment Settings
            [
                'key' => 'admin_commission_percent',
                'value' => '10.00',
                'type' => 'float',
                'description' => 'Admin commission percentage on bookings',
                'group' => 'payment',
            ],
            [
                'key' => 'payment_gateway',
                'value' => 'razorpay',
                'type' => 'string',
                'description' => 'Default payment gateway (razorpay, stripe, paypal)',
                'group' => 'payment',
            ],
            [
                'key' => 'advance_payment_percent',
                'value' => '50.00',
                'type' => 'float',
                'description' => 'Percentage of total amount required as advance payment',
                'group' => 'payment',
            ],
            [
                'key' => 'refund_processing_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Number of days to process refunds',
                'group' => 'payment',
            ],
            [
                'key' => 'vendor_payout_cycle_days',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Number of days for vendor payout cycle',
                'group' => 'payment',
            ],
            [
                'key' => 'enable_wallet',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable wallet functionality for users',
                'group' => 'payment',
            ],

            // Commission Settings
            [
                'key' => 'vendor_commission_percent',
                'value' => '5.00',
                'type' => 'float',
                'description' => 'Commission percentage for vendors (if subvendor involved)',
                'group' => 'commission',
            ],
            [
                'key' => 'subvendor_commission_percent',
                'value' => '3.00',
                'type' => 'float',
                'description' => 'Commission percentage for subvendors',
                'group' => 'commission',
            ],
            [
                'key' => 'referral_commission_percent',
                'value' => '2.00',
                'type' => 'float',
                'description' => 'Commission percentage for referrals',
                'group' => 'commission',
            ],

            // DOOH Settings
            [
                'key' => 'dooh_min_slots_per_day',
                'value' => '6',
                'type' => 'integer',
                'description' => 'Minimum number of time slots per day for DOOH',
                'group' => 'dooh',
            ],
            [
                'key' => 'dooh_slot_duration_seconds',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Duration of each DOOH advertisement slot in seconds',
                'group' => 'dooh',
            ],
            [
                'key' => 'dooh_content_review_required',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Require admin review for DOOH content before publishing',
                'group' => 'dooh',
            ],
            [
                'key' => 'dooh_max_file_size_mb',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Maximum file size for DOOH content in MB',
                'group' => 'dooh',
            ],
            [
                'key' => 'dooh_allowed_formats',
                'value' => '["mp4", "mov", "avi", "jpg", "png", "gif"]',
                'type' => 'json',
                'description' => 'Allowed file formats for DOOH content',
                'group' => 'dooh',
            ],

            // Notification Settings
            [
                'key' => 'email_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable email notifications',
                'group' => 'notification',
            ],
            [
                'key' => 'sms_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable SMS notifications',
                'group' => 'notification',
            ],
            [
                'key' => 'push_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable push notifications',
                'group' => 'notification',
            ],
            [
                'key' => 'notification_booking_confirmed',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Send notification when booking is confirmed',
                'group' => 'notification',
            ],
            [
                'key' => 'notification_payment_received',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Send notification when payment is received',
                'group' => 'notification',
            ],
            [
                'key' => 'notification_booking_expiring',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Send notification before booking expiry',
                'group' => 'notification',
            ],
            [
                'key' => 'booking_expiry_alert_days',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Days before booking expiry to send alert',
                'group' => 'notification',
            ],

            // General Settings
            [
                'key' => 'site_name',
                'value' => 'OOHAPP',
                'type' => 'string',
                'description' => 'Website name',
                'group' => 'general',
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Your Out-of-Home Advertising Platform',
                'type' => 'string',
                'description' => 'Website tagline',
                'group' => 'general',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@oohapp.com',
                'type' => 'string',
                'description' => 'Support email address',
                'group' => 'general',
            ],
            [
                'key' => 'support_phone',
                'value' => '+91 9876543210',
                'type' => 'string',
                'description' => 'Support phone number',
                'group' => 'general',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode',
                'group' => 'general',
            ],
            [
                'key' => 'default_currency',
                'value' => 'INR',
                'type' => 'string',
                'description' => 'Default currency code',
                'group' => 'general',
            ],
            [
                'key' => 'default_timezone',
                'value' => 'Asia/Kolkata',
                'type' => 'string',
                'description' => 'Default timezone',
                'group' => 'general',
            ],
            [
                'key' => 'items_per_page',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Default number of items per page in listings',
                'group' => 'general',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                [
                    'key' => $setting['key'],
                    'tenant_id' => null, // Global settings
                ],
                $setting
            );
        }

        $this->command->info('✅ Default settings seeded successfully!');
    }
}

