<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PROMPT 105: Offer Auto-Expiry Logic
     * Add fields to support configurable offer expiry
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            // Expiry configuration
            $table->integer('expiry_days')->nullable()->after('valid_until')
                ->comment('Number of days until offer expires (null = use system default)');
            
            $table->timestamp('expires_at')->nullable()->after('expiry_days')
                ->comment('Calculated expiry timestamp (sent_at + expiry_days)');
            
            $table->timestamp('sent_at')->nullable()->after('expires_at')
                ->comment('When offer was sent to customer');
            
            $table->timestamp('expired_at')->nullable()->after('sent_at')
                ->comment('When offer was marked as expired');
            
            // Index for performance
            $table->index('expires_at');
            $table->index(['status', 'expires_at']);
        });

        // Backfill sent_at for existing sent/accepted/rejected/expired offers
        DB::statement("
            UPDATE offers 
            SET sent_at = created_at 
            WHERE status IN ('sent', 'accepted', 'rejected', 'expired') 
            AND sent_at IS NULL
        ");

        // Calculate expires_at for existing offers that have valid_until
        DB::statement("
            UPDATE offers 
            SET expires_at = valid_until 
            WHERE valid_until IS NOT NULL 
            AND expires_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex(['offers_expires_at_index']);
            $table->dropIndex(['offers_status_expires_at_index']);
            
            $table->dropColumn([
                'expiry_days',
                'expires_at',
                'sent_at',
                'expired_at',
            ]);
        });
    }
};
