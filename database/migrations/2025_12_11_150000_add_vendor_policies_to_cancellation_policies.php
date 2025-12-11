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
        Schema::table('cancellation_policies', function (Blueprint $table) {
            // Add vendor_id for vendor-specific policies
            $table->foreignId('vendor_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('NULL = global/admin policy, specific ID = vendor custom policy');
            
            // Add campaign start enforcement
            $table->boolean('enforce_campaign_start')
                ->default(true)
                ->after('auto_refund_enabled')
                ->comment('No refund after campaign/booking starts');
            
            // Add flexible refund tiers
            $table->boolean('allow_partial_refund')
                ->default(true)
                ->after('enforce_campaign_start')
                ->comment('Allow partial refunds based on time windows');
            
            // Index for vendor lookup
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cancellation_policies', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropColumn([
                'vendor_id',
                'enforce_campaign_start',
                'allow_partial_refund',
            ]);
        });
    }
};
