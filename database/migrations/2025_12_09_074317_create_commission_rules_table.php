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
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            
            // Rule identification
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0)->comment('Higher priority rules are checked first');
            
            // Rule type: vendor, hoarding, location, flat, time_based, seasonal
            $table->enum('rule_type', ['vendor', 'hoarding', 'location', 'flat', 'time_based', 'seasonal'])->default('flat');
            
            // Applicability filters (JSON for flexible criteria)
            $table->foreignId('vendor_id')->nullable()->constrained('users')->onDelete('cascade')->comment('Specific vendor (null = all)');
            $table->foreignId('hoarding_id')->nullable()->constrained('hoardings')->onDelete('cascade')->comment('Specific hoarding (null = all)');
            $table->string('city')->nullable()->comment('City filter');
            $table->string('area')->nullable()->comment('Area filter');
            $table->enum('hoarding_type', ['billboard', 'digital', 'transit', 'street_furniture', 'wallscape', 'mobile'])->nullable()->comment('Hoarding type filter');
            
            // Time-based conditions
            $table->date('valid_from')->nullable()->comment('Rule valid from date');
            $table->date('valid_to')->nullable()->comment('Rule valid until date');
            $table->json('days_of_week')->nullable()->comment('Array of days [0-6] for weekly rules');
            $table->json('time_range')->nullable()->comment('{start: HH:MM, end: HH:MM}');
            
            // Seasonal offers
            $table->boolean('is_seasonal')->default(false);
            $table->string('season_name')->nullable()->comment('e.g., Summer Sale, Diwali Offer');
            
            // Commission calculation
            $table->enum('commission_type', ['percentage', 'fixed', 'tiered'])->default('percentage');
            $table->decimal('commission_value', 10, 2)->comment('Percentage (e.g., 15.00) or Fixed amount');
            $table->json('tiered_config')->nullable()->comment('For tiered: [{min: 0, max: 10000, rate: 15}, ...]');
            
            // Booking distribution (optional revenue sharing)
            $table->boolean('enable_distribution')->default(false);
            $table->json('distribution_config')->nullable()->comment('{vendor: 80, platform: 15, other: 5}');
            
            // Additional conditions
            $table->decimal('min_booking_amount', 10, 2)->nullable()->comment('Minimum booking amount to apply rule');
            $table->decimal('max_booking_amount', 10, 2)->nullable()->comment('Maximum booking amount to apply rule');
            $table->integer('min_duration_days')->nullable()->comment('Minimum booking duration');
            $table->integer('max_duration_days')->nullable()->comment('Maximum booking duration');
            
            // Usage tracking
            $table->integer('usage_count')->default(0)->comment('Number of times rule has been applied');
            $table->timestamp('last_used_at')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('is_active');
            $table->index('rule_type');
            $table->index('vendor_id');
            $table->index('hoarding_id');
            $table->index(['valid_from', 'valid_to']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
