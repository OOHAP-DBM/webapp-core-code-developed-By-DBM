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
        Schema::create('dooh_slots', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('dooh_screen_id')->constrained('dooh_screens')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            
            // Slot Configuration
            $table->string('slot_name')->nullable(); // e.g., "Morning Prime", "Evening Slot"
            $table->time('start_time'); // Slot start time (e.g., 08:00:00)
            $table->time('end_time'); // Slot end time (e.g., 20:00:00)
            $table->integer('duration_seconds')->default(10); // How long ad displays (10 seconds)
            $table->integer('frequency_per_hour')->default(6); // How many times per hour
            $table->integer('loop_position')->nullable(); // Position in the ad loop (1, 2, 3...)
            
            // Calculated Fields
            $table->integer('total_daily_displays')->default(0); // Total displays per day
            $table->integer('total_hourly_displays')->default(0); // Displays per hour
            $table->decimal('interval_seconds', 8, 2)->default(0); // Seconds between displays
            
            // Pricing
            $table->decimal('price_per_display', 10, 2)->default(0); // Cost per single display
            $table->decimal('hourly_cost', 10, 2)->default(0); // Cost per hour
            $table->decimal('daily_cost', 10, 2)->default(0); // Cost per day
            $table->decimal('monthly_cost', 10, 2)->default(0); // Cost per month (30 days)
            
            // Booking Period
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('total_booking_days')->nullable(); // Total days booked
            $table->decimal('total_booking_cost', 12, 2)->nullable(); // Total cost for booking period
            
            // Status
            $table->enum('status', ['available', 'booked', 'blocked', 'maintenance'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_prime_time')->default(false); // Higher pricing for prime slots
            
            // Looping Logic
            $table->integer('ads_in_loop')->default(1); // Total number of ads in the loop
            $table->json('loop_schedule')->nullable(); // Detailed loop schedule
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional configuration
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('dooh_screen_id');
            $table->index('booking_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dooh_slots');
    }
};
