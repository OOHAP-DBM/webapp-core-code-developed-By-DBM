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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained('users');
            $table->string('source', 30)->nullable(); // web, app, agency

            // enquiry lifecycle
            $table->enum('status', [
                'draft',
                'submitted',
                'responded',
                'cancelled'
            ])->default('draft');

            // meta
            $table->text('customer_note')->nullable();

            $table->timestamps();

            /* INDEXES */
            $table->index(['customer_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
