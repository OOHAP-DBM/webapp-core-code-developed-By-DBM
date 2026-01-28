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
        Schema::table('direct_enquiries', function (Blueprint $table) {
            //   $table->json('preferred_locations')->nullable(); // multiple locations
            $table->json('preferred_modes')->nullable(); // call, whatsapp, email
            // $table->string('best_way_to_connect')->nullable();
            $table->string('hoarding_location')->nullable()->change();
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_phone_verified')->default(false);


        });
    }
    public function down(): void
    {
        Schema::table('direct_enquiries', function (Blueprint $table) {
            $table->dropColumn([
                // 'preferred_locations',
                'hoarding_location',
                'preferred_modes',
                'best_way_to_connect',
                'is_email_verified',
                'is_phone_verified'
                
            ]);
        });
    }

};
