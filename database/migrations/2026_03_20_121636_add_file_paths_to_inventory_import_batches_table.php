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
        Schema::table('inventory_import_batches', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('invalid_rows');
            $table->string('ppt_path')->nullable()->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_import_batches', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'ppt_path']);
        });
    }
};
