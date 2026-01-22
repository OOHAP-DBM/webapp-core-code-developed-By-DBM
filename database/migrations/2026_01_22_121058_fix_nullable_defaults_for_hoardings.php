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

            $table->decimal('base_monthly_price', 12, 2)
                ->nullable()
                ->default(0)
                ->change();

            $table->decimal('monthly_price', 12, 2)
                ->nullable()
                ->default(0)
                ->change();

            $table->decimal('commission_percent', 5, 2)
                ->nullable()
                ->default(0)
                ->change();

            $table->unsignedInteger('grace_period_days')
                ->nullable()
                ->default(0)
                ->change();

            // $table->unsignedInteger('min_booking_duration')
            //     ->nullable()
            //     ->default(1)
            //     ->change();

            $table->boolean('nagar_nigam_approved')
                ->nullable()
                ->default(false)
                ->change();

            $table->boolean('geolocation_verified')
                ->nullable()
                ->default(false)
                ->change();
        });
    }

    public function down(): void
    {
        // NO tightening. Same schema = rollback-safe
        Schema::table('hoardings', function (Blueprint $table) {
            $table->decimal('base_monthly_price', 12, 2)->nullable()->default(0)->change();
            $table->decimal('monthly_price', 12, 2)->nullable()->default(0)->change();
            $table->decimal('commission_percent', 5, 2)->nullable()->default(0)->change();

            $table->unsignedInteger('grace_period_days')->nullable()->default(0)->change();
            // $table->unsignedInteger('min_booking_duration')->nullable()->default(1)->change();

            $table->boolean('nagar_nigam_approved')->nullable()->default(false)->change();
            $table->boolean('geolocation_verified')->nullable()->default(false)->change();
        });
    }
};
