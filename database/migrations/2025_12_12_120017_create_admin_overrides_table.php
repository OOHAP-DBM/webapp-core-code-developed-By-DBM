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
        Schema::create('admin_overrides', function (Blueprint $table) {
            $table->id();
            
            // Admin who made the override
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('user_name');
            $table->string('user_email');
            
            // Polymorphic relationship to the overridden model
            $table->morphs('overridable');
            
            // Override action details
            $table->string('action'); // 'update', 'status_change', 'payment_override', etc.
            $table->string('field_changed')->nullable(); // Specific field changed (if applicable)
            
            // Data snapshots
            $table->json('original_data'); // State before override
            $table->json('new_data'); // State after override
            $table->json('changes'); // Specific changes made
            
            // Override reason and justification
            $table->text('reason'); // Required reason for override
            $table->text('notes')->nullable(); // Additional notes
            
            // Revert tracking
            $table->boolean('is_reverted')->default(false);
            $table->timestamp('reverted_at')->nullable();
            $table->foreignId('reverted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('revert_reason')->nullable();
            $table->json('revert_data')->nullable(); // Data state after revert
            
            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Categorization
            $table->string('override_type'); // 'booking', 'payment', 'offer', 'quote', 'commission', 'vendor_kyc'
            $table->string('severity')->default('medium'); // 'low', 'medium', 'high', 'critical'
            
            // Additional metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index(['user_id', 'created_at']);
            $table->index(['override_type', 'created_at']);
            $table->index(['is_reverted', 'created_at']);
            $table->index(['overridable_type', 'overridable_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_overrides');
    }
};
