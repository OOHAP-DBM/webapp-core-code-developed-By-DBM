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
        Schema::create('thread_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('threads')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->enum('sender_type', ['customer', 'vendor', 'admin'])->default('customer');
            $table->enum('message_type', ['text', 'offer', 'quotation', 'system'])->default('text');
            $table->text('message')->nullable();
            $table->json('attachments')->nullable(); // File attachments
            $table->foreignId('offer_id')->nullable()->constrained('offers')->onDelete('set null');
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->onDelete('set null');
            $table->boolean('is_read_customer')->default(false);
            $table->boolean('is_read_vendor')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('thread_id');
            $table->index('sender_id');
            $table->index('message_type');
            $table->index('created_at');
            $table->index(['thread_id', 'created_at']); // For message ordering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thread_messages');
    }
};
