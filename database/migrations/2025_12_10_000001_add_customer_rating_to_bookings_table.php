<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROMPT 54: Add customer rating column for vendor rating calculations
 * Required for SmartSearchService vendor rating scoring
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Customer rating for vendor (used in smart search scoring)
            $table->decimal('customer_rating', 3, 2)->nullable()->after('status')
                ->comment('Customer rating for vendor (0.00-5.00 stars)');
            
            // Optional: Add review text
            $table->text('customer_review')->nullable()->after('customer_rating')
                ->comment('Customer review text for vendor');
            
            // Timestamp for when rating was given
            $table->timestamp('rated_at')->nullable()->after('customer_review')
                ->comment('When customer rated the vendor');
            
            // Index for fast vendor rating lookups
            $table->index(['vendor_id', 'customer_rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['vendor_id', 'customer_rating']);
            $table->dropColumn(['customer_rating', 'customer_review', 'rated_at']);
        });
    }
};
