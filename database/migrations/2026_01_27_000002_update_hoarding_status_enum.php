<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
            // Add new status fields - published_at for auto-approval tracking
            $table->timestamp('published_at')->nullable()->after('status');
            $table->string('preview_token')->nullable()->unique()->after('published_at');
            
            // Add publisher tracking
            $table->unsignedBigInteger('published_by')->nullable()->after('preview_token');
        });

        // Update enum values
        DB::statement("ALTER TABLE hoardings MODIFY status ENUM('draft', 'preview', 'published', 'inactive', 'suspended') DEFAULT 'draft'");
    }

    public function down(): void
    {
        Schema::table('hoardings', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'preview_token', 'published_by']);
        });

        // Revert enum values
        DB::statement("ALTER TABLE hoardings MODIFY status ENUM('draft', 'pending_approval', 'active', 'inactive', 'suspended') DEFAULT 'draft'");
    }
};
