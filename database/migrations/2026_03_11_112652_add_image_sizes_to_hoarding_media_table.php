<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoarding_media', function (Blueprint $table) {
            $table->string('path_100')->nullable()->after('file_path');
            $table->string('path_300')->nullable()->after('path_100');
            $table->string('path_600')->nullable()->after('path_300');
            $table->string('path_1000')->nullable()->after('path_600');
            $table->string('path_1500')->nullable()->after('path_1000');
            $table->string('mime_type')->nullable()->after('path_1500');
        });
    }

    public function down(): void
    {
        Schema::table('hoarding_media', function (Blueprint $table) {
            $table->dropColumn(['path_100', 'path_300', 'path_600', 'path_1000', 'path_1500', 'mime_type']);
        });
    }
};