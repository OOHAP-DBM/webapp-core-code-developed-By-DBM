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
       Schema::table('pos_bookings', function (Blueprint $table) {
            $table->boolean('is_milestone')->default(false)->after('payment_mode');
            $table->decimal('milestone_total', 10, 2)->nullable()->after('is_milestone');
            $table->integer('milestone_paid')->default(0)->after('milestone_total');
            $table->decimal('milestone_amount_paid', 10, 2)->default(0)->after('milestone_paid');
            $table->decimal('milestone_amount_remaining', 10, 2)->nullable()->after('milestone_amount_paid');
            $table->unsignedBigInteger('current_milestone_id')->nullable()->after('milestone_amount_remaining');
            $table->timestamp('all_milestones_paid_at')->nullable()->after('current_milestone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('pos_bookings', function (Blueprint $table) {

            $table->dropColumn([
                'is_milestone',
                'milestone_total',
                'milestone_paid',
                'milestone_amount_paid',
                'milestone_amount_remaining',
                'current_milestone_id',
                'all_milestones_paid_at'
            ]);
        });
    }
};
