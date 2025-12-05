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
        Schema::create('razorpay_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 50)->index(); // create_order, capture_payment, verify_payment, etc.
            $table->json('request_payload')->nullable(); // Request sent to Razorpay
            $table->json('response_payload')->nullable(); // Response from Razorpay
            $table->integer('status_code')->nullable(); // HTTP status code
            $table->json('metadata')->nullable(); // Additional context (booking_id, payment_id, etc.)
            $table->timestamps();

            // Index for faster lookups
            $table->index('created_at');
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razorpay_logs');
    }
};
