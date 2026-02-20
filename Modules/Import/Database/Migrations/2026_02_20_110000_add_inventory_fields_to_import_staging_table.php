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
        Schema::table('inventory_import_staging', function (Blueprint $table) {
            $table->string('category')->nullable()->after('city');
            $table->string('address')->nullable()->after('category');
            $table->string('locality')->nullable()->after('address');
            $table->string('landmark')->nullable()->after('locality');
            $table->string('state')->nullable()->after('landmark');
            $table->string('pincode', 20)->nullable()->after('state');
            $table->decimal('latitude', 10, 7)->nullable()->after('pincode');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            $table->string('measurement_unit', 20)->nullable()->after('height');
            $table->string('lighting_type')->nullable()->after('measurement_unit');
            $table->string('screen_type')->nullable()->after('lighting_type');

            $table->decimal('base_monthly_price', 12, 2)->nullable()->after('image_name');
            $table->decimal('monthly_price', 12, 2)->nullable()->after('base_monthly_price');
            $table->decimal('weekly_price_1', 12, 2)->nullable()->after('monthly_price');
            $table->decimal('weekly_price_2', 12, 2)->nullable()->after('weekly_price_1');
            $table->decimal('weekly_price_3', 12, 2)->nullable()->after('weekly_price_2');
            $table->decimal('price_per_slot', 12, 2)->nullable()->after('weekly_price_3');

            $table->integer('slot_duration_seconds')->nullable()->after('price_per_slot');
            $table->integer('screen_run_time')->nullable()->after('slot_duration_seconds');
            $table->integer('total_slots_per_day')->nullable()->after('screen_run_time');
            $table->integer('min_slots_per_day')->nullable()->after('total_slots_per_day');
            $table->integer('min_booking_duration')->nullable()->after('min_slots_per_day');

            $table->decimal('minimum_booking_amount', 12, 2)->nullable()->after('min_booking_duration');
            $table->decimal('commission_percent', 8, 2)->nullable()->after('minimum_booking_amount');
            $table->decimal('graphics_charge', 12, 2)->nullable()->after('commission_percent');
            $table->decimal('survey_charge', 12, 2)->nullable()->after('graphics_charge');
            $table->decimal('printing_charge', 12, 2)->nullable()->after('survey_charge');
            $table->decimal('mounting_charge', 12, 2)->nullable()->after('printing_charge');
            $table->decimal('remounting_charge', 12, 2)->nullable()->after('mounting_charge');
            $table->decimal('lighting_charge', 12, 2)->nullable()->after('remounting_charge');

            $table->string('discount_type', 50)->nullable()->after('lighting_charge');
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type');
            $table->string('availability', 100)->nullable()->after('discount_value');
            $table->string('currency', 10)->nullable()->after('availability');
            $table->date('available_from')->nullable()->after('currency');
            $table->date('available_to')->nullable()->after('available_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_import_staging', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'address',
                'locality',
                'landmark',
                'state',
                'pincode',
                'latitude',
                'longitude',
                'measurement_unit',
                'lighting_type',
                'screen_type',
                'base_monthly_price',
                'monthly_price',
                'weekly_price_1',
                'weekly_price_2',
                'weekly_price_3',
                'price_per_slot',
                'slot_duration_seconds',
                'screen_run_time',
                'total_slots_per_day',
                'min_slots_per_day',
                'min_booking_duration',
                'minimum_booking_amount',
                'commission_percent',
                'graphics_charge',
                'survey_charge',
                'printing_charge',
                'mounting_charge',
                'remounting_charge',
                'lighting_charge',
                'discount_type',
                'discount_value',
                'availability',
                'currency',
                'available_from',
                'available_to',
            ]);
        });
    }
};
