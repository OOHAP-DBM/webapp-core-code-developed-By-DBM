<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoarding_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hoarding_id');
            $table->string('file_path');
            $table->string('media_type')->nullable(); // e.g., image, video
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('hoarding_id')->references('id')->on('hoardings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoarding_media');
    }
};
