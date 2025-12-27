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
        Schema::create('hoardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->decimal('weekly_price', 10, 2)->nullable();
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->boolean('enable_weekly_booking')->default(true);
            $table->enum('type', ['billboard', 'digital', 'transit', 'street_furniture', 'wallscape', 'mobile'])->default('billboard');
            $table->enum('status', ['draft', 'pending_approval', 'active', 'inactive', 'suspended'])->default('draft');
            $table->boolean('is_featured')->nullable();
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('vendor_id');
            $table->index('status');
            $table->index('type');
            $table->index(['lat', 'lng']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoardings');
    }
};
