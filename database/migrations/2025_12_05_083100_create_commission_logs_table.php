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
        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            
            // Commission breakdown
            $table->decimal('gross_amount', 10, 2)->comment('Total booking amount');
            $table->decimal('admin_commission', 10, 2)->comment('Platform commission earned');
            $table->decimal('vendor_payout', 10, 2)->comment('Amount payable to vendor');
            $table->decimal('pg_fee', 10, 2)->default(0)->comment('Payment gateway fees');
            $table->decimal('tax', 10, 2)->default(0)->comment('Tax on commission (if applicable)');
            
            // Commission calculation metadata
            $table->decimal('commission_rate', 5, 2)->comment('Commission percentage applied (e.g., 15.00 for 15%)');
            $table->string('commission_type', 50)->default('percentage')->comment('percentage, fixed, tiered, etc.');
            
            // References
            $table->foreignId('booking_payment_id')->nullable()->constrained('booking_payments')->onDelete('set null');
            
            // Calculation snapshot (for audit)
            $table->json('calculation_snapshot')->nullable()->comment('Full calculation breakdown for audit trail');
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('booking_id');
            $table->index('booking_payment_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_logs');
    }
};
