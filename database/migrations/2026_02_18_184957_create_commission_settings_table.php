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
        Schema::create('commission_settings', function (Blueprint $table) {
            $table->id();
             $table->foreignId('vendor_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->enum('hoarding_type', ['all', 'ooh', 'dooh'])->default('all');
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('locality', 100)->nullable();
            $table->decimal('commission_percent', 5, 2);
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'hoarding_type', 'state', 'city'], 'uq_commission');
            $table->index(['vendor_id', 'hoarding_type']);
            $table->index(['state', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_settings');
    }
};
