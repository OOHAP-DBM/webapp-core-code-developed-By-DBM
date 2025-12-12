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
        Schema::create('vendor_quotes', function (Blueprint $table) {
            $table->id();
            
            // References
            $table->foreignId('quote_request_id')->nullable()->constrained('quote_requests')->onDelete('cascade');
            $table->foreignId('enquiry_id')->nullable()->constrained('enquiries')->onDelete('cascade');
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            
            // Quote Details
            $table->string('quote_number')->unique()->comment('VQ-XXXXXXXXXX');
            $table->integer('version')->default(1)->comment('Quote version for revisions');
            $table->foreignId('parent_quote_id')->nullable()->constrained('vendor_quotes')->comment('For revised quotes');
            
            // Duration
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days');
            $table->enum('duration_type', ['days', 'weeks', 'months']);
            
            // Hoarding Details (snapshot)
            $table->json('hoarding_snapshot')->comment('Immutable hoarding details');
            
            // Pricing Breakdown
            $table->decimal('base_price', 12, 2)->comment('Per day/week/month base price');
            $table->decimal('printing_cost', 12, 2)->default(0);
            $table->decimal('mounting_cost', 12, 2)->default(0);
            $table->decimal('survey_cost', 12, 2)->default(0);
            $table->decimal('lighting_cost', 12, 2)->default(0);
            $table->decimal('maintenance_cost', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->text('other_charges_description')->nullable();
            
            // Totals
            $table->decimal('subtotal', 12, 2)->comment('Sum of all charges');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->comment('GST/Tax');
            $table->decimal('tax_percentage', 5, 2)->default(18)->comment('GST %');
            $table->decimal('grand_total', 12, 2)->comment('Final amount');
            
            // Vendor Notes
            $table->text('vendor_notes')->nullable()->comment('Terms, conditions, special notes');
            $table->json('terms_and_conditions')->nullable();
            
            // Status
            $table->enum('status', [
                'draft',
                'sent',
                'viewed',
                'accepted',
                'rejected',
                'expired',
                'revised'
            ])->default('draft');
            
            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Quote validity');
            $table->text('rejection_reason')->nullable();
            
            // PDF Storage
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            
            // JSON Snapshot (complete immutable record)
            $table->json('quote_snapshot')->nullable()->comment('Complete quote data on approval');
            
            // Booking Reference
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->comment('Created booking if accepted');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('quote_number');
            $table->index('quote_request_id');
            $table->index('hoarding_id');
            $table->index('customer_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('sent_at');
            $table->index('expires_at');
            $table->index(['vendor_id', 'status']);
            $table->index(['customer_id', 'status']);
        });
        
        // Add foreign key to quote_requests after both tables exist
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->foreign('selected_quote_id')->references('id')->on('vendor_quotes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_quotes');
    }
};
