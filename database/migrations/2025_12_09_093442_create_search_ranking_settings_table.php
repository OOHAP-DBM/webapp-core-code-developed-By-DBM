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
        Schema::create('search_ranking_settings', function (Blueprint $table) {
            $table->id();
            
            // Ranking factors (0-100 weight)
            $table->integer('distance_weight')->default(40); // Distance from search location
            $table->integer('price_weight')->default(20); // Price match
            $table->integer('availability_weight')->default(15); // Availability status
            $table->integer('rating_weight')->default(10); // Vendor/property rating
            $table->integer('popularity_weight')->default(10); // Views/bookings count
            $table->integer('recency_weight')->default(5); // Recently added
            
            // Boost factors (percentage boost)
            $table->integer('featured_boost')->default(50); // Featured listings
            $table->integer('verified_vendor_boost')->default(20); // Verified vendors
            $table->integer('premium_boost')->default(30); // Premium listings
            
            // Search behavior
            $table->integer('default_radius_km')->default(10); // Default search radius
            $table->integer('max_radius_km')->default(100); // Maximum allowed radius
            $table->integer('min_radius_km')->default(1); // Minimum radius
            $table->integer('results_per_page')->default(20);
            $table->integer('max_results')->default(1000);
            
            // Map settings
            $table->json('default_center')->nullable(); // {lat, lng}
            $table->integer('default_zoom_level')->default(12);
            $table->boolean('cluster_markers')->default(true);
            $table->integer('cluster_radius')->default(80); // pixels
            
            // Filter settings
            $table->json('enabled_filters')->nullable(); // Array of enabled filter names
            $table->json('filter_defaults')->nullable(); // Default filter values
            
            // Auto-complete settings
            $table->boolean('enable_autocomplete')->default(true);
            $table->integer('autocomplete_min_chars')->default(3);
            $table->integer('autocomplete_max_results')->default(10);
            
            // Metadata
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_ranking_settings');
    }
};
