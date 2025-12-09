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
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            
            // User reference
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Search details
            $table->string('name', 100); // User-defined name
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_name')->nullable(); // Human-readable location
            $table->integer('radius_km')->default(10);
            
            // Search filters (JSON)
            $table->json('filters')->nullable(); // All applied filters
            
            // Search metadata
            $table->integer('results_count')->default(0); // Last search results count
            $table->timestamp('last_executed_at')->nullable();
            $table->integer('execution_count')->default(0);
            
            // Notifications
            $table->boolean('notify_new_results')->default(false);
            $table->timestamp('last_notified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
