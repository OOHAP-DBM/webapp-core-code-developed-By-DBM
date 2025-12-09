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
        Schema::create('booking_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hoarding_id')->constrained('hoardings')->onDelete('cascade');
            $table->unsignedBigInteger('package_id')->nullable(); // Will add FK after dooh_packages migration
            
            // Date selection
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_days')->nullable();
            $table->enum('duration_type', ['days', 'weeks', 'months'])->nullable();
            
            // Price snapshot - frozen at time of draft creation
            $table->json('price_snapshot')->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            
            // Applied offers/discounts
            $table->json('applied_offers')->nullable();
            $table->string('coupon_code')->nullable();
            
            // Draft metadata
            $table->enum('step', ['hoarding_selected', 'package_selected', 'dates_selected', 'review', 'payment_pending'])->default('hoarding_selected');
            $table->timestamp('last_updated_step_at')->nullable();
            $table->string('session_id')->nullable()->index();
            
            // Expiry and conversion tracking
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_converted')->default(false);
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->timestamp('converted_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['customer_id', 'is_converted']);
            $table->index(['hoarding_id', 'start_date', 'end_date']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_drafts');
    }
};
