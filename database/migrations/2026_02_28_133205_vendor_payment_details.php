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
        Schema::create('vendor_payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->enum('type', ['bank', 'upi']);

            // Bank fields
            $table->string('bank_name',  255)->nullable();
            $table->string('ifsc_code',   11)->nullable();
            $table->string('account_number', 20)->nullable();
            $table->string('account_holder', 255)->nullable();

            // UPI fields
            $table->string('upi_id',        100)->nullable();
            $table->string('qr_image_path', 500)->nullable();

            $table->timestamps();

            $table->unique(['vendor_id', 'type']); // one bank + one upi per vendor
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add hold_minutes + hold_expiry_at to pos_bookings if not already present
        Schema::table('pos_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_bookings', 'hold_minutes')) {
                $table->unsignedSmallInteger('hold_minutes')->default(30)->after('payment_status')
                      ->comment('Minutes the booking is held waiting for payment (0 = no limit)');
            }
            if (!Schema::hasColumn('pos_bookings', 'hold_expiry_at')) {
                $table->timestamp('hold_expiry_at')->nullable()->after('hold_minutes')
                      ->comment('When the payment hold expires and booking should be auto-released');
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payment_details');
        Schema::table('pos_bookings', function (Blueprint $table) {
            $table->dropColumnIfExists('hold_minutes');
            $table->dropColumnIfExists('hold_expiry_at');
        });
    }
};
