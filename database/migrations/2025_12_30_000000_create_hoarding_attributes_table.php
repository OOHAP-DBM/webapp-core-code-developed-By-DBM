<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hoarding_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // e.g., category, material, lighting
            $table->string('label'); // e.g., Unipole
            $table->string('value'); // e.g., unipole
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoarding_attributes');
    }
};
