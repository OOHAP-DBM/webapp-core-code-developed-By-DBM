<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          if (Schema::hasTable('dooh_screens') && Schema::hasColumn('dooh_screens', 'display_price_per_30s')) {
            Schema::table('dooh_screens', function (Blueprint $table) {
                $table->renameColumn('display_price_per_30s', 'screen_run_time');
            });
        }

        // Step 2: Change column type if exists
        if (Schema::hasTable('dooh_screens') && Schema::hasColumn('dooh_screens', 'screen_run_time')) {
            Schema::table('dooh_screens', function (Blueprint $table) {
                $table->decimal('screen_run_time', 5, 2)
                      ->nullable()
                      ->comment('Screen run time in hours (max 24)')
                      ->change();
            });
        }

        if (Schema::hasTable('hoardings') && Schema::hasColumn('hoardings', 'title')) {
            Schema::table('hoardings', function (Blueprint $table) {
                $table->dropUnique('hoardings_title_unique'); // Remove UNIQUE constraint
            });
        }
    }

    public function down(): void
    {
        // Step 1: Revert type change
        if (Schema::hasTable('dooh_screens') && Schema::hasColumn('dooh_screens', 'screen_run_time')) {
            Schema::table('dooh_screens', function (Blueprint $table) {
                $table->decimal('screen_run_time', 12, 2)
                      ->nullable()
                      ->change();
            });
        }

        // Step 2: Rename column back
        if (Schema::hasTable('dooh_screens') && Schema::hasColumn('dooh_screens', 'screen_run_time')) {
            Schema::table('dooh_screens', function (Blueprint $table) {
                $table->renameColumn('screen_run_time', 'display_price_per_30s');
            });
        }
        if (Schema::hasTable('hoardings') && Schema::hasColumn('hoardings', 'title')) {
            Schema::table('hoardings', function (Blueprint $table) {
                $table->unique('title');
            });
        }
    }
};
