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
        Schema::create('dooh_bookings', function (Blueprint $table) {
            $table->id();
            
            // References
            $table->foreignId('dooh_screen_id')->constrained('dooh_screens')->onDelete('cascade');
            $table->foreignId('dooh_package_id')->nullable()->constrained('dooh_packages')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            
            // Booking reference
            $table->string('booking_number')->unique(); // Auto-generated: DOOH-YYYYMMDD-XXXX
            
            // Booking period
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months'); // Duration in months
            $table->integer('duration_days'); // Total days
            
            // Slot allocation
            $table->integer('slots_per_day'); // Number of slots booked per day
            $table->integer('total_slots'); // Total slots for entire period
            $table->integer('slot_frequency_minutes')->default(5); // How often ad appears
            
            // Content details
            $table->json('content_files')->nullable(); // Uploaded content files [{path, type, size, duration}]
            $table->enum('content_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('content_rejection_reason')->nullable();
            $table->timestamp('content_approved_at')->nullable();
            $table->foreignId('content_approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Pricing
            $table->decimal('package_price', 12, 2); // Package base price
            $table->decimal('total_amount', 12, 2); // Total before discount/tax
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2); // Final payable amount
            
            // Payment details
            $table->enum('payment_status', ['pending', 'authorized', 'captured', 'failed', 'refunded'])->default('pending');
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->timestamp('payment_authorized_at')->nullable();
            $table->timestamp('payment_captured_at')->nullable();
            $table->timestamp('hold_expiry_at')->nullable(); // 30-minute payment hold
            
            // Refund details
            $table->string('refund_id')->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            
            // Campaign lifecycle
            $table->enum('status', [
                'draft',
                'payment_pending',
                'payment_authorized',
                'confirmed',
                'content_pending',
                'content_approved',
                'active',
                'paused',
                'completed',
                'cancelled'
            ])->default('draft');
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('campaign_started_at')->nullable();
            $table->timestamp('campaign_ended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Booking snapshot
            $table->json('booking_snapshot')->nullable(); // Store screen, package details at booking time
            
            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Survey package (optional)
            $table->boolean('survey_required')->default(false);
            $table->enum('survey_status', ['not_required', 'pending', 'completed'])->default('not_required');
            $table->timestamp('survey_completed_at')->nullable();
            $table->json('survey_data')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('booking_number');
            $table->index('customer_id');
            $table->index('vendor_id');
            $table->index('dooh_screen_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dooh_bookings');
    }
};
