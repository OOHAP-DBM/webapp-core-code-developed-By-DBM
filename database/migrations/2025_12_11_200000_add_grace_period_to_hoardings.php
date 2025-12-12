<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
            // Grace period in days - vendor can set custom grace period
            // If NULL, system will use default from env (BOOKING_GRACE_PERIOD_DAYS)
            $table->integer('grace_period_days')
                ->nullable()
                ->after('enable_weekly_booking')
                ->comment('Minimum days required before campaign can start. NULL = use admin default');
            
            $table->index('grace_period_days');
        });

        // Update existing hoardings to use admin default (NULL means use default)
        DB::statement('UPDATE hoardings SET grace_period_days = NULL WHERE grace_period_days IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
            $table->dropIndex(['grace_period_days']);
            $table->dropColumn('grace_period_days');
        });
    }
};
