<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pos_bookings', function (Blueprint $table) {
              if (!Schema::hasColumn('pos_bookings', 'payment_received_at')) {
                $table->dateTime('payment_received_at')
                      ->nullable()
                      ->after('paid_amount');
            }

            if (!Schema::hasColumn('pos_bookings', 'hold_expiry_at')) {
                $table->dateTime('hold_expiry_at')
                      ->nullable()
                      ->after('payment_received_at');
            }
            if (!Schema::hasColumn('pos_bookings', 'reminder_count')) {
                $table->integer('reminder_count')
                      ->default(0)
                      ->after('hold_expiry_at');
            }
            
            if (!Schema::hasColumn('pos_bookings', 'last_reminder_at')) {
                $table->dateTime('last_reminder_at')
                      ->nullable()
                      ->after('reminder_count');
            }

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pos_bookings', function (Blueprint $table) {
           if (Schema::hasColumn('pos_bookings', 'payment_received_at')) {
                $table->dropColumn('payment_received_at');
            }

            if (Schema::hasColumn('pos_bookings', 'hold_expiry_at')) {
                $table->dropColumn('hold_expiry_at');
            }
            if (Schema::hasColumn('pos_bookings', 'reminder_count')) {
                $table->dropColumn('reminder_count');
            }
            if (Schema::hasColumn('pos_bookings', 'last_reminder_at')) {
                $table->dropColumn('last_reminder_at');
            }
        });
    }
};
