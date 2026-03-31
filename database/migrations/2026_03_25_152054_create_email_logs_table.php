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
    Schema::create('email_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('template_id')
              ->constrained('email_templates')
              ->onDelete('restrict');
        $table->string('recipient_email');
        $table->string('subject_final');      // subject after variable replace
        $table->longText('body_final');       // final rendered HTML
        $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
        $table->text('error_message')->nullable(); // agar fail ho
        $table->timestamp('sent_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('email_logs');
}
};
