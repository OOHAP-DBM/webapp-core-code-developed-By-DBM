<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROMPT 68: Vendor SLA Violations Tracking
 * 
 * This migration creates the vendor_sla_violations table to track all
 * SLA violations by vendors, including impact on reliability scores.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_sla_violations', function (Blueprint $table) {
            $table->id();
            
            // Vendor & SLA Settings
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sla_setting_id')->constrained('vendor_sla_settings')->onDelete('cascade');
            
            // Related Entity (what violated)
            $table->string('violatable_type')->comment('QuoteRequest or VendorQuote');
            $table->unsignedBigInteger('violatable_id');
            $table->index(['violatable_type', 'violatable_id'], 'violatable_index');
            
            // Violation Details
            $table->enum('violation_type', [
                'enquiry_acceptance_late',
                'quote_submission_late',
                'quote_revision_late',
                'no_response',
                'missed_deadline'
            ])->comment('Type of SLA violation');
            
            $table->enum('severity', ['minor', 'major', 'critical'])
                ->default('minor')
                ->comment('Severity based on delay and repetition');
            
            // Timing Information
            $table->timestamp('deadline')->comment('Original SLA deadline');
            $table->timestamp('actual_time')->nullable()->comment('When action was actually taken');
            $table->integer('delay_hours')->default(0)->comment('Hours delayed');
            $table->integer('delay_minutes')->default(0)->comment('Minutes delayed (for precision)');
            
            // SLA Thresholds at Time of Violation
            $table->integer('expected_hours')->comment('Expected completion time in hours');
            $table->integer('grace_period_hours')->default(0)->comment('Grace period at violation time');
            
            // Penalty & Impact
            $table->decimal('penalty_points', 5, 2)->default(0.00)
                ->comment('Points deducted from reliability score');
            $table->decimal('reliability_score_before', 5, 2)->nullable()
                ->comment('Vendor reliability score before penalty');
            $table->decimal('reliability_score_after', 5, 2)->nullable()
                ->comment('Vendor reliability score after penalty');
            
            // Status & Resolution
            $table->enum('status', [
                'pending',
                'confirmed',
                'disputed',
                'resolved',
                'waived',
                'escalated'
            ])->default('pending');
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('waived_at')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->comment('Admin who waived');
            $table->text('waiver_reason')->nullable();
            
            // Vendor Response
            $table->text('vendor_explanation')->nullable();
            $table->timestamp('vendor_responded_at')->nullable();
            $table->enum('vendor_dispute_status', [
                'not_disputed',
                'disputed',
                'accepted',
                'rejected'
            ])->default('not_disputed');
            
            // Admin Review
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Auto-Actions Taken
            $table->boolean('auto_notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->boolean('escalated_to_admin')->default(false);
            $table->timestamp('escalated_at')->nullable();
            
            // Violation Count Context
            $table->integer('violation_count_this_month')->default(0)
                ->comment('Vendor violations count in current month');
            $table->integer('violation_count_total')->default(0)
                ->comment('Total violations by vendor');
            
            // Business Context
            $table->decimal('business_impact', 12, 2)->nullable()
                ->comment('Estimated business impact (e.g., lost opportunity value)');
            $table->text('customer_impact_notes')->nullable();
            
            // Metadata
            $table->json('violation_context')->nullable()
                ->comment('Snapshot: enquiry/quote details, customer info, etc.');
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('vendor_id');
            $table->index('violation_type');
            $table->index('severity');
            $table->index('status');
            $table->index('deadline');
            $table->index('created_at');
            $table->index(['vendor_id', 'violation_type']);
            $table->index(['vendor_id', 'created_at']);
            $table->index(['vendor_id', 'severity']);
            $table->index(['vendor_id', 'status']);
        });
        
        // Add SLA tracking fields to quote_requests table
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->timestamp('vendor_notified_at')->nullable()->after('published_at')
                ->comment('When vendor(s) were notified about the RFP');
            $table->timestamp('sla_acceptance_deadline')->nullable()->after('vendor_notified_at')
                ->comment('SLA deadline for vendor to accept/acknowledge');
            $table->timestamp('sla_quote_deadline')->nullable()->after('sla_acceptance_deadline')
                ->comment('SLA deadline for vendor to submit quote');
            $table->timestamp('vendor_accepted_at')->nullable()->after('sla_quote_deadline')
                ->comment('When vendor accepted/acknowledged the enquiry');
            $table->boolean('sla_acceptance_violated')->default(false)->after('vendor_accepted_at');
            $table->boolean('sla_quote_violated')->default(false)->after('sla_acceptance_violated');
            $table->foreignId('sla_setting_id')->nullable()->after('sla_quote_violated')
                ->constrained('vendor_sla_settings')->nullOnDelete();
            
            // Indexes
            $table->index('sla_acceptance_deadline');
            $table->index('sla_quote_deadline');
            $table->index('vendor_accepted_at');
            $table->index(['sla_acceptance_violated', 'sla_quote_violated']);
        });
        
        // Add SLA tracking fields to vendor_quotes table
        Schema::table('vendor_quotes', function (Blueprint $table) {
            $table->timestamp('sla_submission_deadline')->nullable()->after('sent_at')
                ->comment('SLA deadline for submitting this quote');
            $table->boolean('sla_violated')->default(false)->after('sla_submission_deadline');
            $table->timestamp('sla_violation_time')->nullable()->after('sla_violated')
                ->comment('When SLA violation was detected');
            $table->foreignId('sla_setting_id')->nullable()->after('sla_violation_time')
                ->constrained('vendor_sla_settings')->nullOnDelete();
            
            // Indexes
            $table->index('sla_submission_deadline');
            $table->index('sla_violated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from vendor_quotes
        Schema::table('vendor_quotes', function (Blueprint $table) {
            $table->dropForeign(['sla_setting_id']);
            $table->dropIndex(['sla_submission_deadline']);
            $table->dropIndex(['sla_violated']);
            $table->dropColumn([
                'sla_submission_deadline',
                'sla_violated',
                'sla_violation_time',
                'sla_setting_id'
            ]);
        });
        
        // Remove fields from quote_requests
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropForeign(['sla_setting_id']);
            $table->dropIndex(['sla_acceptance_deadline']);
            $table->dropIndex(['sla_quote_deadline']);
            $table->dropIndex(['vendor_accepted_at']);
            $table->dropIndex(['sla_acceptance_violated', 'sla_quote_violated']);
            $table->dropColumn([
                'vendor_notified_at',
                'sla_acceptance_deadline',
                'sla_quote_deadline',
                'vendor_accepted_at',
                'sla_acceptance_violated',
                'sla_quote_violated',
                'sla_setting_id'
            ]);
        });
        
        Schema::dropIfExists('vendor_sla_violations');
    }
};
