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
    Schema::create('email_templates', function (Blueprint $table) {
        $table->id();
        $table->foreignId('layout_id')
              ->constrained('email_layouts')
              ->onDelete('restrict');
        $table->string('template_key')->unique(); // e.g. 'welcome_user'
        $table->string('name');                   // e.g. 'Welcome Email'
        $table->string('subject');                // e.g. 'Welcome {{name}}'
        $table->longText('body_html');            // dynamic content
        $table->json('variables_schema')->nullable(); // ["name","email"]
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('email_templates');
}
};
