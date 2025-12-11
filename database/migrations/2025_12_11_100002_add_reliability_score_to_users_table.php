<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * PROMPT 68: Add vendor reliability score to users table
 * 
 * Adds reliability scoring fields to users table for vendor performance tracking
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reliability Score (0-100)
            $table->decimal('reliability_score', 5, 2)->default(100.00)->after('email_verified_at')
                ->comment('Vendor reliability score (0-100), starts at 100');
            
            // Score Components
            $table->integer('sla_violations_count')->default(0)->after('reliability_score')
                ->comment('Total SLA violations');
            $table->integer('sla_violations_this_month')->default(0)->after('sla_violations_count')
                ->comment('SLA violations in current month');
            $table->decimal('total_penalty_points', 8, 2)->default(0.00)->after('sla_violations_this_month')
                ->comment('Total penalty points accumulated');
            
            // Performance Metrics
            $table->integer('enquiries_accepted_count')->default(0)->after('total_penalty_points')
                ->comment('Total enquiries accepted');
            $table->integer('quotes_submitted_count')->default(0)->after('enquiries_accepted_count')
                ->comment('Total quotes submitted');
            $table->integer('quotes_accepted_count')->default(0)->after('quotes_submitted_count')
                ->comment('Total quotes accepted by customers');
            
            // Response Times (average in hours)
            $table->decimal('avg_acceptance_time_hours', 8, 2)->nullable()->after('quotes_accepted_count')
                ->comment('Average time to accept enquiries');
            $table->decimal('avg_quote_time_hours', 8, 2)->nullable()->after('avg_acceptance_time_hours')
                ->comment('Average time to submit quotes');
            
            // Success Rates (percentages)
            $table->decimal('on_time_acceptance_rate', 5, 2)->default(100.00)->after('avg_quote_time_hours')
                ->comment('Percentage of enquiries accepted on time');
            $table->decimal('on_time_quote_rate', 5, 2)->default(100.00)->after('on_time_acceptance_rate')
                ->comment('Percentage of quotes submitted on time');
            $table->decimal('quote_win_rate', 5, 2)->default(0.00)->after('on_time_quote_rate')
                ->comment('Percentage of quotes that win');
            
            // Status & Tracking
            $table->timestamp('last_sla_violation_at')->nullable()->after('quote_win_rate')
                ->comment('Last SLA violation timestamp');
            $table->timestamp('last_score_update_at')->nullable()->after('last_sla_violation_at')
                ->comment('Last reliability score update');
            $table->timestamp('last_recovery_at')->nullable()->after('last_score_update_at')
                ->comment('Last score recovery timestamp');
            
            // Vendor SLA Setting Override
            $table->foreignId('vendor_sla_setting_id')->nullable()->after('last_recovery_at')
                ->constrained('vendor_sla_settings')->nullOnDelete()
                ->comment('Custom SLA setting for this vendor (overrides default)');
            
            // Performance Tier (calculated)
            $table->enum('reliability_tier', ['excellent', 'good', 'average', 'poor', 'critical'])
                ->default('excellent')->after('vendor_sla_setting_id')
                ->comment('Performance tier based on reliability score');
            
            // Indexes
            $table->index('reliability_score');
            $table->index('reliability_tier');
            $table->index('sla_violations_count');
            $table->index('last_sla_violation_at');
        });
        
        // Initialize reliability scores for existing vendors
        DB::statement("
            UPDATE users 
            SET reliability_score = 100.00,
                reliability_tier = 'excellent',
                last_score_update_at = NOW()
            WHERE id IN (
                SELECT DISTINCT vendor_id 
                FROM hoardings 
                WHERE vendor_id IS NOT NULL
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vendor_sla_setting_id']);
            $table->dropIndex(['reliability_score']);
            $table->dropIndex(['reliability_tier']);
            $table->dropIndex(['sla_violations_count']);
            $table->dropIndex(['last_sla_violation_at']);
            
            $table->dropColumn([
                'reliability_score',
                'sla_violations_count',
                'sla_violations_this_month',
                'total_penalty_points',
                'enquiries_accepted_count',
                'quotes_submitted_count',
                'quotes_accepted_count',
                'avg_acceptance_time_hours',
                'avg_quote_time_hours',
                'on_time_acceptance_rate',
                'on_time_quote_rate',
                'quote_win_rate',
                'last_sla_violation_at',
                'last_score_update_at',
                'last_recovery_at',
                'vendor_sla_setting_id',
                'reliability_tier',
            ]);
        });
    }
};
