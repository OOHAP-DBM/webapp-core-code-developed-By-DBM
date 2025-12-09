<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = $this->getDefaultTemplates();

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                [
                    'event_type' => $template['event_type'],
                    'channel' => $template['channel'],
                    'is_system_default' => true,
                ],
                $template
            );
        }

        $this->command->info('Default notification templates seeded successfully!');
    }

    /**
     * Get default templates
     */
    protected function getDefaultTemplates(): array
    {
        return [
            // OTP Templates
            [
                'name' => 'OTP - Email',
                'event_type' => NotificationTemplate::EVENT_OTP,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'subject' => 'Your OTP Code - {{app_name}}',
                'body' => "Hello {{user_name}},\n\nYour OTP code is: {{otp_code}}\n\nThis code will expire in {{expiry_minutes}} minutes.\n\nIf you didn't request this code, please ignore this email.\n\nBest regards,\n{{app_name}}",
                'html_body' => '<p>Hello <strong>{{user_name}}</strong>,</p><p>Your OTP code is: <strong style="font-size: 24px; color: #007bff;">{{otp_code}}</strong></p><p>This code will expire in {{expiry_minutes}} minutes.</p><p>If you didn\'t request this code, please ignore this email.</p><p>Best regards,<br>{{app_name}}</p>',
                'description' => 'OTP verification email',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 100,
            ],
            [
                'name' => 'OTP - SMS',
                'event_type' => NotificationTemplate::EVENT_OTP,
                'channel' => NotificationTemplate::CHANNEL_SMS,
                'body' => "Your {{app_name}} OTP is {{otp_code}}. Valid for {{expiry_minutes}} minutes. Do not share with anyone.",
                'description' => 'OTP verification SMS',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 100,
            ],

            // Enquiry Received Templates
            [
                'name' => 'Enquiry Received - Vendor Email',
                'event_type' => NotificationTemplate::EVENT_ENQUIRY_RECEIVED,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'subject' => 'New Enquiry Received - {{property_name}}',
                'body' => "Hello {{vendor_name}},\n\nYou have received a new enquiry!\n\nEnquiry ID: {{enquiry_id}}\nCustomer: {{customer_name}}\nProperty: {{property_name}}\nDate: {{enquiry_date}}\n\nView details: {{enquiry_url}}\n\nPlease respond promptly to secure the booking.\n\nBest regards,\n{{app_name}}",
                'html_body' => '<h3>New Enquiry Received!</h3><p>Hello <strong>{{vendor_name}}</strong>,</p><p>You have received a new enquiry for your property.</p><table style="border-collapse: collapse; width: 100%;"><tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Enquiry ID:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{enquiry_id}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Customer:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{customer_name}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Property:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{property_name}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Date:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{enquiry_date}}</td></tr></table><p><a href="{{enquiry_url}}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px;">View Enquiry Details</a></p><p>Please respond promptly to secure the booking.</p>',
                'description' => 'Notify vendor when new enquiry is received',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 90,
            ],

            // Offer Created Templates
            [
                'name' => 'Offer Created - Customer Email',
                'event_type' => NotificationTemplate::EVENT_OFFER_CREATED,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'subject' => 'New Offer from {{vendor_name}} - {{property_name}}',
                'body' => "Hello {{customer_name}},\n\nGreat news! You have received a new offer.\n\nOffer ID: {{offer_id}}\nVendor: {{vendor_name}}\nProperty: {{property_name}}\nOffer Amount: ₹{{offer_amount}}\nExpires: {{expiry_date}}\n\nView offer: {{offer_url}}\n\nReview and accept the offer before it expires.\n\nBest regards,\n{{app_name}}",
                'html_body' => '<h3>New Offer Received!</h3><p>Hello <strong>{{customer_name}}</strong>,</p><p>Great news! <strong>{{vendor_name}}</strong> has sent you an offer.</p><div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;"><h2 style="color: #28a745; margin: 0;">₹{{offer_amount}}</h2><p style="margin: 5px 0;">for {{property_name}}</p></div><table style="border-collapse: collapse; width: 100%;"><tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Offer ID:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{offer_id}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Expires:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{expiry_date}}</td></tr></table><p><a href="{{offer_url}}" style="background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px; font-weight: bold;">View & Accept Offer</a></p>',
                'description' => 'Notify customer when vendor creates an offer',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 90,
            ],

            // Offer Accepted Templates
            [
                'name' => 'Offer Accepted - Vendor Email',
                'event_type' => NotificationTemplate::EVENT_OFFER_ACCEPTED,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'subject' => 'Offer Accepted by {{customer_name}}!',
                'body' => "Hello {{vendor_name}},\n\nGreat news! Your offer has been accepted.\n\nOffer ID: {{offer_id}}\nCustomer: {{customer_name}}\nAmount: ₹{{offer_amount}}\nAccepted on: {{acceptance_date}}\n\nView details: {{offer_url}}\n\nPlease proceed with the next steps.\n\nBest regards,\n{{app_name}}",
                'html_body' => '<h3 style="color: #28a745;">🎉 Offer Accepted!</h3><p>Hello <strong>{{vendor_name}}</strong>,</p><p>Great news! <strong>{{customer_name}}</strong> has accepted your offer.</p><div style="background: #d4edda; padding: 20px; border-radius: 10px; border-left: 4px solid #28a745; margin: 20px 0;"><h2 style="margin: 0; color: #155724;">₹{{offer_amount}}</h2><p style="margin: 5px 0; color: #155724;">Accepted on {{acceptance_date}}</p></div><p><a href="{{offer_url}}" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px;">View Details</a></p>',
                'description' => 'Notify vendor when customer accepts their offer',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 90,
            ],

            // Payment Complete Templates
            [
                'name' => 'Payment Complete - Customer Email',
                'event_type' => NotificationTemplate::EVENT_PAYMENT_COMPLETE,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'subject' => 'Payment Successful - Booking #{{booking_id}}',
                'body' => "Hello {{customer_name}},\n\nYour payment has been received successfully!\n\nPayment ID: {{payment_id}}\nBooking ID: {{booking_id}}\nAmount: ₹{{payment_amount}}\nPayment Method: {{payment_method}}\nDate: {{payment_date}}\n\nDownload invoice: {{invoice_url}}\n\nThank you for your business!\n\nBest regards,\n{{app_name}}",
                'html_body' => '<h3 style="color: #28a745;">✓ Payment Successful!</h3><p>Hello <strong>{{customer_name}}</strong>,</p><p>Your payment has been received successfully.</p><div style="background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;"><table style="width: 100%;"><tr><td><strong>Amount Paid:</strong></td><td style="text-align: right; font-size: 24px; color: #28a745;"><strong>₹{{payment_amount}}</strong></td></tr><tr><td>Payment ID:</td><td style="text-align: right;">{{payment_id}}</td></tr><tr><td>Booking ID:</td><td style="text-align: right;">{{booking_id}}</td></tr><tr><td>Method:</td><td style="text-align: right;">{{payment_method}}</td></tr><tr><td>Date:</td><td style="text-align: right;">{{payment_date}}</td></tr></table></div><p><a href="{{invoice_url}}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Download Invoice</a></p>',
                'description' => 'Payment confirmation for customer',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 100,
            ],

            // Refund Issued Templates
            [
                'name' => 'Refund Issued - Customer Email',
                'event_type' => NotificationTemplate::EVENT_REFUND_ISSUED,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'subject' => 'Refund Processed - Booking #{{booking_id}}',
                'body' => "Hello {{customer_name}},\n\nYour refund has been processed successfully.\n\nRefund ID: {{refund_id}}\nBooking ID: {{booking_id}}\nRefund Amount: ₹{{refund_amount}}\nReason: {{refund_reason}}\nMethod: {{refund_method}}\nDate: {{refund_date}}\n\nThe amount will be credited to your account within 5-7 business days.\n\nBest regards,\n{{app_name}}",
                'html_body' => '<h3>Refund Processed</h3><p>Hello <strong>{{customer_name}}</strong>,</p><p>Your refund has been processed successfully.</p><table style="border-collapse: collapse; width: 100%; margin: 20px 0;"><tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Refund Amount:</strong></td><td style="padding: 8px; border: 1px solid #ddd; color: #28a745; font-size: 20px;"><strong>₹{{refund_amount}}</strong></td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;">Refund ID:</td><td style="padding: 8px; border: 1px solid #ddd;">{{refund_id}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;">Booking ID:</td><td style="padding: 8px; border: 1px solid #ddd;">{{booking_id}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;">Reason:</td><td style="padding: 8px; border: 1px solid #ddd;">{{refund_reason}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;">Method:</td><td style="padding: 8px; border: 1px solid #ddd;">{{refund_method}}</td></tr></table><p style="background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107;">The amount will be credited to your account within 5-7 business days.</p>',
                'description' => 'Refund confirmation for customer',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 100,
            ],

            // Survey Booked Templates
            [
                'name' => 'Survey Booked - Customer SMS',
                'event_type' => NotificationTemplate::EVENT_SURVEY_BOOKED,
                'channel' => NotificationTemplate::CHANNEL_SMS,
                'body' => "Survey scheduled for {{survey_date}} at {{survey_time}}. Address: {{property_address}}. Booking ID: {{booking_id}}. -{{app_name}}",
                'description' => 'Survey booking confirmation SMS',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 90,
            ],

            // Campaign Started Templates
            [
                'name' => 'Campaign Started - Customer Email',
                'event_type' => NotificationTemplate::EVENT_CAMPAIGN_STARTED,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'subject' => 'Your Campaign is Now Live! - {{property_name}}',
                'body' => "Hello {{customer_name}},\n\nYour advertising campaign is now live!\n\nCampaign ID: {{campaign_id}}\nBooking ID: {{booking_id}}\nProperty: {{property_name}}\nStart Date: {{campaign_start_date}}\nEnd Date: {{campaign_end_date}}\n\nYour advertisement is now visible to the public.\n\nBest regards,\n{{app_name}}",
                'html_body' => '<h3 style="color: #28a745;">🚀 Campaign Live!</h3><p>Hello <strong>{{customer_name}}</strong>,</p><p>Your advertising campaign for <strong>{{property_name}}</strong> is now live!</p><div style="background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;"><p><strong>Campaign Period:</strong><br>{{campaign_start_date}} to {{campaign_end_date}}</p><p><strong>Booking ID:</strong> {{booking_id}}</p></div><p>Your advertisement is now visible to the public.</p>',
                'description' => 'Campaign start notification',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 80,
            ],

            // Booking Completed Templates
            [
                'name' => 'Booking Completed - Customer Email',
                'event_type' => NotificationTemplate::EVENT_BOOKING_COMPLETED,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'subject' => 'Booking Completed - Thank You!',
                'body' => "Hello {{customer_name}},\n\nYour booking has been successfully completed!\n\nBooking ID: {{booking_id}}\nVendor: {{vendor_name}}\nProperty: {{property_name}}\nCompletion Date: {{completion_date}}\n\nView details: {{booking_url}}\n\nThank you for choosing {{app_name}}. We hope to serve you again!\n\nBest regards,\n{{app_name}}",
                'html_body' => '<h3 style="color: #28a745;">✓ Booking Completed!</h3><p>Hello <strong>{{customer_name}}</strong>,</p><p>Your booking with <strong>{{vendor_name}}</strong> has been successfully completed.</p><table style="border-collapse: collapse; width: 100%; margin: 20px 0;"><tr><td style="padding: 8px; border: 1px solid #ddd;">Booking ID:</td><td style="padding: 8px; border: 1px solid #ddd;">{{booking_id}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;">Property:</td><td style="padding: 8px; border: 1px solid #ddd;">{{property_name}}</td></tr><tr><td style="padding: 8px; border: 1px solid #ddd;">Completed:</td><td style="padding: 8px; border: 1px solid #ddd;">{{completion_date}}</td></tr></table><p><a href="{{booking_url}}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">View Booking</a></p><p>Thank you for choosing {{app_name}}!</p>',
                'description' => 'Booking completion notification',
                'is_active' => true,
                'is_system_default' => true,
                'priority' => 80,
            ],
        ];
    }
}

