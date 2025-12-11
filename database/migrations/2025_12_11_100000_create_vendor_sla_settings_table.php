<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * This migration creates the vendor_sla_settings table to configure
 * Service Level Agreement (SLA) requirements for vendors:
 * - Enquiry acceptance deadline
 * - Quote submission deadline
 * - Response time thresholds
 * - Penalty configurations
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_sla_settings', function (Blueprint $table) {
            $table->id();
            
            // Setting Identification
            $table->string('name')->unique()->comment('Setting name (e.g., default, premium, standard)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            
            // SLA Timeframes (in hours)
            $table->integer('enquiry_acceptance_hours')->default(24)
                ->comment('Vendor must accept/acknowledge enquiry within X hours');
            $table->integer('quote_submission_hours')->default(48)
                ->comment('Vendor must submit quote within X hours after accepting enquiry');
            $table->integer('quote_revision_hours')->default(24)
                ->comment('Vendor must submit quote revision within X hours');
            $table->integer('enquiry_response_hours')->default(72)
                ->comment('Total time from enquiry to quote submission');
            
            // Warning Thresholds (percentage of deadline)
            $table->integer('warning_threshold_percentage')->default(75)
                ->comment('Send warning when X% of deadline reached');
            
            // Grace Period (in hours)
            $table->integer('grace_period_hours')->default(2)
                ->comment('Grace period before marking as violation');
            
            // Violation Penalties (impact on reliability score)
            $table->decimal('minor_violation_penalty', 5, 2)->default(1.00)
                ->comment('Points deducted for minor violations (within grace period)');
            $table->decimal('major_violation_penalty', 5, 2)->default(5.00)
                ->comment('Points deducted for major violations (beyond grace period)');
            $table->decimal('critical_violation_penalty', 5, 2)->default(10.00)
                ->comment('Points deducted for critical violations (repeated or severe)');
            
            // Auto-Actions
            $table->boolean('auto_mark_violated')->default(true)
                ->comment('Automatically mark as violated when deadline passes');
            $table->boolean('auto_notify_vendor')->default(true)
                ->comment('Send notifications to vendor about approaching deadlines');
            $table->boolean('auto_notify_admin')->default(true)
                ->comment('Send notifications to admin about violations');
            $table->boolean('auto_escalate_critical')->default(true)
                ->comment('Auto-escalate critical violations to admin');
            
            // Violation Tracking
            $table->integer('max_violations_per_month')->default(3)
                ->comment('Max violations before escalation');
            $table->integer('critical_violation_threshold')->default(5)
                ->comment('Violations count for critical status');
            
            // Recovery Settings
            $table->integer('reliability_recovery_days')->default(30)
                ->comment('Days for reliability score to recover (if no violations)');
            $table->decimal('recovery_rate_per_day', 5, 2)->default(0.50)
                ->comment('Points recovered per day with good performance');
            
            // Vendor Categories (can be overridden per vendor)
            $table->enum('applies_to', ['all', 'new', 'verified', 'premium'])
                ->default('all')
                ->comment('Which vendor categories this applies to');
            
            // Business Hours Configuration
            $table->boolean('count_business_hours_only')->default(false)
                ->comment('Count only business hours or 24/7');
            $table->json('business_hours')->nullable()
                ->comment('Business hours config: {days: [1-7], start: "09:00", end: "18:00"}');
            $table->json('excluded_days')->nullable()
                ->comment('Holidays/weekends: ["2025-12-25", "2025-01-01"]');
            
            // Metadata
            $table->json('metadata')->nullable()->comment('Additional configuration');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('name');
            $table->index('is_active');
            $table->index('is_default');
            $table->index('applies_to');
        });
        
        // Insert default SLA settings
        DB::table('vendor_sla_settings')->insert([
            [
                'name' => 'default',
                'description' => 'Default SLA settings for all vendors',
                'is_active' => true,
                'is_default' => true,
                'enquiry_acceptance_hours' => 24,
                'quote_submission_hours' => 48,
                'quote_revision_hours' => 24,
                'enquiry_response_hours' => 72,
                'warning_threshold_percentage' => 75,
                'grace_period_hours' => 2,
                'minor_violation_penalty' => 1.00,
                'major_violation_penalty' => 5.00,
                'critical_violation_penalty' => 10.00,
                'auto_mark_violated' => true,
                'auto_notify_vendor' => true,
                'auto_notify_admin' => true,
                'auto_escalate_critical' => true,
                'max_violations_per_month' => 3,
                'critical_violation_threshold' => 5,
                'reliability_recovery_days' => 30,
                'recovery_rate_per_day' => 0.50,
                'applies_to' => 'all',
                'count_business_hours_only' => false,
                'business_hours' => null,
                'excluded_days' => null,
                'metadata' => json_encode(['created_by' => 'system']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'premium',
                'description' => 'Premium SLA for verified/premium vendors - stricter requirements',
                'is_active' => true,
                'is_default' => false,
                'enquiry_acceptance_hours' => 12,
                'quote_submission_hours' => 24,
                'quote_revision_hours' => 12,
                'enquiry_response_hours' => 36,
                'warning_threshold_percentage' => 80,
                'grace_period_hours' => 1,
                'minor_violation_penalty' => 2.00,
                'major_violation_penalty' => 7.50,
                'critical_violation_penalty' => 15.00,
                'auto_mark_violated' => true,
                'auto_notify_vendor' => true,
                'auto_notify_admin' => true,
                'auto_escalate_critical' => true,
                'max_violations_per_month' => 2,
                'critical_violation_threshold' => 3,
                'reliability_recovery_days' => 45,
                'recovery_rate_per_day' => 0.33,
                'applies_to' => 'premium',
                'count_business_hours_only' => false,
                'business_hours' => null,
                'excluded_days' => null,
                'metadata' => json_encode(['created_by' => 'system']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'relaxed',
                'description' => 'Relaxed SLA for new vendors - more lenient requirements',
                'is_active' => true,
                'is_default' => false,
                'enquiry_acceptance_hours' => 48,
                'quote_submission_hours' => 96,
                'quote_revision_hours' => 48,
                'enquiry_response_hours' => 120,
                'warning_threshold_percentage' => 70,
                'grace_period_hours' => 4,
                'minor_violation_penalty' => 0.50,
                'major_violation_penalty' => 2.50,
                'critical_violation_penalty' => 5.00,
                'auto_mark_violated' => true,
                'auto_notify_vendor' => true,
                'auto_notify_admin' => false,
                'auto_escalate_critical' => true,
                'max_violations_per_month' => 5,
                'critical_violation_threshold' => 10,
                'reliability_recovery_days' => 20,
                'recovery_rate_per_day' => 0.75,
                'applies_to' => 'new',
                'count_business_hours_only' => false,
                'business_hours' => null,
                'excluded_days' => null,
                'metadata' => json_encode(['created_by' => 'system']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_sla_settings');
    }
};
