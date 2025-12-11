<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PROMPT 67: DOOH Creative assets management
     */
    public function up(): void
    {
        Schema::create('dooh_creatives', function (Blueprint $table) {
            $table->id();
            
            // Ownership & booking reference
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->foreignId('dooh_screen_id')->nullable()->constrained('dooh_screens')->onDelete('set null');
            
            // Creative details
            $table->string('creative_name');
            $table->text('description')->nullable();
            $table->enum('creative_type', ['video', 'image', 'html5', 'gif'])->default('video');
            
            // File information
            $table->string('file_path'); // storage/app/public/dooh_creatives/...
            $table->string('file_url'); // Public URL
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size_bytes'); // In bytes
            
            // Media specifications
            $table->string('resolution')->nullable(); // e.g., "1920x1080"
            $table->integer('width_pixels')->nullable();
            $table->integer('height_pixels')->nullable();
            $table->integer('duration_seconds')->nullable(); // For videos
            $table->decimal('fps', 5, 2)->nullable(); // Frames per second
            $table->string('codec')->nullable(); // Video codec (h264, h265, etc.)
            $table->integer('bitrate_kbps')->nullable(); // Video bitrate
            
            // Validation & approval
            $table->enum('validation_status', [
                'pending',
                'validating',
                'approved',
                'rejected',
                'revision_required'
            ])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('validation_notes')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            
            // Technical validation results
            $table->json('validation_results')->nullable(); // Stores detailed validation checks
            $table->boolean('format_valid')->default(false);
            $table->boolean('resolution_valid')->default(false);
            $table->boolean('duration_valid')->default(false);
            $table->boolean('file_size_valid')->default(false);
            $table->boolean('content_policy_valid')->default(false);
            
            // Scheduling metadata
            $table->boolean('is_active')->default(true);
            $table->integer('total_schedules')->default(0); // Count of active schedules
            $table->timestamp('first_scheduled_at')->nullable();
            $table->timestamp('last_scheduled_at')->nullable();
            
            // Processing status (for transcoding, optimization)
            $table->enum('processing_status', [
                'pending',
                'processing',
                'completed',
                'failed'
            ])->default('pending');
            $table->text('processing_error')->nullable();
            $table->string('thumbnail_path')->nullable(); // Auto-generated thumbnail
            $table->string('preview_url')->nullable(); // Preview for admin/customer
            
            // Metadata & tags
            $table->json('metadata')->nullable(); // Additional metadata (aspect ratio, color space, etc.)
            $table->json('tags')->nullable(); // Customer tags for organization
            
            // Status & tracking
            $table->enum('status', ['draft', 'active', 'archived', 'deleted'])->default('draft');
            $table->timestamp('uploaded_at');
            $table->timestamp('archived_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('customer_id');
            $table->index('booking_id');
            $table->index('dooh_screen_id');
            $table->index(['validation_status', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dooh_creatives');
    }
};
