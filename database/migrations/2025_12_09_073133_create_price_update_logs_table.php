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
        Schema::create('price_update_logs', function (Blueprint $table) {
            $table->id();
            
            // Update metadata
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->enum('update_type', ['single', 'bulk'])->default('single');
            $table->string('batch_id')->nullable()->index(); // Groups bulk updates together
            
            // Hoarding reference
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            
            // Price snapshot (before update)
            $table->decimal('old_weekly_price', 10, 2)->nullable();
            $table->decimal('old_monthly_price', 10, 2)->nullable();
            
            // New prices
            $table->decimal('new_weekly_price', 10, 2)->nullable();
            $table->decimal('new_monthly_price', 10, 2)->nullable();
            
            // Bulk update criteria (if applicable)
            $table->json('bulk_criteria')->nullable(); // Stores filter criteria: {area, vendor_id, property_type, city, type, size}
            $table->enum('update_method', ['fixed', 'percentage', 'increment', 'decrement'])->nullable();
            $table->decimal('update_value', 10, 2)->nullable(); // Amount or percentage value
            
            // Additional context
            $table->text('reason')->nullable(); // Admin's reason for price update
            $table->text('notes')->nullable(); // Additional notes
            $table->integer('affected_hoardings_count')->default(1); // For bulk updates
            
            // Complete snapshot of hoarding at time of update
            $table->json('hoarding_snapshot')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('admin_id');
            $table->index('update_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_update_logs');
    }
};
