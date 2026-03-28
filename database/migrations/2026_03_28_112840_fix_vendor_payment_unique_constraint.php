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

                // ✅ Drop old unique (THIS is the correct name)
                $table->dropUnique('vendor_payment_details_vendor_id_type_unique');

                // ✅ Optional: prevent duplicate same account number per vendor
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

            // rollback
            $table->dropUnique('vendor_payment_details_vendor_account_unique');

            $table->unique(
                ['vendor_id', 'type'],
                'vendor_payment_details_vendor_id_type_unique'
            );
        });
    }
};
