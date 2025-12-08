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
        Schema::table('bookings', function (Blueprint $table) {
            // Add refund tracking columns (only these are missing)
            if (!Schema::hasColumn('bookings', 'refund_id')) {
                $table->string('refund_id')->nullable()->after('payment_error_description');
            }
            if (!Schema::hasColumn('bookings', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->nullable()->after('refund_id');
            }
            if (!Schema::hasColumn('bookings', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            }
            if (!Schema::hasColumn('bookings', 'refund_error')) {
                $table->text('refund_error')->nullable()->after('refunded_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'refund_id',
                'refund_amount',
                'refunded_at',
                'refund_error',
            ]);
        });
    }
};
