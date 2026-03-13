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
        Schema::table('quotation_milestones', function (Blueprint $table) {
             Schema::table('quotation_milestones', function (Blueprint $table) {
            // Make quotation_id nullable (was required; POS bookings have no quotation)
            $table->unsignedBigInteger('quotation_id')->nullable()->change();
 
            // Add pos_booking_id — nullable so existing quotation milestones are unaffected
            if (!Schema::hasColumn('quotation_milestones', 'pos_booking_id')) {
                $table->unsignedBigInteger('pos_booking_id')
                    ->nullable()
                    ->after('quotation_id')
                    ->comment('Set only for POS bookings that have no quotation');
 
                $table->foreign('pos_booking_id')
                    ->references('id')
                    ->on('pos_bookings')
                    ->cascadeOnDelete();
 
                $table->index('pos_booking_id');
            }
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_milestones', function (Blueprint $table) {
            $table->dropForeign(['pos_booking_id']);
            $table->dropColumn('pos_booking_id');
            // Revert quotation_id to required
            $table->unsignedBigInteger('quotation_id')->nullable(false)->change();
        });
    }
};
