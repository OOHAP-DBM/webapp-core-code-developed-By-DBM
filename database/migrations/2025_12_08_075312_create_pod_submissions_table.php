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
        Schema::create('pod_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade'); // Mounter
            $table->timestamp('submission_date');
            $table->json('files'); // Array of uploaded photos/videos
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Vendor
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null'); // Vendor
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('booking_id');
            $table->index('submitted_by');
            $table->index('status');
            $table->index('submission_date');
        });

        // Add campaign tracking columns to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('campaign_started_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('campaign_started_at');
        });

        Schema::dropIfExists('pod_submissions');
    }
};
