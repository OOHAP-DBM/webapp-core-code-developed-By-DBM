<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop mobile OTP columns if they exist
            if (Schema::hasColumn('users', 'mobile_otp')) {
                $table->dropColumn('mobile_otp');
            }
            if (Schema::hasColumn('users', 'mobile_otp_expires_at')) {
                $table->dropColumn('mobile_otp_expires_at');
            }
            if (Schema::hasColumn('users', 'mobile_otp_attempts')) {
                $table->dropColumn('mobile_otp_attempts');
            }
            if (Schema::hasColumn('users', 'mobile_otp_last_sent_at')) {
                $table->dropColumn('mobile_otp_last_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile_otp')->nullable();
            $table->timestamp('mobile_otp_expires_at')->nullable();
            $table->integer('mobile_otp_attempts')->default(0);
            $table->timestamp('mobile_otp_last_sent_at')->nullable();
        });
    }
};
