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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained('enquiries')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('price', 12, 2);
            $table->enum('price_type', ['total', 'monthly', 'weekly', 'daily'])->default('monthly');
            $table->json('price_snapshot')->nullable(); // Immutable snapshot of pricing details
            $table->text('description')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->integer('version')->default(1); // Version number for multiple offers per enquiry
            $table->timestamps();

            // Indexes
            $table->index('enquiry_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index(['enquiry_id', 'version']); // For version lookup
            $table->index('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
