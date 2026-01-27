<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing POS booking fields for hold/release workflow
     */
    public function up(): void
    {
        Schema::table('pos_bookings', function (Blueprint $table) {
            // Hold expiry tracking
            $table->timestamp('hold_expiry_at')->nullable()->after('payment_status');
            
            // Payment received timestamp
            $table->timestamp('payment_received_at')->nullable()->after('paid_amount');
            
            // Reminder tracking
            $table->integer('reminder_count')->default(0)->after('payment_received_at');
            $table->timestamp('last_reminder_at')->nullable()->after('reminder_count');
            
            // Track confirmation
            $table->timestamp('started_at')->nullable()->after('confirmed_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            
            // Add indexes for hold workflow
            $table->index('hold_expiry_at');
            $table->index(['payment_status', 'hold_expiry_at']);
            $table->index(['vendor_id', 'payment_status']);
        });
    }

    /**
     * Rollback the migration.
     */
    public function down(): void
    {
        Schema::table('pos_bookings', function (Blueprint $table) {
            $table->dropIndex(['hold_expiry_at']);
            $table->dropIndex(['payment_status', 'hold_expiry_at']);
            $table->dropIndex(['vendor_id', 'payment_status']);
            
            $table->dropColumn([
                'hold_expiry_at',
                'payment_received_at',
                'reminder_count',
                'last_reminder_at',
                'started_at',
                'completed_at',
            ]);
        });
    }
};
