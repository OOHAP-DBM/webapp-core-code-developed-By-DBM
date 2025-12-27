<?php
// database/migrations/2025_12_27_000001_update_dooh_screens_and_related_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 2 & 3: Add new columns to dooh_screens
        Schema::table('dooh_screens', function (Blueprint $table) {
            $table->boolean('nagar_nigam_approved')->nullable();
            $table->json('block_dates')->nullable();
            $table->boolean('grace_period')->nullable();
            $table->json('audience_types')->nullable();
            $table->json('visible_from')->nullable();
            $table->json('located_at')->nullable();
            $table->enum('hoarding_visibility', ['one_way', 'both_side'])->nullable();
            $table->json('visibility_details')->nullable();
            $table->decimal('display_price_per_30s', 10, 2)->nullable();
            $table->integer('video_length')->nullable();
            $table->decimal('monthly_price', 12, 2)->nullable();
            $table->decimal('weekly_price', 12, 2)->nullable();
            $table->boolean('offer_discount')->nullable();
            $table->json('services_included')->nullable();
            $table->decimal('base_monthly_price', 12, 2)->nullable();
            $table->json('long_term_offers')->nullable();
            $table->boolean('graphics_included')->default(false);
            $table->decimal('graphics_price', 12, 2)->nullable();
            $table->decimal('survey_charge', 12, 2)->nullable();
        });

        // Step 2: Brand logos table (removed, replaced by hoarding_brand_logos)
        // Step 3: Update dooh_packages for campaign packages (if needed)
        Schema::table('dooh_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('dooh_packages', 'duration')) {
                $table->string('duration')->nullable(); // e.g., '3 months', '1 year'
            }
            if (!Schema::hasColumn('dooh_packages', 'custom_fields')) {
                $table->json('custom_fields')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dooh_screens', function (Blueprint $table) {
            $table->dropColumn([
                'nagar_nigam_approved', 'block_dates', 'grace_period', 'audience_types',
                'visible_from', 'located_at', 'hoarding_visibility', 'visibility_details',
                'display_price_per_30s', 'video_length', 'monthly_price', 'weekly_price',
                'offer_discount', 'services_included'
            ]);
        });
        // Schema::dropIfExists('dooh_screen_brand_logos'); // removed, replaced by hoarding_brand_logos
        Schema::dropIfExists('dooh_screen_slots');
        Schema::table('dooh_packages', function (Blueprint $table) {
            if (Schema::hasColumn('dooh_packages', 'duration')) {
                $table->dropColumn('duration');
            }
            if (Schema::hasColumn('dooh_packages', 'custom_fields')) {
                $table->dropColumn('custom_fields');
            }
        });
    }
};
