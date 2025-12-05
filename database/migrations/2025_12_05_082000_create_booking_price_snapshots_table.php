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
        Schema::create('booking_price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            
            // Immutable quotation snapshot (full quotation data at time of booking)
            $table->json('quotation_snapshot');
            
            // Price breakdown (extracted from quotation for quick access)
            $table->decimal('services_price', 10, 2)->comment('Base price for all services/line items');
            $table->decimal('discounts', 10, 2)->default(0)->comment('Total discounts applied');
            $table->decimal('taxes', 10, 2)->default(0)->comment('Total taxes (GST, etc.)');
            $table->decimal('total_amount', 10, 2)->comment('Final total amount after discounts and taxes');
            
            // Currency
            $table->string('currency', 10)->default('INR');
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('booking_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_price_snapshots');
    }
};
