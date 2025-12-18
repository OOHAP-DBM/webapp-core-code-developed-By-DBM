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
        Schema::create('booking_creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('type')->default('reference'); // 'design' or 'reference'
            $table->string('file_path')->nullable(); // Path to uploaded file
            $table->string('file_name')->nullable();
            $table->string('file_mime')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status', ['pending_approval', 'approved', 'rejected'])->default('pending_approval');
            $table->text('notes')->nullable();
            $table->boolean('has_graphics_designer')->default(false);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_creatives');
    }
};
