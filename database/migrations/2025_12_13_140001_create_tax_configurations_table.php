<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PROMPT 109: Global Tax Configuration
     * 
     * Admin-configurable tax settings that work alongside TaxRule (PROMPT 62)
     */
    public function up(): void
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->id();
            
            // Configuration identification
            $table->string('key')->unique()->comment('Unique config key: gst_enabled, tcs_threshold');
            $table->string('name')->comment('Display name');
            $table->text('description')->nullable();
            
            // Configuration type
            $table->enum('config_type', ['gst', 'tcs', 'tds', 'general'])->default('general');
            $table->enum('data_type', ['boolean', 'integer', 'float', 'string', 'array'])->default('string');
            $table->string('group')->default('tax_rules')->comment('tax_rates, tcs_rules, tds_rules, exemptions');
            
            // Value (stored as string, cast by data_type)
            $table->text('value');
            
            // Status and applicability
            $table->boolean('is_active')->default(true);
            $table->string('applies_to')->nullable()->comment('booking, invoice, payout, all');
            $table->string('country_code', 2)->default('IN')->comment('ISO 3166-1 alpha-2');
            
            // Metadata and validation
            $table->json('metadata')->nullable()->comment('Additional context, examples');
            $table->json('validation_rules')->nullable()->comment('min, max, regex, in');
            
            $table->timestamps();
            
            // Indexes
            $table->index('key');
            $table->index('config_type');
            $table->index('group');
            $table->index('is_active');
            $table->index(['config_type', 'is_active']);
            $table->index(['group', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_configurations');
    }
};
