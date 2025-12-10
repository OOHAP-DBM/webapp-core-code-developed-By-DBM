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
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            
            // Rule identification
            $table->string('name')->comment('Tax rule name (e.g., "GST India", "TDS on Commission")');
            $table->string('code')->unique()->comment('Unique code (e.g., "GST_IN", "TDS_COMMISSION")');
            $table->enum('tax_type', ['gst', 'tds', 'vat', 'service_tax', 'reverse_charge', 'other'])->default('gst');
            
            // Tax configuration
            $table->decimal('rate', 5, 2)->comment('Tax rate percentage (e.g., 18.00 for 18%)');
            $table->enum('calculation_method', ['percentage', 'flat', 'tiered'])->default('percentage');
            
            // Applicability conditions
            $table->enum('applies_to', ['booking', 'commission', 'payout', 'all'])->default('booking');
            $table->json('conditions')->nullable()->comment('Complex conditions (amount ranges, user types, etc.)');
            
            // Reverse charge handling
            $table->boolean('is_reverse_charge')->default(false)->comment('Tax paid by recipient');
            $table->text('reverse_charge_conditions')->nullable()->comment('When reverse charge applies');
            
            // TDS specific
            $table->boolean('is_tds')->default(false);
            $table->decimal('tds_threshold', 10, 2)->nullable()->comment('Minimum amount for TDS applicability');
            $table->string('tds_section')->nullable()->comment('Tax section (e.g., "194J")');
            
            // Geographical applicability
            $table->string('country_code')->default('IN')->index();
            $table->json('applicable_states')->nullable()->comment('State codes if applicable');
            
            // Priority and status
            $table->integer('priority')->default(100)->comment('Lower number = higher priority');
            $table->boolean('is_active')->default(true)->index();
            
            // Effective dates
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            
            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable()->comment('Additional configuration');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tax_type', 'is_active']);
            $table->index(['applies_to', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rules');
    }
};
