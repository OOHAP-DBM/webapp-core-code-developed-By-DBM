<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cancellation_refund_policies', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            // e.g. "Cancellation & Refund Policy"

            $table->longText('content');
            // Full policy text (points 1–5, notes, email, HTML allowed)

            $table->date('effective_date')->nullable();
            // Last updated date

            $table->boolean('is_active')->default(true);
            // Only active policy visible on frontend

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_refund_policies');
    }
};

