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
        Schema::create('booking_timeline_events', function (Blueprint $table) {
            $table->id();
            
            // Booking reference
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            
            // Event details
            $table->string('event_type')->comment('enquiry, offer, quotation, payment, graphics, printing, mounting, proof, campaign_start, campaign_running, campaign_completed, etc.');
            $table->string('event_category')->comment('booking, payment, production, campaign');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'cancelled'])->default('pending');
            
            // References to related entities
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID of related entity (offer_id, quotation_id, payment_id, etc.)');
            $table->string('reference_type')->nullable()->comment('Type of related entity');
            
            // Version tracking (for offers, quotations)
            $table->integer('version')->nullable()->comment('Version number for versioned entities');
            
            // User and metadata
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable();
            $table->json('metadata')->nullable()->comment('Additional event data');
            
            // Timing
            $table->timestamp('scheduled_at')->nullable()->comment('When event is scheduled to occur');
            $table->timestamp('started_at')->nullable()->comment('When event started');
            $table->timestamp('completed_at')->nullable()->comment('When event completed');
            $table->integer('duration_minutes')->nullable()->comment('Duration in minutes');
            
            // Display and ordering
            $table->integer('order')->default(0)->comment('Display order in timeline');
            $table->string('icon')->nullable()->comment('Icon class for UI');
            $table->string('color')->nullable()->comment('Color for UI (e.g., success, warning, danger)');
            
            // Notifications
            $table->boolean('notify_customer')->default(false);
            $table->boolean('notify_vendor')->default(false);
            $table->timestamp('notified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('event_type');
            $table->index('event_category');
            $table->index('status');
            $table->index(['booking_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_timeline_events');
    }
};
