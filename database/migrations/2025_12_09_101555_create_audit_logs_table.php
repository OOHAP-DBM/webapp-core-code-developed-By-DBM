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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship to any model
            $table->string('auditable_type')->index();
            $table->unsignedBigInteger('auditable_id')->index();
            
            // User who performed the action
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_type')->default('admin')->comment('admin, vendor, customer');
            $table->string('user_name')->nullable()->comment('Cached user name for history');
            $table->string('user_email')->nullable()->comment('Cached user email for history');
            
            // Action details
            $table->enum('action', ['created', 'updated', 'deleted', 'restored', 'status_changed', 'price_changed', 'other']);
            $table->string('event')->nullable()->comment('Custom event name if action=other');
            $table->text('description')->nullable()->comment('Human-readable description of change');
            
            // Change tracking
            $table->json('old_values')->nullable()->comment('Before state of changed fields');
            $table->json('new_values')->nullable()->comment('After state of changed fields');
            $table->json('changed_fields')->nullable()->comment('Array of field names that changed');
            
            // Context and metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_method')->nullable()->comment('GET, POST, PUT, DELETE');
            $table->string('request_url')->nullable();
            $table->json('metadata')->nullable()->comment('Additional context data');
            
            // Categorization
            $table->string('module')->nullable()->comment('e.g., booking, payment, commission');
            $table->string('tags')->nullable()->comment('Comma-separated tags for filtering');
            
            // Timestamp (only created_at, logs are immutable)
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for efficient querying
            $table->index(['auditable_type', 'auditable_id'], 'auditable_index');
            $table->index('created_at');
            $table->index('user_id');
            $table->index('action');
            $table->index('module');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
