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
        Schema::create('cancellation_policies', function (Blueprint $table) {
            $table->id();
            
            // Policy identification
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            
            // Applicability (null = applies to all)
            $table->enum('applies_to', ['all', 'customer', 'vendor', 'admin'])->default('all');
            $table->enum('booking_type', ['ooh', 'dooh', 'pos'])->nullable()->comment('null = all types');
            
            // Time-based cancellation rules (tiered by hours before start)
            $table->json('time_windows')->comment('[{hours_before: 24, refund_percent: 100, customer_fee_percent: 0, vendor_penalty_percent: 0}]');
            
            // Customer cancellation fees
            $table->enum('customer_fee_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('customer_fee_value', 10, 2)->default(0)->comment('% or fixed amount');
            $table->decimal('customer_min_fee', 10, 2)->nullable()->comment('Minimum fee if percentage');
            $table->decimal('customer_max_fee', 10, 2)->nullable()->comment('Maximum fee if percentage');
            
            // Vendor cancellation penalties
            $table->enum('vendor_penalty_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('vendor_penalty_value', 10, 2)->default(0)->comment('% of booking or fixed');
            $table->decimal('vendor_min_penalty', 10, 2)->nullable();
            $table->decimal('vendor_max_penalty', 10, 2)->nullable();
            
            // Refund settings
            $table->boolean('auto_refund_enabled')->default(true)->comment('Auto-refund through payment gateway');
            $table->integer('refund_processing_days')->default(7)->comment('Expected days for refund');
            $table->enum('refund_method', ['original', 'wallet', 'manual'])->default('original');
            
            // POS-specific settings
            $table->boolean('pos_auto_refund_disabled')->default(true)->comment('POS bookings cannot be auto-refunded');
            $table->text('pos_refund_note')->nullable()->comment('Note for POS refund process');
            
            // Admin override
            $table->boolean('allow_admin_override')->default(true);
            $table->text('override_conditions')->nullable();
            
            // Additional rules
            $table->integer('min_hours_before_start')->nullable()->comment('Minimum hours before start to cancel');
            $table->integer('max_hours_before_start')->nullable()->comment('Maximum hours (e.g., 72 for 3 days)');
            $table->decimal('min_booking_amount', 10, 2)->nullable();
            $table->decimal('max_booking_amount', 10, 2)->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('is_active');
            $table->index('is_default');
            $table->index('applies_to');
            $table->index('booking_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancellation_policies');
    }
};
