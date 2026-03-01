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
        Schema::table('pos_booking_hoardings', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_slots_per_day')
                  ->nullable()
                  ->after('duration_days')
                  ->comment('DOOH only: number of ad slots per day booked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_booking_hoardings', function (Blueprint $table) {
             $table->dropColumn('total_slots_per_day');
        });
    }
};
