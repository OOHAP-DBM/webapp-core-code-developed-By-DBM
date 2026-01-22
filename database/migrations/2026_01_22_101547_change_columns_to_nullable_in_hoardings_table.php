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
        Schema::table('hoardings', function (Blueprint $table) {
           // Price & Commission
            // $table->decimal('base_monthly_price', 12, 2)->nullable()->change();
            // $table->decimal('monthly_price', 12, 2)->nullable()->change();
            // $table->decimal('commission_percent', 5, 2)->nullable()->change();

            // Integers
            $table->unsignedInteger('grace_period_days')->nullable()->change();
            $table->unsignedInteger('min_booking_duration')->nullable()->change();

            // Booleans
            // $table->boolean('nagar_nigam_approved')->nullable()->change();
            // $table->boolean('geolocation_verified')->nullable()->change();

            // Strings
            $table->string('state')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
        //   $table->decimal('base_monthly_price', 12, 2)->default(0)->change();
        //     $table->decimal('monthly_price', 12, 2)->default(0)->change();
        //     $table->decimal('commission_percent', 5, 2)->default(0)->change();
        //     $table->unsignedInteger('grace_period_days')->default(0)->change();
            $table->unsignedInteger('min_booking_duration')->default(1)->change();
        //     $table->boolean('nagar_nigam_approved')->default(false)->change();
        //     $table->boolean('geolocation_verified')->default(false)->change();
            $table->string('state')->default('India')->change();
        });
    }
};
