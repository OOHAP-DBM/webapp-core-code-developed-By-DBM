<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disclaimers', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            // e.g. "Disclaimer update"

            $table->longText('content');
            // Full disclaimer paragraph(s)

            $table->date('effective_date')->nullable();
            // optional: for "updated on" logic

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disclaimers');
    }
};
