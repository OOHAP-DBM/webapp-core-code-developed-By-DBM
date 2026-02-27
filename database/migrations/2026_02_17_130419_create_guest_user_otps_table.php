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
        Schema::create('guest_user_otps', function (Blueprint $table) {
            $table->id();
              // Identifier (phone or email) - NO user_id needed!
            $table->string('identifier'); // e.g., "9876543210" or "user@example.com"
            
            // OTP details
            $table->string('otp', 4);
            $table->string('purpose')->default('verification'); // direct_enquiry, login, etc.
            
            // Timestamps
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['identifier', 'purpose']);
            $table->index('expires_at');
            $table->index('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_user_otps');
    }
};
