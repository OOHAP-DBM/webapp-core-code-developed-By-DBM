<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_booking_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_booking_id');
            $table->timestamp('scheduled_at');
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->text('error_message')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('pos_booking_id')
                ->references('id')
                ->on('pos_bookings')
                ->onDelete('cascade');

            $table->index(['pos_booking_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_booking_reminders');
    }
};
