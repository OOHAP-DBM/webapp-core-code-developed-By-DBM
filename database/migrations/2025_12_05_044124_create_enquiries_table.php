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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->date('preferred_start_date');
            $table->date('preferred_end_date');
            $table->enum('duration_type', ['days', 'weeks', 'months'])->default('months');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->json('snapshot')->nullable(); // Captures hoarding price & flags at enquiry time
            $table->timestamps();

            // Indexes
            $table->index('customer_id');
            $table->index('hoarding_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
