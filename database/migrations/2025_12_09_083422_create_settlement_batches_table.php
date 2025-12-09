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
        Schema::create('settlement_batches', function (Blueprint $table) {
            $table->id();
            
            // Batch Identification
            $table->string('batch_reference', 50)->unique()->comment('e.g., STL-YYYYMMDD-XXXX');
            $table->string('batch_name')->nullable()->comment('Optional batch name');
            $table->text('batch_description')->nullable();
            
            // Batch Period
            $table->date('period_start')->comment('Settlement period start date');
            $table->date('period_end')->comment('Settlement period end date');
            
            // Batch Status
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'processing', 'completed', 'failed', 'cancelled'])
                ->default('draft')
                ->comment('Batch lifecycle status');
            
            // Amounts Summary
            $table->decimal('total_bookings_amount', 12, 2)->default(0)->comment('Sum of all booking amounts in batch');
            $table->decimal('total_admin_commission', 12, 2)->default(0)->comment('Sum of admin commission');
            $table->decimal('total_vendor_payout', 12, 2)->default(0)->comment('Sum of vendor payouts');
            $table->decimal('total_pg_fees', 12, 2)->default(0)->comment('Sum of payment gateway fees');
            
            // Booking Counts
            $table->integer('total_bookings_count')->default(0)->comment('Number of bookings in batch');
            $table->integer('vendors_count')->default(0)->comment('Number of unique vendors');
            $table->integer('pending_kyc_count')->default(0)->comment('Vendors with incomplete KYC');
            
            // Approval Workflow
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('Admin who created batch');
            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('Admin who approved batch');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Processing Details
            $table->timestamp('processed_at')->nullable()->comment('When batch processing started');
            $table->timestamp('completed_at')->nullable()->comment('When all payouts completed');
            $table->json('processing_errors')->nullable()->comment('Errors during processing');
            
            // Split Configuration Snapshot
            $table->json('split_config')->nullable()->comment('Commission/split rules used for this batch');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('batch_reference');
            $table->index('status');
            $table->index(['period_start', 'period_end']);
            $table->index('created_by');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_batches');
    }
};
