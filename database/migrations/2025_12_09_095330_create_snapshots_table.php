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
        Schema::create('snapshots', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship to snapshotted entity
            $table->string('snapshotable_type'); // Model class name
            $table->unsignedBigInteger('snapshotable_id'); // Model ID
            $table->index(['snapshotable_type', 'snapshotable_id'], 'snapshot_polymorphic_index');
            
            // Snapshot metadata
            $table->string('snapshot_type', 50); // offer, quotation, price_update, booking, commission_rule
            $table->string('event', 100); // created, updated, price_changed, confirmed, etc.
            $table->unsignedInteger('version')->default(1); // Snapshot version number
            
            // Immutable JSON snapshot of the entity at this point in time
            $table->json('data'); // Complete state of the entity
            $table->json('changes')->nullable(); // What changed (for update events)
            $table->json('metadata')->nullable(); // Additional context (user_id, ip, reason, etc.)
            
            // Audit trail
            $table->unsignedBigInteger('created_by')->nullable(); // User who triggered this snapshot
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Immutability: No updated_at, only created_at
            $table->timestamp('created_at')->useCurrent();
            
            // Indexing for fast queries
            $table->index('snapshot_type');
            $table->index('event');
            $table->index('version');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
