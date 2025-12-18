<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->float('reliability_score')->nullable();
            $table->string('reliability_tier')->nullable();
            $table->unsignedInteger('sla_violations_count')->default(0);
            $table->unsignedInteger('sla_violations_this_month')->default(0);
            $table->unsignedInteger('total_penalty_points')->default(0);
            $table->unsignedInteger('enquiries_accepted_count')->default(0);
            $table->unsignedInteger('quotes_submitted_count')->default(0);
            $table->unsignedInteger('quotes_accepted_count')->default(0);
            $table->float('avg_acceptance_time_hours')->nullable();
            $table->float('avg_quote_time_hours')->nullable();
            $table->float('on_time_acceptance_rate')->nullable();
            $table->float('on_time_quote_rate')->nullable();
            $table->float('quote_win_rate')->nullable();
            $table->timestamp('last_sla_violation_at')->nullable();
            $table->timestamp('last_score_update_at')->nullable();
            $table->timestamp('last_recovery_at')->nullable();
            $table->unsignedBigInteger('vendor_sla_setting_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendor_profiles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_metrics');
    }
};
