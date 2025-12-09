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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            
            // Template identification
            $table->string('name', 100); // e.g., "OTP Email", "Offer Created"
            $table->string('slug', 100)->unique(); // e.g., "otp_email", "offer_created"
            $table->string('event_type', 50); // otp, enquiry_received, offer_created, offer_accepted, etc.
            $table->string('channel', 20); // email, sms, whatsapp, web
            $table->text('description')->nullable();
            
            // Template content
            $table->string('subject')->nullable(); // For email
            $table->text('body'); // Template body with placeholders
            $table->text('html_body')->nullable(); // HTML version for email
            $table->json('metadata')->nullable(); // Channel-specific settings
            
            // Placeholders info
            $table->json('available_placeholders')->nullable(); // List of available placeholders
            
            // Configuration
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_default')->default(false); // Cannot be deleted
            $table->integer('priority')->default(0); // For ordering
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index(['event_type', 'channel']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
