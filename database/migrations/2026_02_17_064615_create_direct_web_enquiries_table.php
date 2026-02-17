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
        Schema::create('direct_web_enquiries', function (Blueprint $table) {
            $table->id();
            
            // Customer Information
            $table->string('name');
            $table->string('email');
            $table->string('phone', 10);
            $table->boolean('is_phone_verified')->default(false);
            
            // Hoarding Requirements
            $table->string('hoarding_type'); // DOOH, OOH (comma-separated)
            $table->string('location_city');
            $table->json('preferred_locations')->nullable(); // Array of locality names
            $table->text('remarks');
            
            // Communication Preferences
            $table->json('preferred_modes')->nullable(); // Call, WhatsApp, Email
            
            // Status & Tracking
            $table->enum('status', [
                'new', 
                'contacted', 
                'quote_sent', 
                'negotiating', 
                'confirmed', 
                'rejected', 
                'expired'
            ])->default('new');
            
            $table->string('source')->default('website'); // website, mobile_app, referral
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            // Metadata
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('quote_sent_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('phone');
            $table->index('email');
            $table->index('location_city');
            $table->index('status');
            $table->index('created_at');
        });

        // Pivot table for enquiry-vendor relationship
        Schema::create('enquiry_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained('direct_web_enquiries')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('has_viewed')->default(false);
            $table->timestamp('viewed_at')->nullable();
            $table->enum('response_status', ['pending', 'interested', 'quote_sent', 'declined'])->default('pending');
            $table->text('vendor_notes')->nullable();
            $table->timestamps();
            
            $table->unique(['enquiry_id', 'vendor_id']);
            $table->index('vendor_id');
            $table->index('response_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiry_vendor');
        Schema::dropIfExists('direct_web_enquiries');
    }
};
