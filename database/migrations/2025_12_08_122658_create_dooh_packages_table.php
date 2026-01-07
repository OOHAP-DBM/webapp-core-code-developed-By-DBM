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
        Schema::create('dooh_packages', function (Blueprint $table) {
            $table->id();

            // Screen reference
            // FK (explicit, safest)
            $table->unsignedBigInteger('dooh_screen_id');
            $table->foreign('dooh_screen_id')
                ->references('id')
                ->on('dooh_screens')
                ->onDelete('cascade');
            
            // Package details
            $table->string('package_name'); // e.g., "Basic Package", "Premium Package"
            $table->text('description')->nullable();
            
            // Slot allocation
            $table->integer('slots_per_day'); // Number of slots included per day
            $table->integer('slots_per_month')->nullable(); // Total slots per month (calculated)
            
            // Frequency and timing
            $table->integer('loop_interval_minutes')->default(5); // How often ad appears in loop
            $table->json('time_slots')->nullable(); // Specific time preferences: [{"start": "09:00", "end": "18:00"}]
            
            // Pricing
            $table->integer('min_booking_duration')->default(1); // Minimum booking period
            // $table->integer('max_booking_duration')->default(12); // Maximum booking period
            $table->string('duration_unit')->default('months');
            // Discount
            $table->decimal('discount_percent', 5, 2)->default(0); // Discount for long-term bookings
            
            // Package type
            $table->enum('package_type', ['standard', 'premium', 'custom'])->default('standard');
            $table->json('services_included')->nullable();
            // Status
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('custom_fields')->nullable();
            $table->boolean('auto_apply')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('dooh_screen_id');
            $table->index('is_active');
            $table->index('package_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dooh_packages');
    }
};
