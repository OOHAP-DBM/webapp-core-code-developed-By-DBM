<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoarding_brand_logos', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name'); // e.g., 'Pepsi'
            $table->unsignedBigInteger('brandable_id');
            $table->string('brandable_type');
            $table->string('file_path'); // Path to the logo image
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['brandable_id', 'brandable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoarding_brand_logos');
    }
};
