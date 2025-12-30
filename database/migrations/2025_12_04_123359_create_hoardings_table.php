<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('hoardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();

            /* CORE */
            $table->string('title')->unique()->nullable();
            $table->string('slug')->unique()->nullable();
             $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->enum('hoarding_type', ['ooh', 'dooh'])->default('ooh');
            $table->string('category')->nullable();

            /* LOCATION */
            $table->text('address')->nullable();
            $table->string('city')->index()->nullable();
            $table->string('state')->default('India');
            $table->string('locality')->nullable();
            $table->string('pincode')->nullable();
            $table->string('country')->default('India');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('geolocation_verified')->default(false);
            $table->string('geolocation_source')->nullable();

            /* VISIBILITY */
            $table->time('visibility_start')->nullable();
            $table->time('visibility_end')->nullable();
            $table->enum('facing_direction', [
                'north',
                'south',
                'east',
                'west',
                'north_east',
                'north_west',
                'south_east',
                'south_west'
            ])->nullable();
            $table->string('road_type')->nullable();
            $table->string('traffic_type')->nullable();
            $table->string('hoarding_visibility')->nullable();
            $table->json('visibility_details')->nullable();

            /* AUDIENCE */
            $table->integer('expected_footfall')->nullable();
            $table->integer('expected_eyeball')->nullable();
            $table->json('audience_types')->nullable();

            /* PRICING */
            $table->decimal('base_monthly_price', 12, 2)->default(0);
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->boolean('enable_weekly_booking')->default(false);
            $table->decimal('weekly_price', 10, 2)->nullable();
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('currency', 10)->default('INR');

            /* ONE TIME CHARGES */
            $table->boolean('graphics_included')->default(false);
            $table->decimal('graphics_charge', 10, 2)->nullable();
            $table->decimal('survey_charge', 10, 2)->nullable();

            /* BOOKING RULES */
            $table->unsignedInteger('grace_period_days')->default(0);
            $table->unsignedInteger('min_booking_months')->default(1);
            $table->unsignedInteger('max_booking_months')->nullable();
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->json('block_dates')->nullable();

            /* LEGAL */
            $table->boolean('nagar_nigam_approved')->default(false);
            $table->string('permit_number')->nullable();
            $table->date('permit_valid_till')->nullable();

            /* WORKFLOW */
            $table->enum('status', ['draft', 'pending_approval', 'active', 'inactive', 'suspended','approved'])->default('draft');
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('currency', 10)->default('INR');

            /* ONE TIME CHARGES */
            $table->boolean('graphics_included')->default(false);
            $table->decimal('graphics_charge', 10, 2)->nullable();
            $table->decimal('survey_charge', 10, 2)->nullable();

            /* BOOKING RULES */
            $table->unsignedInteger('grace_period_days')->default(0);
            $table->unsignedInteger('min_booking_months')->default(1);
            $table->unsignedInteger('max_booking_months')->nullable();
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->json('block_dates')->nullable();

            /* LEGAL */
            $table->boolean('nagar_nigam_approved')->default(false);
            $table->string('permit_number')->nullable();
            $table->date('permit_valid_till')->nullable();

            /* WORKFLOW */
            $table->enum('status', ['draft', 'pending_approval', 'active', 'inactive', 'suspended'])->default('draft');
            $table->unsignedInteger('current_step')->default(1);
            $table->boolean('is_featured')->default(false);

            /* ADMIN TRACKING */
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();

            /* SEO */
            $table->string('meta_title', 70)->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('noindex')->default(false);

            /* STATS */
            $table->integer('view_count')->default(0);
            $table->integer('bookings_count')->default(0);
            $table->timestamp('last_booked_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['latitude', 'longitude'], 'hoardings_location_index');
            $table->index('status');
            $table->index(['vendor_id', 'status']);
            $table->index('submitted_at');
        });
    }

    // Schema::create('hoardings', function (Blueprint $table) {
    //     $table->id();
    //     $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();

    //     /* ================= CORE ================= */
    //     $table->string('title')->unique()->nullable();
    //     $table->text('description')->nullable();
    //     $table->string('hoarding_type')->default('ooh'); // ooh, dooh
    //     $table->string('category')->nullable();

    //     /* ================= PHYSICAL ================= */
    //     $table->decimal('width', 8, 2)->nullable();
    //     $table->decimal('height', 8, 2)->nullable();
    //     $table->string('measurement_unit')->default('Sq.ft');
    //     $table->enum('lighting_type', ['frontlight', 'backlight', 'none'])->nullable();
    //     $table->string('material_type')->nullable();
    //     $table->string('facing')->nullable();
    //     $table->unsignedInteger('grace_period_days')->default(0);

    //     /* ================= LEGAL ================= */
    //     $table->boolean('is_nagar_nigam_approved')->default(false);
    //     $table->date('permit_valid_till')->nullable();

    //     /* ================= LOCATION ================= */
    //     $table->text('address')->nullable();
    //     $table->string('city')->nullable()->index();
    //     $table->string('state')->default('India');
    //     $table->string('pincode', 10)->nullable();
    //     $table->string('landmark')->nullable();

    //     $table->decimal('latitude', 10, 7)->nullable();
    //     $table->decimal('longitude', 10, 7)->nullable();
    //     $table->boolean('geolocation_verified')->default(false);
    //     $table->string('geolocation_source', 50)->nullable();

    //     $table->time('visibility_start')->nullable();
    //     $table->time('visibility_end')->nullable();

    //     /* ================= AUDIENCE ================= */
    //     $table->integer('expected_footfall')->nullable();
    //     $table->integer('expected_eyeball')->nullable();

    //     /* ================= PRICING ================= */
    //     $table->decimal('monthly_price', 12, 2)->default(0);
    //     $table->decimal('base_monthly_price', 12, 2)->default(0);
    //     $table->decimal('weekly_price', 10, 2)->nullable();
    //     $table->decimal('commission_percent', 5, 2)->default(0);
    //     $table->decimal('printing_charge', 10, 2)->nullable();
    //     $table->decimal('mounting_charge', 10, 2)->nullable();
    //     $table->decimal('designing_charge', 10, 2)->nullable();

    //     /* ================= BOOKING RULES ================= */
    //     $table->unsignedInteger('min_booking_months')->default(1);
    //     $table->unsignedInteger('max_booking_months')->nullable();
    //     $table->date('available_from')->nullable();
    //     $table->date('available_to')->nullable();

    //     /* ================= STATUS ================= */
    //     $table->enum('listing_status', ['draft', 'active', 'inactive'])
    //         ->default('draft')
    //         ->index();

    //     $table->enum('approval_status', ['pending', 'under_verification', 'approved', 'rejected'])
    //         ->default('pending')
    //         ->index();

    //     $table->unsignedInteger('current_step')->default(1);
    //     $table->boolean('is_featured')->default(false);

    //     /* ================= TRACKING ================= */
    //     $table->integer('bookings_count')->default(0);
    //     $table->timestamp('last_booked_at')->nullable();

    //     $table->timestamps();
    //     $table->softDeletes();

    //     $table->index(['latitude', 'longitude'], 'hoardings_location_index');
    // });


    public function down(): void
    {
        Schema::dropIfExists('hoardings');
    }
};
