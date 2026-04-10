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
    Schema::create('email_layouts', function (Blueprint $table) {
        $table->id();
        $table->string('logo_url')->nullable();
        $table->text('header_html')->nullable();
        $table->text('footer_html')->nullable();
        $table->string('primary_color', 10)->default('#000000');
        $table->string('font_family')->default('Arial');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('email_layouts');
}
};
