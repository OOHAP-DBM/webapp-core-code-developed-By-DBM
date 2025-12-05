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
        Schema::table('vendor_kyc', function (Blueprint $table) {
            $table->enum('payout_status', [
                'pending_verification',
                'verified',
                'rejected',
                'failed'
            ])->default('pending_verification')->after('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_kyc', function (Blueprint $table) {
            $table->dropColumn('payout_status');
        });
    }
};
