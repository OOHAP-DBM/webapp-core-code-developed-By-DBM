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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Invoice identification
            $table->string('invoice_number', 50)->unique()->comment('Format: INV/2024-25/000001');
            $table->string('financial_year', 10)->index()->comment('Format: 2024-25');
            $table->date('invoice_date')->index();
            $table->enum('invoice_type', ['full_payment', 'milestone', 'subscription', 'pos', 'printing', 'remounting', 'vendor_service'])->default('full_payment');
            
            // Relationships
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_payment_id')->nullable()->constrained('booking_payments')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->comment('For milestone-based invoices');
            $table->foreignId('pos_booking_id')->nullable()->comment('For POS bookings');
            
            // Seller details (OOHAPP company)
            $table->string('seller_name');
            $table->string('seller_gstin', 15)->index();
            $table->text('seller_address');
            $table->string('seller_city', 100);
            $table->string('seller_state', 100);
            $table->string('seller_state_code', 2);
            $table->string('seller_pincode', 6);
            $table->string('seller_pan', 10)->nullable();
            
            // Buyer details
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_gstin', 15)->nullable()->index();
            $table->text('buyer_address')->nullable();
            $table->string('buyer_city', 100)->nullable();
            $table->string('buyer_state', 100);
            $table->string('buyer_state_code', 2);
            $table->string('buyer_pincode', 6)->nullable();
            $table->string('buyer_pan', 10)->nullable();
            $table->enum('buyer_type', ['individual', 'business'])->default('individual');
            $table->string('buyer_email')->nullable();
            $table->string('buyer_phone', 15)->nullable();
            
            // GST calculation details
            $table->string('place_of_supply', 100)->comment('State name for GST');
            $table->boolean('is_reverse_charge')->default(false)->comment('Reverse charge applicable');
            $table->boolean('is_intra_state')->comment('Same state transaction for CGST+SGST vs IGST');
            $table->string('supply_type', 50)->default('services')->comment('Goods or Services');
            
            // Amount breakdown
            $table->decimal('subtotal', 12, 2)->comment('Sum of all line items before discount');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->comment('After discount, before tax');
            
            // Tax amounts - CGST + SGST for intra-state
            $table->decimal('cgst_rate', 5, 2)->nullable();
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->nullable();
            $table->decimal('sgst_amount', 12, 2)->default(0);
            
            // Tax amounts - IGST for inter-state
            $table->decimal('igst_rate', 5, 2)->nullable();
            $table->decimal('igst_amount', 12, 2)->default(0);
            
            // Totals
            $table->decimal('total_tax', 12, 2)->comment('CGST+SGST or IGST');
            $table->decimal('total_amount', 12, 2)->comment('Taxable + Tax');
            $table->decimal('round_off', 5, 2)->default(0)->comment('Rounding adjustment');
            $table->decimal('grand_total', 12, 2)->comment('Final payable amount');
            
            // Additional details
            $table->text('notes')->nullable()->comment('Customer-facing notes');
            $table->text('terms_conditions')->nullable()->comment('Invoice T&C');
            $table->text('payment_terms')->nullable()->comment('Payment terms (e.g., Due in 30 days)');
            
            // PDF and QR Code
            $table->string('pdf_path')->nullable()->comment('Path to generated PDF');
            $table->string('qr_code_path')->nullable()->comment('Path to QR code image');
            $table->text('qr_code_data')->nullable()->comment('QR code content (UPI/invoice data)');
            
            // Email tracking
            $table->boolean('is_emailed')->default(false);
            $table->timestamp('emailed_at')->nullable();
            $table->integer('email_count')->default(0)->comment('Number of times emailed');
            $table->text('email_recipients')->nullable()->comment('Comma-separated email list');
            
            // Status management
            $table->enum('status', ['draft', 'issued', 'sent', 'paid', 'partially_paid', 'overdue', 'cancelled', 'void'])->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Payment tracking
            $table->decimal('paid_amount', 12, 2)->default(0)->comment('Total amount paid (for partial payments)');
            $table->date('due_date')->nullable();
            
            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable()->comment('Additional custom data');
            $table->timestamps();
            $table->softDeletes();
            
            // Composite indexes
            $table->index(['customer_id', 'invoice_date']);
            $table->index(['financial_year', 'invoice_number']);
            $table->index(['status', 'due_date']);
            $table->index('booking_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
