<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoarding_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hoarding_id');
            $table->string('package_code', 50)->unique();
            $table->string('package_name');
            $table->decimal('discount_percent', 5, 2); // max 100
            // Ownership
            $table->unsignedBigInteger('vendor_id');

            // Pricing (base, before offer)
            $table->decimal('base_price_per_month', 12, 2);

            // Booking rules
            $table->integer('min_booking_duration')->default(1); // in months
            $table->string('duration_unit')->default('months');

            // Validity
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('services_included')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('hoarding_id')->references('id')->on('hoardings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoarding_packages');
    }
};
