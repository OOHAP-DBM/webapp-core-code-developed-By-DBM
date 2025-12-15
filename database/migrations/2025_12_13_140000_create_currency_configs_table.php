<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PROMPT 109: Global Currency Configuration
     */
    public function up(): void
    {
        Schema::create('currency_configs', function (Blueprint $table) {
            $table->id();
            
            // Currency identification
            $table->string('code', 3)->unique()->comment('ISO 4217 code: USD, EUR, INR');
            $table->string('name')->comment('Currency name: US Dollar, Indian Rupee');
            $table->string('symbol', 10)->comment('Currency symbol: $, ₹, €');
            
            // Formatting rules
            $table->enum('symbol_position', ['before', 'after'])->default('before');
            $table->string('decimal_separator', 1)->default('.');
            $table->string('thousand_separator', 1)->default(',');
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->string('format_pattern')->nullable()->comment('Custom format: {symbol} {amount}');
            
            // Exchange rates (relative to base currency)
            $table->decimal('exchange_rate', 12, 6)->default(1.000000)->comment('Rate to base currency');
            
            // Status
            $table->boolean('is_default')->default(false)->comment('System default currency');
            $table->boolean('is_active')->default(true);
            
            // Additional info
            $table->string('country_code', 2)->nullable()->comment('ISO 3166-1 alpha-2');
            $table->json('metadata')->nullable()->comment('Additional settings, country list');
            
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('is_default');
            $table->index('is_active');
            $table->index(['is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_configs');
    }
};
