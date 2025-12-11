<?php

namespace Database\Seeders;

use App\Models\CancellationPolicy;
use Illuminate\Database\Seeder;

class DefaultCancellationPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates default cancellation policy as per PROMPT 71:
     * - Cancellation within 7 days (168 hours) = partial refund
     * - After campaign start = no refund
     */
    public function run(): void
    {
        // Check if default policy already exists
        if (CancellationPolicy::where('is_default', true)->exists()) {
            $this->command->info('Default cancellation policy already exists. Skipping...');
            return;
        }

        $this->command->info('Creating default cancellation policy...');

        CancellationPolicy::create([
            'vendor_id' => null, // Global policy
            'name' => 'Default 7-Day Cancellation Policy',
            'description' => 'Standard cancellation policy: Full refund within 7 days, partial refund within 3 days, no refund after campaign starts.',
            'is_active' => true,
            'is_default' => true,
            'applies_to' => 'all', // Applies to all roles
            'booking_type' => null, // Applies to all booking types (ooh, dooh, pos)
            
            // Time windows (sorted by hours_before descending)
            'time_windows' => [
                [
                    'hours_before' => 168, // 7 days
                    'refund_percent' => 100, // 100% refund
                    'customer_fee_percent' => 0, // No fee
                    'vendor_penalty_percent' => null,
                ],
                [
                    'hours_before' => 72, // 3 days
                    'refund_percent' => 50, // 50% refund
                    'customer_fee_percent' => 10, // 10% cancellation fee
                    'vendor_penalty_percent' => null,
                ],
                [
                    'hours_before' => 24, // 1 day
                    'refund_percent' => 25, // 25% refund
                    'customer_fee_percent' => 15, // 15% cancellation fee
                    'vendor_penalty_percent' => null,
                ],
                [
                    'hours_before' => 0, // Less than 24 hours
                    'refund_percent' => 0, // No refund
                    'customer_fee_percent' => 100, // Full cancellation fee
                    'vendor_penalty_percent' => null,
                ],
            ],
            
            // Customer cancellation fees
            'customer_fee_type' => 'percentage',
            'customer_fee_value' => 10.00, // 10% default fee
            'customer_min_fee' => 500.00, // Minimum ₹500 fee
            'customer_max_fee' => 10000.00, // Maximum ₹10,000 fee
            
            // Vendor cancellation penalties (when vendor cancels)
            'vendor_penalty_type' => 'percentage',
            'vendor_penalty_value' => 20.00, // 20% penalty
            'vendor_min_penalty' => 1000.00,
            'vendor_max_penalty' => 50000.00,
            
            // Refund settings
            'auto_refund_enabled' => true, // Enable auto-refund through payment gateway
            'enforce_campaign_start' => true, // No refund after campaign starts
            'allow_partial_refund' => true, // Allow partial refunds
            'refund_processing_days' => 7, // 7 business days for refund
            'refund_method' => 'original', // Refund to original payment method
            
            // POS-specific settings
            'pos_auto_refund_disabled' => true, // POS bookings require manual refund
            'pos_refund_note' => 'POS bookings require manual refund processing. Contact admin for refund.',
            
            // Admin override
            'allow_admin_override' => true,
            'override_conditions' => 'Admin can override in special circumstances',
            
            // Additional rules
            'min_hours_before_start' => null, // No minimum restriction
            'max_hours_before_start' => null, // No maximum restriction
            'min_booking_amount' => null, // No minimum amount
            'max_booking_amount' => null, // No maximum amount
            
            'created_by' => 1, // Assume admin user ID is 1
            'updated_by' => null,
        ]);

        $this->command->info('✓ Default cancellation policy created successfully!');
        $this->command->newLine();
        $this->command->info('Policy Details:');
        $this->command->info('  - 7+ days before: 100% refund, 0% fee');
        $this->command->info('  - 3-7 days before: 50% refund, 10% fee');
        $this->command->info('  - 1-3 days before: 25% refund, 15% fee');
        $this->command->info('  - < 24 hours or after campaign start: No refund');
    }
}
