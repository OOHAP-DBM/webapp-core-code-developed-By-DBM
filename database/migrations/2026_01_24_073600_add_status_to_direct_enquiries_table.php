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
           $table->string('status')->default('pending'); // pending, mailed, failed
           $table->timestamp('mailed_at')->nullable();
           $table->text('failure_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_enquiries', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'mailed_at',
                'failure_reason',
            ]);
        });
    }
};
