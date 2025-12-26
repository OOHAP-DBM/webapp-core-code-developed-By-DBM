<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dooh_screen_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dooh_screen_id')->constrained('dooh_screens')->onDelete('cascade');
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->string('file_path');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dooh_screen_media');
    }
};
