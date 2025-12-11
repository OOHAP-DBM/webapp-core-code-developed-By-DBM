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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->integer('line_number')->comment('Sequential item number in invoice');
            
            // Item identification
            $table->string('item_type', 50)->comment('hoarding, printing, mounting, subscription, service, other');
            $table->text('description')->comment('Item description/title');
            $table->string('hsn_sac_code', 10)->nullable()->comment('HSN (goods) or SAC (services) code');
            
            // Related entities
            $table->foreignId('hoarding_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->comment('For printing/mounting products');
            
            // Quantity and pricing
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 20)->default('days')->comment('days, pcs, sqft, etc.');
            $table->decimal('rate', 12, 2)->comment('Per unit rate');
            $table->decimal('amount', 12, 2)->comment('Quantity × Rate');
            
            // Discounts
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->comment('After discount, before tax');
            
            // Tax breakdown per item - CGST + SGST for intra-state
            $table->decimal('cgst_rate', 5, 2)->nullable();
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->nullable();
            $table->decimal('sgst_amount', 12, 2)->default(0);
            
            // Tax breakdown per item - IGST for inter-state
            $table->decimal('igst_rate', 5, 2)->nullable();
            $table->decimal('igst_amount', 12, 2)->default(0);
            
            // Item totals
            $table->decimal('total_tax', 12, 2)->comment('Total tax for this item');
            $table->decimal('total_amount', 12, 2)->comment('Final amount including tax');
            
            // Additional metadata
            $table->date('service_start_date')->nullable()->comment('For booking period');
            $table->date('service_end_date')->nullable();
            $table->integer('duration_days')->nullable();
            $table->json('metadata')->nullable()->comment('Additional item-specific data');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'line_number']);
            $table->index('hoarding_id');
            $table->index('item_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
