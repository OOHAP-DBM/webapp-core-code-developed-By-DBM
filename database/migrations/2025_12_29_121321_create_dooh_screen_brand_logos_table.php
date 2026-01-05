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
        Schema::create('dooh_screen_brand_logos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dooh_screen_id')->constrained('dooh_screens')->onDelete('cascade');
            $table->string('file_path');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dooh_screen_brand_logos');
    }
};
