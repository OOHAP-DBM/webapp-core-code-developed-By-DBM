<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::table('ratings', function (Blueprint $table) {
        // Pehle foreign keys drop karo
        $table->dropForeign(['user_id']);
        $table->dropForeign(['hoarding_id']);

        // Ab unique constraint drop karo
        $table->dropUnique('ratings_user_id_hoarding_id_unique');

        // Foreign keys wapas lagao (without unique)
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('hoarding_id')->references('id')->on('hoardings')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('ratings', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropForeign(['hoarding_id']);
        $table->unique(['user_id', 'hoarding_id']);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('hoarding_id')->references('id')->on('hoardings')->onDelete('cascade');
    });
}
};
