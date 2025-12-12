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
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            
            // Customer Request
            $table->string('request_number')->unique()->comment('QR-XXXXXXXXXX');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            
            // Duration Requirements
            $table->date('preferred_start_date');
            $table->date('preferred_end_date');
            $table->integer('duration_days');
            $table->enum('duration_type', ['days', 'weeks', 'months']);
            
            // Requirements
            $table->text('requirements')->nullable()->comment('Customer requirements/specifications');
            $table->boolean('printing_required')->default(false);
            $table->boolean('mounting_required')->default(false);
            $table->boolean('lighting_required')->default(false);
            $table->json('additional_services')->nullable();
            
            // Budget
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            
            // Multi-Vendor Selection
            $table->enum('vendor_selection_mode', ['single', 'multiple'])->default('single');
            $table->json('invited_vendor_ids')->nullable()->comment('Specific vendors invited');
            $table->boolean('open_to_all_vendors')->default(true);
            
            // Status
            $table->enum('status', [
                'draft',
                'published',
                'quotes_received',
                'quote_accepted',
                'closed',
                'expired',
                'cancelled'
            ])->default('draft');
            
            // Deadlines
            $table->timestamp('published_at')->nullable();
            $table->timestamp('response_deadline')->nullable()->comment('Vendors must respond by');
            $table->timestamp('decision_deadline')->nullable()->comment('Customer will decide by');
            
            // Selected Quote
            $table->unsignedBigInteger('selected_quote_id')->nullable();
            $table->timestamp('quote_selected_at')->nullable();
            
            // Metadata
            $table->integer('quotes_received_count')->default(0);
            $table->integer('quotes_viewed_count')->default(0);
            $table->json('hoarding_snapshot')->nullable()->comment('Hoarding details at request time');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('request_number');
            $table->index('customer_id');
            $table->index('hoarding_id');
            $table->index('status');
            $table->index('published_at');
            $table->index('response_deadline');
            $table->index(['hoarding_id', 'status']);
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
