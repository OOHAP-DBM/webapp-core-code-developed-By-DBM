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
        Schema::create('hoarding_geos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->text('geojson')->nullable()->comment('GeoJSON polygon coordinates');
            $table->json('bounding_box')->nullable()->comment('Bounding box: {min_lat, max_lat, min_lng, max_lng}');
            $table->timestamps();
            
            $table->index('hoarding_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoarding_geos');
    }
};
