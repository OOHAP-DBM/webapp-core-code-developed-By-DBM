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
        Schema::create('vendor_ledgers', function (Blueprint $table) {
            $table->id();
            
            // Vendor Reference
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete()->comment('Vendor user ID');
            
            // Transaction Reference
            $table->string('transaction_reference', 50)->unique()->comment('e.g., TXN-XXXXXXXXXXXX');
            $table->enum('transaction_type', [
                'booking_earning',      // Credit from new booking
                'commission_deduction', // Debit for admin commission
                'payout',              // Debit for payout to vendor
                'refund_debit',        // Debit for customer refund
                'penalty',             // Debit for vendor cancellation penalty
                'adjustment',          // Manual adjustment by admin
                'hold_release',        // Release from hold when KYC complete
            ])->comment('Type of transaction');
            
            // Transaction Amounts
            $table->decimal('amount', 12, 2)->comment('Transaction amount (positive for credit, negative for debit)');
            $table->decimal('balance_before', 12, 2)->comment('Balance before this transaction');
            $table->decimal('balance_after', 12, 2)->comment('Balance after this transaction');
            
            // Related Entities (Polymorphic)
            $table->nullableMorphs('related', 'vendor_ledgers_related_index');
            $table->foreignId('booking_payment_id')->nullable()->constrained('booking_payments')->comment('Related booking payment');
            $table->foreignId('settlement_batch_id')->nullable()->constrained('settlement_batches')->comment('Related settlement batch');
            
            // Transaction Status
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');
            
            // KYC Tracking
            $table->boolean('kyc_verified_at_time')->default(false)->comment('Was KYC verified when transaction occurred');
            $table->string('kyc_status_snapshot')->nullable()->comment('KYC verification_status at time of transaction');
            $table->string('payout_status_snapshot')->nullable()->comment('Razorpay payout_status at time of transaction');
            
            // Hold Management
            $table->boolean('is_on_hold')->default(false)->comment('Transaction amount held due to incomplete KYC');
            $table->timestamp('hold_released_at')->nullable()->comment('When hold was released');
            $table->foreignId('hold_released_by')->nullable()->constrained('users')->comment('Admin who released hold');
            
            // Transaction Details
            $table->text('description')->nullable()->comment('Human-readable transaction description');
            $table->text('notes')->nullable()->comment('Admin or system notes');
            $table->json('metadata')->nullable()->comment('Additional transaction data');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('Who created this entry (admin for adjustments)');
            $table->timestamp('transaction_date')->useCurrent()->comment('When transaction occurred');
            
            $table->timestamps();
            
            // Indexes
            $table->index('vendor_id');
            $table->index('transaction_reference');
            $table->index('transaction_type');
            $table->index('status');
            $table->index('is_on_hold');
            $table->index('transaction_date');
            $table->index('booking_payment_id');
            $table->index('settlement_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_ledgers');
    }
};
