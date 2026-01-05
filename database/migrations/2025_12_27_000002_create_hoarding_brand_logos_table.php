<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoarding_brand_logos', function (Blueprint $table) {
            $table->unsignedBigInteger('hoarding_id');
            $table->string('brand_name')->nullable();

            $table->string('file_path');
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('hoarding_id')
                ->references('id')
                ->on('hoardings')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoarding_brand_logos');
    }
};
