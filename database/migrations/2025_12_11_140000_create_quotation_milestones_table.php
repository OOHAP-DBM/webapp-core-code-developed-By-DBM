<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PROMPT 70: Vendor-Controlled Milestone Payment Logic
     * 
     * Milestones are created ONLY by vendor during quotation generation.
     * Used for quotation-based bookings to split payments into phases.
     */
    public function up(): void
    {
        Schema::create('quotation_milestones', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            
            // Milestone details
            $table->string('title'); // e.g., "Advance Payment", "Design Approval", "Final Payment"
            $table->text('description')->nullable();
            $table->integer('sequence_no')->unsigned(); // 1, 2, 3... (payment order)
            
            // Amount calculation
            $table->enum('amount_type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('amount', 15, 2); // Fixed amount OR percentage value
            $table->decimal('calculated_amount', 15, 2)->nullable(); // Actual amount after calculation
            
            // Payment tracking
            $table->enum('status', ['pending', 'due', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable(); // When this milestone payment is due
            $table->timestamp('paid_at')->nullable();
            
            // Payment references
            $table->string('invoice_number')->nullable(); // Generated invoice for this milestone
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions');
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            
            // Metadata
            $table->json('payment_details')->nullable(); // Store payment method, fees, etc.
            $table->text('vendor_notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['quotation_id', 'sequence_no']);
            $table->index(['quotation_id', 'status']);
            $table->index('due_date');
            $table->index('status');
        });
        
        // Add milestone support fields to quotations table
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('has_milestones')->default(false)->after('grand_total');
            $table->enum('payment_mode', ['full', 'milestone'])->default('full')->after('has_milestones');
            $table->integer('milestone_count')->unsigned()->default(0)->after('payment_mode');
            $table->json('milestone_summary')->nullable()->after('milestone_count');
        });
        
        // Add milestone support fields to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_mode', ['full', 'milestone'])->default('full')->after('payment_status');
            $table->integer('milestone_total')->unsigned()->default(0)->after('payment_mode');
            $table->integer('milestone_paid')->unsigned()->default(0)->after('milestone_total');
            $table->decimal('milestone_amount_paid', 15, 2)->default(0)->after('milestone_paid');
            $table->decimal('milestone_amount_remaining', 15, 2)->default(0)->after('milestone_amount_paid');
            $table->foreignId('current_milestone_id')->nullable()->constrained('quotation_milestones')->after('milestone_amount_remaining');
            $table->timestamp('all_milestones_paid_at')->nullable()->after('current_milestone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['current_milestone_id']);
            $table->dropColumn([
                'payment_mode',
                'milestone_total',
                'milestone_paid',
                'milestone_amount_paid',
                'milestone_amount_remaining',
                'current_milestone_id',
                'all_milestones_paid_at',
            ]);
        });
        
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'has_milestones',
                'payment_mode',
                'milestone_count',
                'milestone_summary',
            ]);
        });
        
        Schema::dropIfExists('quotation_milestones');
    }
};
