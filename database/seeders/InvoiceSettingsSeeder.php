<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class InvoiceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $invoiceSettings = [
            // Company Details (Seller Information)
            [
                'key' => 'company_name',
                'value' => 'OOHAPP Private Limited',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'Legal company name for invoices',
            ],
            [
                'key' => 'company_gstin',
                'value' => '27AABCU9603R1ZX',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'Company GSTIN (15 characters)',
            ],
            [
                'key' => 'company_pan',
                'value' => 'AABCU9603R',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'Company PAN number',
            ],
            [
                'key' => 'company_address',
                'value' => '123, Business Tower, Andheri East',
                'type' => 'text',
                'group' => 'invoice',
                'description' => 'Company registered address',
            ],
            [
                'key' => 'company_city',
                'value' => 'Mumbai',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'Company city',
            ],
            [
                'key' => 'company_state',
                'value' => 'Maharashtra',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'Company state',
            ],
            [
                'key' => 'company_state_code',
                'value' => '27',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'GST state code (2 digits)',
            ],
            [
                'key' => 'company_pincode',
                'value' => '400069',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'Company pincode',
            ],

            // HSN/SAC Codes
            [
                'key' => 'hsn_advertising_services',
                'value' => '998599',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'HSN/SAC code for outdoor advertising services',
            ],
            [
                'key' => 'hsn_printing_services',
                'value' => '998914',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'HSN/SAC code for printing services',
            ],
            [
                'key' => 'hsn_mounting_services',
                'value' => '995415',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'HSN/SAC code for mounting/installation services',
            ],

            // GST Settings
            [
                'key' => 'gst_rate',
                'value' => '18.00',
                'type' => 'decimal',
                'group' => 'invoice',
                'description' => 'Default GST rate percentage',
            ],

            // Invoice Terms & Conditions
            [
                'key' => 'invoice_terms_conditions',
                'value' => "1. Payment is due within 30 days from invoice date.\n2. Please quote invoice number in all correspondence.\n3. Interest @ 18% p.a. will be charged on overdue amounts.\n4. Subject to Mumbai jurisdiction only.\n5. All disputes subject to arbitration as per Indian Arbitration Act.",
                'type' => 'text',
                'group' => 'invoice',
                'description' => 'Standard terms and conditions for invoices',
            ],
            [
                'key' => 'invoice_payment_terms',
                'value' => 'Net 30 Days',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'Payment terms text (e.g., Net 30 Days, Due on Receipt)',
            ],
            [
                'key' => 'invoice_payment_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'invoice',
                'description' => 'Number of days for payment (for due date calculation)',
            ],

            // Invoice Formatting
            [
                'key' => 'invoice_prefix',
                'value' => 'INV',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'Invoice number prefix (default: INV)',
            ],
            [
                'key' => 'invoice_auto_send_email',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'invoice',
                'description' => 'Automatically send invoice email on generation',
            ],
            [
                'key' => 'invoice_cc_emails',
                'value' => 'accounts@oohapp.com,finance@oohapp.com',
                'type' => 'string',
                'group' => 'invoice',
                'description' => 'CC email addresses for invoice emails (comma-separated)',
            ],

            // Invoice Notes
            [
                'key' => 'invoice_footer_note',
                'value' => 'Thank you for your business! For any queries, please contact our support team.',
                'type' => 'text',
                'group' => 'invoice',
                'description' => 'Footer note for invoices',
            ],

            // POS Invoice Settings
            [
                'key' => 'pos_auto_invoice',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'invoice',
                'description' => 'Auto-generate invoice for POS bookings',
            ],
        ];

        foreach ($invoiceSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Invoice settings seeded successfully!');
    }
}
