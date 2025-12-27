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
        Schema::create('hoardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');

            // --- CORE IDENTIFICATION ---
            $table->string('title')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('hoarding_type')->default('ooh'); // ooh, dooh
            $table->string('category')->nullable(); // Unipole, Billboard, etc.

            // --- PHYSICAL SPECS (STEP 1) ---
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('measurement_unit')->default('Sq.ft');
            $table->date('valid_till')->nullable();
            $table->enum('lighting_type', ['frontlight', 'backlight', 'none'])->nullable();
            $table->unsignedInteger('grace_period_days')->default(0);
            $table->boolean('is_nagar_nigam_approved')->default(false);

            // --- LOCATION & GEOLOCATION (STEP 2) ---
            $table->text('address')->nullable();
            $table->string('city')->index()->nullable();
            $table->string('state')->default('India');
            $table->string('pincode', 10)->nullable();
            $table->string('landmark')->nullable();
            $table->string('facing')->nullable();

            // High Precision Geolocation
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('geolocation_verified')->default(false);
            $table->string('geolocation_source', 50)->nullable()->comment('manual, google_maps, gps, etc.');

            // Visibility Tracking
            $table->time('visibility_start')->nullable();
            $table->time('visibility_end')->nullable();

            // --- AUDIENCE METRICS (GAZEFLOW) ---
            $table->integer('expected_footfall')->nullable();
            $table->integer('expected_eyeball')->nullable();

            // --- PRICING & FINANCE (STEP 3) ---
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->decimal('base_monthly_price', 12, 2)->default(0);
            $table->decimal('weekly_price', 10, 2)->nullable();
            $table->decimal('commission_percent', 5, 2)->default(0);

            // Service Charges
            $table->decimal('printing_charge', 10, 2)->nullable();
            $table->decimal('mounting_charge', 10, 2)->nullable();
            $table->decimal('designing_charge', 10, 2)->nullable();
            $table->string('material_type')->nullable();

            // --- TRACKING & POPULARITY ---
            $table->integer('views_count')->default(0);
            $table->integer('bookings_count')->default(0);
            $table->timestamp('last_booked_at')->nullable();

            // --- WORKFLOW & STATUS ---
            $table->enum('status', ['draft', 'pending', 'active', 'inactive'])->default('draft')->index();
            $table->unsignedInteger('current_step')->default(1);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Spatial Index for map-based searches
            $table->index(['latitude', 'longitude'], 'hoardings_location_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoardings');
    }
};
