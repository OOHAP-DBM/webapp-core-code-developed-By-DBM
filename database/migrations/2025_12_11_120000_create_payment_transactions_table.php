<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PROMPT 69: Payment Gateway Integration Wrapper
     * 
     * Unified payment transaction tracking for all gateways
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            
            // Gateway Information
            $table->string('gateway')->index(); // razorpay, stripe, paypal, etc.
            $table->string('transaction_type')->index(); // order, payment, refund, capture, void
            
            // External IDs
            $table->string('gateway_order_id')->nullable()->index();
            $table->string('gateway_payment_id')->nullable()->index();
            $table->string('gateway_refund_id')->nullable()->index();
            $table->string('gateway_customer_id')->nullable()->index();
            
            // Internal References
            $table->string('reference_type')->nullable()->index(); // Booking, Invoice, DOOHBooking, etc.
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            
            // Amount Details
            $table->decimal('amount', 15, 2); // Amount in base currency (INR, USD)
            $table->string('currency', 3)->default('INR');
            $table->integer('amount_in_smallest_unit'); // Paise, cents, etc.
            $table->decimal('fee', 15, 2)->nullable(); // Gateway fee
            $table->decimal('tax', 15, 2)->nullable(); // Tax on fee
            $table->decimal('net_amount', 15, 2)->nullable(); // Amount - fee - tax
            
            // Status and State
            $table->string('status')->index(); 
            // created, authorized, captured, failed, refunded, partially_refunded, voided, pending
            $table->string('payment_method')->nullable(); // card, upi, netbanking, wallet
            $table->string('payment_method_details')->nullable(); // Last 4 digits, UPI ID, etc.
            
            // Capture Details (for manual capture)
            $table->boolean('manual_capture')->default(false);
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('capture_expires_at')->nullable();
            $table->decimal('captured_amount', 15, 2)->nullable();
            
            // Refund Details
            $table->decimal('refunded_amount', 15, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_status')->nullable(); // pending, processed, failed
            $table->text('refund_reason')->nullable();
            
            // Webhook Tracking
            $table->boolean('webhook_received')->default(false);
            $table->timestamp('webhook_received_at')->nullable();
            $table->string('webhook_event_type')->nullable();
            $table->json('webhook_payload')->nullable();
            
            // Request/Response Logging
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('metadata')->nullable(); // Additional custom data
            
            // Error Handling
            $table->text('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->text('error_description')->nullable();
            $table->json('error_details')->nullable();
            
            // Customer Information (snapshot)
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Receipt and Notes
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('description')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            
            // Composite Indexes
            $table->index(['reference_type', 'reference_id']);
            $table->index(['gateway', 'status']);
            $table->index(['gateway', 'transaction_type']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
