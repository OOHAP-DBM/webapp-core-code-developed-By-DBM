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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            
            // Template reference
            $table->foreignId('notification_template_id')->nullable()->constrained()->nullOnDelete();
            
            // Recipient info
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_type', 50)->nullable(); // user, vendor, admin
            $table->string('recipient_identifier'); // email, phone, user_id
            
            // Notification details
            $table->string('event_type', 50);
            $table->string('channel', 20); // email, sms, whatsapp, web
            $table->string('subject')->nullable();
            $table->text('body');
            $table->text('html_body')->nullable();
            
            // Delivery tracking
            $table->string('status', 20)->default('pending'); // pending, sent, delivered, failed, read
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // Response from provider
            $table->string('provider', 50)->nullable(); // smtp, razorpay_sms, twilio, etc.
            $table->string('provider_message_id')->nullable();
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            
            // Related entity
            $table->morphs('related'); // enquiry, booking, offer, etc.
            
            // Retry tracking
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            
            // Metadata
            $table->json('placeholders_data')->nullable(); // The actual data used
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'channel', 'status']);
            $table->index(['event_type', 'channel']);
            $table->index('status');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
