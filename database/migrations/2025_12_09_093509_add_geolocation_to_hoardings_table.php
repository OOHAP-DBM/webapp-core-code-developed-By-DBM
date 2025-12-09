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
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('hoardings', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
            }
            if (!Schema::hasColumn('hoardings', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('hoardings', 'geolocation_verified')) {
                $table->boolean('geolocation_verified')->default(false)->after('longitude');
            }
            if (!Schema::hasColumn('hoardings', 'geolocation_source')) {
                $table->string('geolocation_source', 50)->nullable()->after('geolocation_verified')
                    ->comment('manual, google_maps, gps, etc.');
            }
            
            // View/popularity tracking
            if (!Schema::hasColumn('hoardings', 'views_count')) {
                $table->integer('views_count')->default(0)->after('status');
            }
            if (!Schema::hasColumn('hoardings', 'bookings_count')) {
                $table->integer('bookings_count')->default(0)->after('views_count');
            }
            if (!Schema::hasColumn('hoardings', 'last_booked_at')) {
                $table->timestamp('last_booked_at')->nullable()->after('bookings_count');
            }
            
            // Add indexes for geolocation queries
            $table->index(['latitude', 'longitude'], 'hoardings_location_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
            $table->dropIndex('hoardings_location_index');
            $table->dropColumn([
                'latitude',
                'longitude',
                'geolocation_verified',
                'geolocation_source',
                'views_count',
                'bookings_count',
                'last_booked_at'
            ]);
        });
    }
};
