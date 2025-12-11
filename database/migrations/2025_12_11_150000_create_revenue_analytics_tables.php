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
        // Daily revenue snapshots for fast dashboard loading
        Schema::create('daily_revenue_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique()->index();
            
            // Booking metrics
            $table->integer('total_bookings')->default(0);
            $table->integer('confirmed_bookings')->default(0);
            $table->integer('cancelled_bookings')->default(0);
            $table->integer('pos_bookings')->default(0);
            
            // Revenue breakdown
            $table->decimal('gross_revenue', 15, 2)->default(0)->comment('Total booking amount');
            $table->decimal('vendor_revenue', 15, 2)->default(0)->comment('Amount to vendors');
            $table->decimal('commission_earned', 15, 2)->default(0)->comment('Platform commission');
            $table->decimal('tax_collected', 15, 2)->default(0)->comment('GST collected');
            
            // Payment status
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('pending_amount', 15, 2)->default(0);
            $table->decimal('refunded_amount', 15, 2)->default(0);
            
            // Payout metrics
            $table->decimal('pending_payouts', 15, 2)->default(0)->comment('Vendor payouts pending');
            $table->decimal('completed_payouts', 15, 2)->default(0)->comment('Vendor payouts completed');
            
            // Invoice metrics
            $table->integer('invoices_generated')->default(0);
            $table->integer('paid_invoices')->default(0);
            $table->integer('pending_invoices')->default(0);
            
            // Milestone metrics
            $table->integer('milestone_payments')->default(0);
            $table->decimal('milestone_revenue', 15, 2)->default(0);
            
            // Trends (compared to previous day)
            $table->decimal('revenue_growth_percent', 5, 2)->default(0);
            $table->decimal('booking_growth_percent', 5, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['snapshot_date', 'gross_revenue']);
        });

        // Vendor revenue tracking
        Schema::create('vendor_revenue_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->date('period_date')->index()->comment('Date for this record');
            $table->enum('period_type', ['daily', 'monthly', 'yearly'])->default('daily');
            
            // Booking stats
            $table->integer('total_bookings')->default(0);
            $table->integer('completed_bookings')->default(0);
            $table->integer('cancelled_bookings')->default(0);
            
            // Revenue breakdown
            $table->decimal('gross_revenue', 15, 2)->default(0)->comment('Total booking value');
            $table->decimal('commission_deducted', 15, 2)->default(0)->comment('Platform commission');
            $table->decimal('net_revenue', 15, 2)->default(0)->comment('Vendor net earnings');
            $table->decimal('tax_amount', 15, 2)->default(0);
            
            // Payout status
            $table->decimal('pending_payout', 15, 2)->default(0);
            $table->decimal('paid_payout', 15, 2)->default(0);
            $table->integer('pending_payout_requests')->default(0);
            $table->integer('completed_payout_requests')->default(0);
            
            // Performance metrics
            $table->decimal('average_booking_value', 10, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0)->comment('Average commission %');
            $table->integer('active_hoardings')->default(0);
            
            $table->timestamps();
            
            $table->unique(['vendor_id', 'period_date', 'period_type']);
            $table->index(['period_date', 'gross_revenue']);
            $table->index(['vendor_id', 'net_revenue']);
        });

        // Location-based revenue tracking
        Schema::create('location_revenue_stats', function (Blueprint $table) {
            $table->id();
            $table->string('city', 100)->index();
            $table->string('state', 100)->index();
            $table->date('period_date')->index();
            $table->enum('period_type', ['daily', 'monthly', 'yearly'])->default('daily');
            
            // Booking metrics
            $table->integer('total_bookings')->default(0);
            $table->integer('active_hoardings')->default(0);
            $table->integer('unique_vendors')->default(0);
            $table->integer('unique_customers')->default(0);
            
            // Revenue
            $table->decimal('gross_revenue', 15, 2)->default(0);
            $table->decimal('commission_earned', 15, 2)->default(0);
            $table->decimal('average_booking_value', 10, 2)->default(0);
            
            // Growth metrics
            $table->decimal('revenue_growth_percent', 5, 2)->default(0);
            
            $table->timestamps();
            
            $table->unique(['city', 'state', 'period_date', 'period_type'], 'location_period_unique');
            $table->index(['period_date', 'gross_revenue']);
        });

        // Commission tracking (for audit trail)
        Schema::create('commission_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            
            // Amount breakdown
            $table->decimal('booking_amount', 12, 2);
            $table->decimal('commission_rate', 5, 2)->comment('Commission percentage');
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('vendor_amount', 12, 2)->comment('Amount to vendor after commission');
            
            // Tax details
            $table->decimal('gst_on_commission', 10, 2)->default(0);
            $table->decimal('tds_deducted', 10, 2)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'calculated', 'settled', 'reversed'])->default('calculated');
            $table->timestamp('settled_at')->nullable();
            $table->foreignId('payout_request_id')->nullable()->constrained('payout_requests')->onDelete('set null');
            
            // Metadata
            $table->string('transaction_ref', 100)->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index(['vendor_id', 'status']);
            $table->index(['booking_id', 'status']);
            $table->index(['created_at', 'commission_amount']);
        });

        // Top performers cache (refreshed hourly)
        Schema::create('revenue_leaderboards', function (Blueprint $table) {
            $table->id();
            $table->enum('leaderboard_type', ['vendors', 'locations', 'categories'])->index();
            $table->enum('period', ['today', 'week', 'month', 'year', 'all_time'])->index();
            $table->json('rankings')->comment('Top 10 performers with metrics');
            $table->timestamp('last_updated_at');
            $table->timestamps();
            
            $table->unique(['leaderboard_type', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_leaderboards');
        Schema::dropIfExists('commission_transactions');
        Schema::dropIfExists('location_revenue_stats');
        Schema::dropIfExists('vendor_revenue_stats');
        Schema::dropIfExists('daily_revenue_snapshots');
    }
};
