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
        Schema::create('pos_bookings', function (Blueprint $table) {
            $table->id();
            
            // Vendor who created the POS booking
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            
            // Customer details (can be guest, not requiring user_id)
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->text('customer_address')->nullable();
            $table->string('customer_gstin')->nullable();
            
            // Hoarding/DOOH details
            $table->enum('booking_type', ['ooh', 'dooh'])->default('ooh');
            $table->foreignId('hoarding_id')->nullable()->constrained('hoardings')->onDelete('cascade');
            $table->foreignId('dooh_slot_id')->nullable(); // For future DOOH implementation
            
            // Booking dates
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('duration_type', ['days', 'weeks', 'months'])->default('days');
            $table->integer('duration_days');
            
            // Pricing
            $table->decimal('base_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            
            // Payment details
            $table->enum('payment_mode', ['cash', 'credit_note', 'online', 'bank_transfer', 'cheque'])->default('cash');
            $table->enum('payment_status', ['paid', 'unpaid', 'partial', 'credit'])->default('unpaid');
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('payment_reference')->nullable();
            $table->text('payment_notes')->nullable();
            
            // Credit note details
            $table->string('credit_note_number')->nullable()->unique();
            $table->date('credit_note_date')->nullable();
            $table->date('credit_note_due_date')->nullable();
            $table->enum('credit_note_status', ['active', 'cancelled', 'settled'])->nullable();
            
            // Booking status
            $table->enum('status', [
                'draft',
                'confirmed',
                'active',
                'completed',
                'cancelled'
            ])->default('draft');
            
            // Invoice details
            $table->string('invoice_number')->nullable()->unique();
            $table->date('invoice_date')->nullable();
            $table->string('invoice_path')->nullable();
            
            // Approval & Auto-processing
            $table->boolean('auto_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Additional data
            $table->json('booking_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('vendor_id');
            $table->index('customer_id');
            $table->index('booking_type');
            $table->index('payment_mode');
            $table->index('payment_status');
            $table->index('status');
            $table->index('invoice_number');
            $table->index('credit_note_number');
            $table->index(['hoarding_id', 'start_date', 'end_date']);
            $table->index(['status', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_bookings');
    }
};
