<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direct_enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('direct_enquiries', 'preferred_location')) {
                $table->text('preferred_location')
                      ->nullable()
                      ->after('location_city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('direct_enquiries', function (Blueprint $table) {
            if (Schema::hasColumn('direct_enquiries', 'preferred_location')) {
                $table->dropColumn('preferred_location');
            }
        });
    }
};
