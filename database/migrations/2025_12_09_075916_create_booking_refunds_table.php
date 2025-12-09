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
        Schema::create('booking_refunds', function (Blueprint $table) {
            $table->id();
            
            // Booking reference (polymorphic for OOH, DOOH, POS)
            $table->morphs('booking'); // booking_id, booking_type
            $table->foreignId('cancellation_policy_id')->nullable()->constrained('cancellation_policies')->onDelete('set null');
            
            // Refund details
            $table->string('refund_reference')->unique()->comment('Internal tracking number');
            $table->enum('refund_type', ['full', 'partial'])->default('full');
            $table->enum('refund_method', ['auto', 'manual'])->default('auto');
            
            // Amounts
            $table->decimal('booking_amount', 12, 2)->comment('Original booking amount');
            $table->decimal('refundable_amount', 12, 2)->comment('Amount eligible for refund');
            $table->decimal('customer_fee', 12, 2)->default(0)->comment('Fee charged to customer');
            $table->decimal('vendor_penalty', 12, 2)->default(0)->comment('Penalty to vendor');
            $table->decimal('refund_amount', 12, 2)->comment('Final refund to customer');
            
            // Payment gateway details
            $table->string('pg_refund_id')->nullable()->comment('Razorpay refund ID');
            $table->string('pg_payment_id')->nullable()->comment('Original payment ID');
            $table->enum('pg_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('pg_error')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'approved', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('initiated_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Cancellation context
            $table->enum('cancelled_by_role', ['customer', 'vendor', 'admin'])->default('customer');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason');
            $table->integer('hours_before_start')->comment('Hours before booking start when cancelled');
            
            // Policy snapshot
            $table->json('policy_snapshot')->nullable()->comment('Snapshot of applied policy');
            $table->json('calculation_details')->nullable()->comment('Refund calculation breakdown');
            
            // Admin actions
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->boolean('admin_override')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index('refund_reference');
            $table->index('status');
            $table->index('pg_status');
            $table->index('cancelled_by_role');
            $table->index(['booking_id', 'booking_type']);
            $table->index('initiated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_refunds');
    }
};
