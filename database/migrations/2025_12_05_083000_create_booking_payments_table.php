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
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            
            // Payment amounts breakdown
            $table->decimal('gross_amount', 10, 2)->comment('Total booking amount (customer paid)');
            $table->decimal('admin_commission_amount', 10, 2)->default(0)->comment('Platform commission');
            $table->decimal('vendor_payout_amount', 10, 2)->comment('Amount to be paid to vendor');
            $table->decimal('pg_fee_amount', 10, 2)->default(0)->comment('Payment gateway fees (Razorpay)');
            
            // Razorpay references
            $table->string('razorpay_payment_id', 100)->nullable();
            $table->string('razorpay_order_id', 100)->nullable();
            $table->json('razorpay_transfer_ids')->nullable()->comment('Array of Razorpay transfer IDs for vendor payouts');
            
            // Vendor payout tracking
            $table->enum('vendor_payout_status', ['pending', 'processing', 'completed', 'failed', 'on_hold'])->default('pending');
            $table->string('payout_mode', 50)->nullable()->comment('bank_transfer, razorpay_transfer, manual, etc.');
            $table->string('payout_reference', 100)->nullable()->comment('Bank/transfer reference number');
            $table->timestamp('paid_at')->nullable()->comment('When vendor payout was completed');
            
            // Overall payment status
            $table->enum('status', ['pending', 'captured', 'refunded', 'partially_refunded', 'failed'])->default('pending');
            
            // Metadata
            $table->json('metadata')->nullable()->comment('Additional payment metadata (bank details, notes, etc.)');
            
            $table->timestamps();
            
            // Indexes
            $table->index('booking_id');
            $table->index('razorpay_payment_id');
            $table->index('razorpay_order_id');
            $table->index('vendor_payout_status');
            $table->index('status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
