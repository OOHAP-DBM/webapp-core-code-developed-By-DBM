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
    Schema::create('email_template_variables', function (Blueprint $table) {
        $table->id();
        $table->foreignId('template_id')
              ->constrained('email_templates')
              ->onDelete('cascade');
        $table->string('variable_name');   // e.g. 'name'
        $table->string('variable_type')->default('string'); // string/number/url
        $table->boolean('is_required')->default(true);
        $table->string('default_value')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('email_template_variables');
}
};
