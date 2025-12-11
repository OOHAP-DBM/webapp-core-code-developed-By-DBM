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
        // Vendor performance metrics tracking
        Schema::create('vendor_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->date('period_date')->index()->comment('Date for this metric record');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            
            // Booking metrics
            $table->integer('total_bookings')->default(0);
            $table->integer('confirmed_bookings')->default(0);
            $table->integer('cancelled_bookings')->default(0);
            $table->integer('disputed_bookings')->default(0);
            $table->decimal('cancellation_rate', 5, 2)->default(0)->comment('Percentage');
            $table->decimal('dispute_rate', 5, 2)->default(0)->comment('Percentage');
            
            // Enquiry metrics
            $table->integer('total_enquiries')->default(0)->comment('Quotation requests received');
            $table->integer('quotations_sent')->default(0);
            $table->integer('quotations_accepted')->default(0);
            $table->decimal('enquiry_to_booking_ratio', 5, 2)->default(0)->comment('Conversion %');
            $table->decimal('quote_acceptance_rate', 5, 2)->default(0)->comment('Percentage');
            
            // Response time metrics (in minutes)
            $table->integer('avg_quote_response_time')->nullable()->comment('Average time to send quote (minutes)');
            $table->integer('median_quote_response_time')->nullable()->comment('Median response time');
            $table->integer('min_quote_response_time')->nullable();
            $table->integer('max_quote_response_time')->nullable();
            $table->integer('quotes_within_24h')->default(0)->comment('Quotes sent within 24 hours');
            $table->decimal('response_time_compliance', 5, 2)->default(0)->comment('% within 24h');
            
            // SLA metrics
            $table->decimal('overall_sla_score', 5, 2)->default(100)->comment('0-100 scale');
            $table->integer('sla_violations')->default(0);
            $table->integer('sla_compliant_deliveries')->default(0);
            $table->decimal('sla_compliance_rate', 5, 2)->default(100)->comment('Percentage');
            
            // Rating metrics
            $table->decimal('average_rating', 3, 2)->default(0)->comment('0-5 scale');
            $table->integer('total_ratings')->default(0);
            $table->integer('five_star_ratings')->default(0);
            $table->integer('four_star_ratings')->default(0);
            $table->integer('three_star_ratings')->default(0);
            $table->integer('two_star_ratings')->default(0);
            $table->integer('one_star_ratings')->default(0);
            
            // Revenue metrics
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('avg_booking_value', 10, 2)->default(0);
            $table->decimal('commission_paid', 12, 2)->default(0);
            
            // Performance trends
            $table->decimal('booking_growth_percent', 5, 2)->default(0)->comment('vs previous period');
            $table->decimal('revenue_growth_percent', 5, 2)->default(0);
            $table->decimal('rating_trend', 5, 2)->default(0)->comment('Change in rating');
            
            $table->timestamps();
            
            $table->unique(['vendor_id', 'period_date', 'period_type'], 'vendor_period_unique');
            $table->index(['period_date', 'period_type']);
            $table->index(['vendor_id', 'overall_sla_score']);
            $table->index(['vendor_id', 'average_rating']);
        });

        // Enquiry response tracking
        Schema::create('vendor_enquiry_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            // Timing metrics
            $table->timestamp('enquiry_received_at')->index();
            $table->timestamp('quote_sent_at')->nullable()->index();
            $table->integer('response_time_minutes')->nullable()->comment('Time to send quote');
            
            // Response quality
            $table->enum('response_status', ['pending', 'sent', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->boolean('within_24h')->default(false);
            $table->boolean('within_12h')->default(false);
            $table->boolean('within_6h')->default(false);
            
            // Outcome
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->boolean('converted_to_booking')->default(false);
            $table->timestamp('booking_created_at')->nullable();
            
            // Follow-up tracking
            $table->integer('follow_up_count')->default(0);
            $table->timestamp('last_follow_up_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['vendor_id', 'enquiry_received_at']);
            $table->index(['vendor_id', 'response_status']);
            $table->index(['response_time_minutes']);
        });

        // Disputed bookings tracking
        Schema::create('booking_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            // Dispute details
            $table->enum('dispute_type', [
                'quality_issue',
                'installation_delay',
                'creative_approval',
                'billing_dispute',
                'service_not_provided',
                'poor_location',
                'damage_claim',
                'other'
            ])->index();
            
            $table->text('dispute_reason');
            $table->text('vendor_response')->nullable();
            $table->text('resolution_notes')->nullable();
            
            // Status tracking
            $table->enum('status', ['open', 'under_review', 'resolved', 'closed', 'escalated'])->default('open')->index();
            $table->enum('resolution', ['customer_favor', 'vendor_favor', 'mutual_agreement', 'refunded', 'compensated', 'unresolved'])->nullable();
            
            // Financial impact
            $table->decimal('disputed_amount', 12, 2)->nullable();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('compensation_amount', 12, 2)->default(0);
            
            // SLA impact
            $table->boolean('affects_vendor_sla')->default(true);
            $table->integer('sla_score_impact')->default(0)->comment('Negative impact on SLA score');
            
            // Timestamps
            $table->timestamp('disputed_at')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Evidence
            $table->json('customer_evidence')->nullable()->comment('Photos, documents');
            $table->json('vendor_evidence')->nullable();
            
            $table->timestamps();
            
            $table->index(['vendor_id', 'status']);
            $table->index(['vendor_id', 'disputed_at']);
            $table->index(['dispute_type', 'status']);
        });

        // Vendor performance rankings (cached leaderboard)
        Schema::create('vendor_performance_rankings', function (Blueprint $table) {
            $table->id();
            $table->enum('ranking_type', ['overall', 'sla', 'rating', 'response_time', 'conversion'])->index();
            $table->enum('period', ['week', 'month', 'quarter', 'year'])->index();
            $table->json('rankings')->comment('Top vendors with scores');
            $table->timestamp('last_updated_at');
            $table->timestamps();
            
            $table->unique(['ranking_type', 'period']);
        });

        // Add performance tracking fields to quotations table
        Schema::table('quotations', function (Blueprint $table) {
            $table->timestamp('vendor_notified_at')->nullable()->after('created_at')->comment('When vendor was notified of enquiry');
            $table->timestamp('vendor_viewed_at')->nullable()->after('vendor_notified_at')->comment('When vendor first viewed enquiry');
            $table->timestamp('quote_sent_at')->nullable()->after('vendor_viewed_at')->comment('When vendor submitted quote');
            $table->integer('response_time_minutes')->nullable()->after('quote_sent_at')->comment('Time from notification to quote sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'vendor_notified_at',
                'vendor_viewed_at',
                'quote_sent_at',
                'response_time_minutes'
            ]);
        });
        
        Schema::dropIfExists('vendor_performance_rankings');
        Schema::dropIfExists('booking_disputes');
        Schema::dropIfExists('vendor_enquiry_responses');
        Schema::dropIfExists('vendor_performance_metrics');
    }
};
