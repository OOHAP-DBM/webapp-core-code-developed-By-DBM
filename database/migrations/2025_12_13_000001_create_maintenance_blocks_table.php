<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PROMPT 102: Admin Blocking Periods (Maintenance/Repairs)
     * Table for storing maintenance/repair blocks that make hoardings unavailable
     */
    public function up(): void
    {
        Schema::create('maintenance_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin or Vendor who created the block
            $table->string('title'); // e.g., "Painting Work", "Structural Repair"
            $table->text('description')->nullable(); // Detailed description
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->enum('block_type', ['maintenance', 'repair', 'inspection', 'other'])->default('maintenance');
            $table->string('affected_by')->nullable(); // External factor (weather, permits, etc.)
            $table->text('notes')->nullable(); // Internal notes
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['hoarding_id', 'status', 'start_date', 'end_date'], 'hoarding_status_dates_idx');
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_blocks');
    }
};
