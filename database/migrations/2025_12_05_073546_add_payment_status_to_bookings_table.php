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
            // Payment status tracking for Razorpay webhooks
            $table->enum('payment_status', [
                'pending',      // Initial state - no payment attempted
                'authorized',   // Payment authorized via webhook (manual capture)
                'captured',     // Payment captured and funds transferred
                'failed',       // Payment authorization failed
                'refunded',     // Payment refunded (full or partial)
                'expired'       // Authorization expired (30 min timeout)
            ])->default('pending')->after('status');

            // Payment failure tracking
            $table->string('payment_error_code', 100)->nullable()->after('razorpay_payment_id');
            $table->text('payment_error_description')->nullable()->after('payment_error_code');
            $table->timestamp('payment_authorized_at')->nullable()->after('payment_error_description');
            $table->timestamp('payment_captured_at')->nullable()->after('payment_authorized_at');
            $table->timestamp('payment_failed_at')->nullable()->after('payment_captured_at');

            // Index for filtering by payment status
            $table->index('payment_status');
            $table->index(['payment_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['payment_status', 'created_at']);
            $table->dropColumn([
                'payment_status',
                'payment_error_code',
                'payment_error_description',
                'payment_authorized_at',
                'payment_captured_at',
                'payment_failed_at'
            ]);
        });
    }
};
