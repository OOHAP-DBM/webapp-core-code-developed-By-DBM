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
        Schema::table('invoices', function (Blueprint $table) {
            // Add quotation_milestone_id for milestone payment invoices
            $table->foreignId('quotation_milestone_id')
                ->nullable()
                ->after('booking_payment_id')
                ->constrained('quotation_milestones')
                ->onDelete('set null');
            
            // Add index for faster lookups
            $table->index('quotation_milestone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['quotation_milestone_id']);
            $table->dropIndex(['quotation_milestone_id']);
            $table->dropColumn('quotation_milestone_id');
        });
    }
};
