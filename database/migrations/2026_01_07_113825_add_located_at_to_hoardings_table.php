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
            $table->json('located_at')->nullable()->after('visibility_details');
        });
        Schema::table('dooh_screens', function (Blueprint $table) {
            $table->json('price_per_10_sec_slot')->nullable()->after('display_price_per_30s');
        });
        Schema::table('dooh_screens', function (Blueprint $table) {
            $table->decimal('width', 8, 2)->nullable()->after('screen_type');
            $table->decimal('height', 8, 2)->nullable()->after('width');
            $table->string('measurement_unit', 10)->nullable()->after('height');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
            $table->dropColumn('located_at');
        });
        Schema::table('dooh_screens', function (Blueprint $table) {
            $table->dropColumn('price_per_10_sec_slot');
            $table->dropColumn('width');
            $table->dropColumn('height');
            $table->dropColumn('measurement_unit');
        });   
    }
};
