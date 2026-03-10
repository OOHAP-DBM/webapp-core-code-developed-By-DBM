<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hoarding_id')->constrained()->cascadeOnDelete();

            $table->tinyInteger('rating'); // 1-5 stars
            $table->text('review')->nullable();

            $table->timestamps();

            $table->unique(['user_id','hoarding_id']); // ek user ek hoarding ko ek hi bar rate kare
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};