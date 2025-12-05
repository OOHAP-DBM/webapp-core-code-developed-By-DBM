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
            // Idempotency field for CaptureExpiredHoldsJob
            $table->timestamp('capture_attempted_at')->nullable()->after('payment_failed_at');
            
            // Index for job query optimization
            $table->index(['status', 'payment_status', 'hold_expiry_at', 'capture_attempted_at'], 'idx_capture_job');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_capture_job');
            $table->dropColumn('capture_attempted_at');
        });
    }
};
