<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROMPT 58: Vendor Payout Request System
 * Tracks vendor payout requests with commission, adjustments, GST, and admin approval workflow
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            
            // Request identification
            $table->string('request_reference', 50)->unique()->comment('Auto-generated: PR-YYYYMMDD-XXXX');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            
            // Financial breakdown
            $table->decimal('booking_revenue', 12, 2)->comment('Total revenue from bookings');
            $table->decimal('commission_amount', 12, 2)->comment('Platform commission deducted');
            $table->decimal('commission_percentage', 5, 2)->comment('Commission rate applied (%)');
            $table->decimal('pg_fees', 12, 2)->default(0)->comment('Payment gateway fees deducted');
            $table->decimal('adjustment_amount', 12, 2)->default(0)->comment('Additional adjustments (+/-)');
            $table->string('adjustment_reason')->nullable()->comment('Reason for adjustment');
            $table->decimal('gst_amount', 12, 2)->default(0)->comment('GST/Tax amount');
            $table->decimal('gst_percentage', 5, 2)->default(0)->comment('GST rate (%)');
            $table->decimal('final_payout_amount', 12, 2)->comment('Final amount to be paid to vendor');
            
            // Period tracking
            $table->date('period_start')->comment('Payout period start date');
            $table->date('period_end')->comment('Payout period end date');
            $table->integer('bookings_count')->default(0)->comment('Number of bookings in period');
            
            // Workflow status
            $table->enum('status', [
                'draft',              // Being prepared
                'submitted',          // Submitted by vendor
                'pending_approval',   // Awaiting admin review
                'approved',           // Approved by admin
                'rejected',           // Rejected by admin
                'processing',         // Payment being processed
                'completed',          // Payment completed
                'failed',             // Payment failed
                'cancelled'           // Cancelled by vendor
            ])->default('draft');
            
            // Vendor bank details (snapshot at request time)
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('upi_id')->nullable();
            
            // Payout details
            $table->enum('payout_mode', ['bank_transfer', 'razorpay_transfer', 'upi', 'cheque', 'manual'])->nullable();
            $table->string('payout_reference')->nullable()->comment('Bank/payment reference number');
            $table->text('payout_notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Approval workflow
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Settlement receipt
            $table->string('receipt_pdf_path')->nullable()->comment('Path to generated PDF receipt');
            $table->timestamp('receipt_generated_at')->nullable();
            
            // Metadata
            $table->json('booking_ids')->nullable()->comment('Array of booking_payment IDs included');
            $table->json('metadata')->nullable()->comment('Additional data (calculation snapshot)');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('vendor_id');
            $table->index('status');
            $table->index(['period_start', 'period_end']);
            $table->index('submitted_at');
            $table->index('approved_at');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};
