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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('hoarding_id')
                ->constrained('hoardings')
                ->cascadeOnDelete();

            // 🔥 SINGLE package_id (NO FK)
            $table->unsignedBigInteger('package_id')->nullable();

            $table->string('package_label')->nullable();

            $table->timestamps();

            // ek hi hoarding 1 user ke cart me duplicate na ho
            $table->unique(['user_id', 'hoarding_id']);

            // 🔍 Performance ke liye index
            $table->index('package_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
