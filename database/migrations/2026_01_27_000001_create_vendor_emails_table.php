<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('email');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Verification tracking
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            // Indexes
            $table->unique(['user_id', 'email']);
            $table->index('user_id');
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_emails');
    }
};
