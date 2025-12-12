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
        Schema::table('users', function (Blueprint $table) {
            // Store currently active role for multi-role users (PROMPT 96)
            $table->string('active_role')->nullable()->after('status');
            
            // Track role switching history
            $table->timestamp('last_role_switch_at')->nullable()->after('active_role');
            $table->string('previous_role')->nullable()->after('last_role_switch_at');
            
            $table->index('active_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['active_role']);
            $table->dropColumn(['active_role', 'last_role_switch_at', 'previous_role']);
        });
    }
};
