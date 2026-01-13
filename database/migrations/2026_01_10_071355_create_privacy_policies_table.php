<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('privacy_policies', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            // e.g. "Privacy Policy"

            $table->longText('content');
            // Full privacy policy text (all sections, HTML allowed)

            $table->date('effective_date')->nullable();
            // "Last updated" date

            $table->boolean('is_active')->default(true);
            // Only one active policy shown on site

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_policies');
    }
};

