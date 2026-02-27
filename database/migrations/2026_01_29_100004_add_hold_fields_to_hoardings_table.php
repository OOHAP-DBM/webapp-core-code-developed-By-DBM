<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
            $table->boolean('is_on_hold')->default(false)->after('status');
            $table->timestamp('hold_till')->nullable()->after('is_on_hold');
            $table->unsignedBigInteger('held_by_booking_id')->nullable()->after('hold_till');
            $table->foreign('held_by_booking_id')->references('id')->on('pos_bookings')->onDelete('set null');
            $table->index(['is_on_hold', 'hold_till']);
        });
    }
    public function down(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
            $table->dropForeign(['held_by_booking_id']);
            $table->dropColumn(['is_on_hold', 'hold_till', 'held_by_booking_id']);
        });
    }
};
