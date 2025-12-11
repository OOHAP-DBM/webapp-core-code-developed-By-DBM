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
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('financial_year', 10)->unique()->comment('Format: 2024-25 (Apr 1 to Mar 31)');
            $table->unsignedInteger('last_sequence')->default(0)->comment('Last used sequence number');
            $table->date('year_start_date')->comment('April 1 of start year');
            $table->date('year_end_date')->comment('March 31 of end year');
            $table->boolean('is_active')->default(true)->index()->comment('Current active FY');
            $table->timestamps();
            
            // Composite index for fast lookups
            $table->index(['financial_year', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
