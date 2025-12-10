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
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            
            // Reference to entity
            $table->morphs('taxable'); // taxable_type, taxable_id (Booking, BookingPayment, PayoutRequest)
            
            // Tax rule applied
            $table->foreignId('tax_rule_id')->nullable()->constrained('tax_rules')->nullOnDelete();
            $table->string('tax_code')->index()->comment('Copy of tax_rule code for reference');
            $table->string('tax_name')->comment('Tax rule name at time of calculation');
            $table->enum('tax_type', ['gst', 'tds', 'vat', 'service_tax', 'reverse_charge', 'other']);
            
            // Calculation details
            $table->decimal('base_amount', 10, 2)->comment('Amount on which tax is calculated');
            $table->decimal('tax_rate', 5, 2)->comment('Tax rate percentage');
            $table->decimal('tax_amount', 10, 2)->comment('Calculated tax amount');
            
            // Reverse charge
            $table->boolean('is_reverse_charge')->default(false);
            $table->string('paid_by')->nullable()->comment('customer|vendor|platform');
            
            // TDS details
            $table->boolean('is_tds')->default(false);
            $table->string('tds_section')->nullable();
            $table->decimal('tds_deducted', 10, 2)->nullable();
            
            // Audit trail
            $table->json('calculation_snapshot')->nullable()->comment('Complete calculation details');
            $table->string('calculated_by')->comment('Service/class that performed calculation');
            $table->timestamp('calculated_at');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tax_type', 'created_at']);
            $table->index('calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
    }
};
