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
        // Vendor metrics are now in vendor_metrics table. No vendor columns in users.
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
