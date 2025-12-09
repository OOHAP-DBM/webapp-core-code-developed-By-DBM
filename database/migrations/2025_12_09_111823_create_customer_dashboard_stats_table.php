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
        Schema::create('customer_dashboard_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Booking Stats
            $table->integer('total_bookings')->default(0);
            $table->integer('active_bookings')->default(0);
            $table->integer('completed_bookings')->default(0);
            $table->integer('cancelled_bookings')->default(0);
            $table->decimal('total_booking_amount', 12, 2)->default(0);
            
            // Payment Stats
            $table->integer('total_payments')->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('total_pending', 12, 2)->default(0);
            $table->decimal('total_refunded', 12, 2)->default(0);
            
            // Enquiry Stats
            $table->integer('total_enquiries')->default(0);
            $table->integer('pending_enquiries')->default(0);
            $table->integer('responded_enquiries')->default(0);
            
            // Offer Stats
            $table->integer('total_offers')->default(0);
            $table->integer('active_offers')->default(0);
            $table->integer('accepted_offers')->default(0);
            
            // Quotation Stats
            $table->integer('total_quotations')->default(0);
            $table->integer('pending_quotations')->default(0);
            $table->integer('approved_quotations')->default(0);
            
            // Invoice Stats
            $table->integer('total_invoices')->default(0);
            $table->integer('paid_invoices')->default(0);
            $table->integer('unpaid_invoices')->default(0);
            $table->decimal('total_invoice_amount', 12, 2)->default(0);
            
            // Thread Stats
            $table->integer('total_threads')->default(0);
            $table->integer('unread_threads')->default(0);
            
            // Cache timestamps
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_dashboard_stats');
    }
};
