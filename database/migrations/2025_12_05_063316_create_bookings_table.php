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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('duration_type', ['days', 'weeks', 'months'])->default('days');
            $table->integer('duration_days'); // Calculated duration in days
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'pending_payment_hold', 
                'payment_hold', 
                'confirmed', 
                'cancelled', 
                'refunded'
            ])->default('pending_payment_hold');
            $table->timestamp('hold_expiry_at')->nullable(); // 30 min hold
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->json('booking_snapshot')->nullable(); // Immutable snapshot from quotation
            $table->text('customer_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('quotation_id');
            $table->index('customer_id');
            $table->index('vendor_id');
            $table->index('hoarding_id');
            $table->index('status');
            $table->index('hold_expiry_at');
            $table->index(['hoarding_id', 'start_date', 'end_date']); // For availability checks
            $table->index('razorpay_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
