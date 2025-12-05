<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN for ENUM
        // We need to recreate the table with the new enum values
        
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // For SQLite: Create new table, copy data, drop old, rename new
            Schema::create('bookings_temp', function (Blueprint $table) {
                // Copy structure from existing bookings table
                $table->id();
                $table->foreignId('quotation_id')->constrained('quotations');
                $table->foreignId('customer_id')->constrained('users');
                $table->foreignId('vendor_id')->constrained('users');
                $table->foreignId('hoarding_id')->constrained('hoardings');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('duration_type', 50);
                $table->integer('duration_days');
                $table->decimal('total_amount', 10, 2);
                $table->string('status', 50)->default('pending');
                $table->timestamp('hold_expiry_at')->nullable();
                $table->json('booking_snapshot')->nullable();
                $table->text('customer_notes')->nullable();
                $table->string('razorpay_order_id', 100)->nullable();
                $table->string('razorpay_payment_id', 100)->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                
                // Payment status with new 'voided' value
                $table->enum('payment_status', ['pending', 'authorized', 'captured', 'failed', 'refunded', 'expired', 'voided'])->default('pending');
                $table->string('payment_error_code', 100)->nullable();
                $table->text('payment_error_description')->nullable();
                $table->timestamp('payment_authorized_at')->nullable();
                $table->timestamp('payment_captured_at')->nullable();
                $table->timestamp('payment_failed_at')->nullable();
                $table->timestamp('capture_attempted_at')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
            });

            // Copy data
            DB::statement('INSERT INTO bookings_temp SELECT * FROM bookings');

            // Drop old table
            Schema::dropIfExists('bookings');

            // Rename temp table
            Schema::rename('bookings_temp', 'bookings');

            // Recreate indexes
            Schema::table('bookings', function (Blueprint $table) {
                $table->index('status');
                $table->index('hold_expiry_at');
                $table->index('start_date');
                $table->index('end_date');
                $table->index(['hoarding_id', 'start_date', 'end_date'], 'idx_hoarding_dates');
                $table->index(['status', 'hold_expiry_at'], 'idx_status_hold');
                $table->index(['payment_status', 'hold_expiry_at', 'capture_attempted_at'], 'idx_capture_job');
            });
        } else {
            // For MySQL: Use ALTER TABLE MODIFY
            DB::statement("ALTER TABLE bookings MODIFY COLUMN payment_status ENUM('pending', 'authorized', 'captured', 'failed', 'refunded', 'expired', 'voided') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // For SQLite: Recreate table without 'voided'
            Schema::create('bookings_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quotation_id')->constrained('quotations');
                $table->foreignId('customer_id')->constrained('users');
                $table->foreignId('vendor_id')->constrained('users');
                $table->foreignId('hoarding_id')->constrained('hoardings');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('duration_type', 50);
                $table->integer('duration_days');
                $table->decimal('total_amount', 10, 2);
                $table->string('status', 50)->default('pending');
                $table->timestamp('hold_expiry_at')->nullable();
                $table->json('booking_snapshot')->nullable();
                $table->text('customer_notes')->nullable();
                $table->string('razorpay_order_id', 100)->nullable();
                $table->string('razorpay_payment_id', 100)->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                
                // Payment status without 'voided'
                $table->enum('payment_status', ['pending', 'authorized', 'captured', 'failed', 'refunded', 'expired'])->default('pending');
                $table->string('payment_error_code', 100)->nullable();
                $table->text('payment_error_description')->nullable();
                $table->timestamp('payment_authorized_at')->nullable();
                $table->timestamp('payment_captured_at')->nullable();
                $table->timestamp('payment_failed_at')->nullable();
                $table->timestamp('capture_attempted_at')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
            });

            // Copy data (excluding voided status records)
            DB::statement("INSERT INTO bookings_temp SELECT * FROM bookings WHERE payment_status != 'voided'");

            // Drop old table
            Schema::dropIfExists('bookings');

            // Rename temp table
            Schema::rename('bookings_temp', 'bookings');

            // Recreate indexes
            Schema::table('bookings', function (Blueprint $table) {
                $table->index('status');
                $table->index('hold_expiry_at');
                $table->index('start_date');
                $table->index('end_date');
                $table->index(['hoarding_id', 'start_date', 'end_date'], 'idx_hoarding_dates');
                $table->index(['status', 'hold_expiry_at'], 'idx_status_hold');
                $table->index(['payment_status', 'hold_expiry_at', 'capture_attempted_at'], 'idx_capture_job');
            });
        } else {
            // For MySQL: Use ALTER TABLE MODIFY
            DB::statement("ALTER TABLE bookings MODIFY COLUMN payment_status ENUM('pending', 'authorized', 'captured', 'failed', 'refunded', 'expired') DEFAULT 'pending'");
        }
    }
};
