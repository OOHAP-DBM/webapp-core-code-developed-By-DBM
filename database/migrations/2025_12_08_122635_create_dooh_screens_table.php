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
        Schema::create('dooh_screens', function (Blueprint $table) {
            $table->id();
            
            // Vendor ownership
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            
            // External API integration
            $table->string('external_screen_id')->nullable()->unique(); // ID from external API
            
            // Screen details
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('screen_type')->default('digital'); // digital, led, lcd
            
            // Location
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('India');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            
            // Screen specifications
            $table->string('resolution')->nullable(); // e.g., "1920x1080"
            $table->string('screen_size')->nullable(); // e.g., "55 inch"
            $table->decimal('width', 8, 2)->nullable(); // in feet
            $table->decimal('height', 8, 2)->nullable(); // in feet
            
            // Content specifications
            $table->integer('slot_duration_seconds')->default(10); // Duration per ad slot
            $table->integer('loop_duration_seconds')->default(300); // Total loop duration (5 min default)
            $table->integer('slots_per_loop')->default(30); // Calculated: loop_duration / slot_duration
            
            // Pricing
            $table->integer('min_slots_per_day')->default(6); // Minimum booking requirement
            $table->decimal('price_per_slot', 10, 2); // Price per slot
            $table->decimal('price_per_month', 12, 2)->nullable(); // Package price per month
            $table->decimal('minimum_booking_amount', 12, 2); // Minimum amount to book
            
            // Availability
            $table->integer('total_slots_per_day')->default(144); // (24 hours × 60 min × 60 sec) / 10 sec / 300 sec loop = 288/2 = 144
            $table->integer('available_slots_per_day')->default(144);
            
            // Allowed file formats
            $table->json('allowed_formats')->nullable(); // ['mp4', 'jpg', 'png']
            $table->integer('max_file_size_mb')->default(50);
            
            // Status
            $table->enum('status', ['draft', 'pending_approval', 'active', 'inactive', 'suspended'])->default('draft');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            
            // API sync metadata
            $table->json('sync_metadata')->nullable(); // Store additional data from external API
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('vendor_id');
            $table->index('status');
            $table->index('city');
            $table->index('external_screen_id');
            $table->index(['lat', 'lng']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dooh_screens');
    }
};
