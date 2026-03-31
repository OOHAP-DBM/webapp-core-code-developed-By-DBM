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
            // ✅ New constraint
            $table->unique(
                ['vendor_id', 'account_number'],
                'vendor_payment_details_vendor_account_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('vendor_payment_details', function (Blueprint $table) {

            try {
                $table->dropUnique('vendor_payment_details_vendor_account_unique');
            } catch (\Exception $e) {}

            $table->unique(
                ['vendor_id', 'type'],
                'vendor_payment_details_vendor_id_type_unique'
            );
        });
    }
};
