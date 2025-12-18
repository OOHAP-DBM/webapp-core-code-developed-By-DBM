<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tables if they already exist to avoid migration errors
        Schema::dropIfExists('brand_manager_customers');
        // Schema::dropIfExists('brand_manager_brands');

        Schema::create('brand_manager_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_manager_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['brand_manager_id', 'customer_id']);
        });
        // Temporarily comment out brand_manager_brands table creation due to missing/incompatible brands table
        // Schema::create('brand_manager_brands', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('brand_manager_id')->constrained('users')->onDelete('cascade');
        //     $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
        //     $table->timestamps();
        //     $table->unique(['brand_manager_id', 'brand_id']);
        // });
    }
    public function down(): void
    {
        // Schema::dropIfExists('brand_manager_brands');
        Schema::dropIfExists('brand_manager_customers');
    }
};
