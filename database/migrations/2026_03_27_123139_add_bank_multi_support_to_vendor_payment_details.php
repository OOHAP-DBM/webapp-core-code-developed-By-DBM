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
        Schema::table('vendor_payment_details', function (Blueprint $table) {
            $table->unique(
                ['vendor_id', 'type', 'account_number'],
                'vendor_payment_details_vendor_type_account_unique'
            );
            // Only one bank can be default at a time per vendor
            $table->boolean('is_default')->default(false)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_payment_details', function (Blueprint $table) {
           $table->dropUnique('vendor_payment_details_vendor_type_account_unique');

            $table->dropColumn('is_default');

            $table->unique(
                ['vendor_id', 'type'],
                'vendor_payment_details_vendor_id_type_unique'
            );
        });
    }
};
