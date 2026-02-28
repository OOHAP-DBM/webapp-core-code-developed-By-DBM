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
        Schema::create('pos_booking_hoardings', function (Blueprint $table) {
             $table->id();

            // Foreign keys
            $table->foreignId('pos_booking_id')->constrained('pos_bookings')->onDelete('cascade');
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');

            // Store hoarding-specific pricing
            $table->decimal('hoarding_price', 12, 2); // Price for this specific hoarding
            $table->decimal('hoarding_discount', 12, 2)->default(0);
            $table->decimal('hoarding_tax', 12, 2);
            $table->decimal('hoarding_total', 12, 2);

            // Booking details per hoarding
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days');
            $table->string('duration_type')->nullable();
            $table->json('services_included')->nullable();



            // Status
            $table->enum('status', ['pending', 'confirmed', 'active', 'completed', 'cancelled'])->default('pending');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['pos_booking_id', 'hoarding_id']);
            $table->index('hoarding_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_booking_hoardings');
    }
};
