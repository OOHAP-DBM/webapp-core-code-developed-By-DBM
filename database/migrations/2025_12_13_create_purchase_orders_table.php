<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PROMPT 107: PO Auto-Generation System
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('enquiry_id')->constrained()->onDelete('cascade');
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            
            // PO Details
            $table->json('items')->nullable(); // Line items from quotation
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            
            // Payment details (from quotation)
            $table->boolean('has_milestones')->default(false);
            $table->string('payment_mode')->nullable(); // full, milestone
            $table->integer('milestone_count')->nullable();
            $table->json('milestone_summary')->nullable();
            
            // PDF and attachments
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'sent', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by')->nullable(); // user_id or 'system'
            $table->text('cancellation_reason')->nullable();
            
            // Approval workflow
            $table->timestamp('customer_approved_at')->nullable();
            $table->timestamp('vendor_acknowledged_at')->nullable();
            
            // Thread integration
            $table->foreignId('thread_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('thread_message_id')->nullable()->constrained()->onDelete('set null');
            
            // Additional fields
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('po_number');
            $table->index('quotation_id');
            $table->index('customer_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('sent_at');
            $table->index(['quotation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
