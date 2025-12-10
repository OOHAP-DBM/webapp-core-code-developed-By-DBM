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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combination - user can't add same hoarding twice
            $table->unique(['user_id', 'hoarding_id']);
            
            // Add indexes for faster queries
            $table->index('user_id');
            $table->index('hoarding_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
